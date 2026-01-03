<?php

namespace App\Feature\Access\Service;

use App\Entity\Access\EmailVerificationCode;
use App\Entity\Access\Identity;
use App\Feature\Access\Event\EmailVerificationRequestedEvent;
use App\Feature\Access\Event\EmailVerifiedEvent;
use App\Feature\Access\Event\PasswordResetCodeSentEvent;
use App\Feature\Access\Exception\AuthenticationException;
use App\Feature\Helper\DateHelper;
use App\Repository\Access\EmailVerificationCodeRepository;
use App\Repository\Access\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final class EmailVerificationService
{
    private const RESEND_COOLDOWN_SECONDS = 60; // 1 minute cooldown between resends

    public function __construct(
        private readonly EmailVerificationCodeRepository $codeRepository,
        private readonly UserRepository $userRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly MailerInterface $mailer,
        #[Autowire('%app_name%')]
        private readonly string $appName = '',
        #[Autowire('%app.email_from%')]
        private readonly string $fromEmail = '',
    ) {
    }

    public function sendVerificationCode(Identity $user): EmailVerificationCode
    {
        // Check cooldown
        $latestCode = $this->codeRepository->findLatestByUserAndType($user, 'email_verification');
        if ($latestCode && !$this->canResend($latestCode)) {
            $waitTime = $this->getWaitTime($latestCode);
            throw AuthenticationException::verificationCodeCooldown($waitTime);
        }

        // Invalidate previous codes
        $this->codeRepository->invalidateAllForUser($user, 'email_verification');

        // Create new code
        $verificationCode = new EmailVerificationCode($user, 'email_verification');
        $this->codeRepository->save($verificationCode, true);

        // Send email
        $this->sendVerificationEmail($user, $verificationCode->getCode());

        // Dispatch event
        $this->eventDispatcher->dispatch(
            new EmailVerificationRequestedEvent($user, $verificationCode->getCode()),
            EmailVerificationRequestedEvent::NAME
        );

        return $verificationCode;
    }

    public function verifyEmail(string $email, string $code): Identity
    {
        $user = $this->userRepository->findByEmail($email);
        if (!$user) {
            throw AuthenticationException::userNotFound();
        }

        if ($user->isEmailVerified()) {
            throw AuthenticationException::emailAlreadyVerified();
        }

        $verificationCode = $this->codeRepository->findValidCode($user, $code, 'email_verification');
        if (!$verificationCode) {
            throw AuthenticationException::invalidVerificationCode();
        }

        // Mark code as used
        $verificationCode->markAsUsed();

        // Mark email as verified
        $user->markEmailAsVerified();

        $this->entityManager->flush();

        // Dispatch event
        $this->eventDispatcher->dispatch(
            new EmailVerifiedEvent($user),
            EmailVerifiedEvent::NAME
        );

        return $user;
    }

    public function sendPasswordResetCode(string $email): void
    {
        $user = $this->userRepository->findByEmail($email);
        if (!$user) {
            // Don't reveal if user exists
            return;
        }

        // Check cooldown
        $latestCode = $this->codeRepository->findLatestByUserAndType($user, 'password_reset');
        if ($latestCode && !$this->canResend($latestCode)) {
            // Silently ignore to not reveal timing
            return;
        }

        // Invalidate previous codes
        $this->codeRepository->invalidateAllForUser($user, 'password_reset');

        // Create new code
        $resetCode = new EmailVerificationCode($user, 'password_reset');
        $this->codeRepository->save($resetCode, true);

        // Send email
        $this->sendPasswordResetEmail($user, $resetCode->getCode());

        // Dispatch event
        $this->eventDispatcher->dispatch(
            new PasswordResetCodeSentEvent($user, $resetCode->getCode()),
            PasswordResetCodeSentEvent::NAME
        );
    }

    public function verifyPasswordResetCode(string $email, string $code): Identity
    {
        $user = $this->userRepository->findByEmail($email);
        if (!$user) {
            throw AuthenticationException::invalidResetCode();
        }

        $resetCode = $this->codeRepository->findValidCode($user, $code, 'password_reset');
        if (!$resetCode) {
            throw AuthenticationException::invalidResetCode();
        }

        return $user;
    }

    public function resetPasswordWithCode(string $email, string $code, string $newPasswordHash): Identity
    {
        $user = $this->userRepository->findByEmail($email);
        if (!$user) {
            throw AuthenticationException::invalidResetCode();
        }

        $resetCode = $this->codeRepository->findValidCode($user, $code, 'password_reset');
        if (!$resetCode) {
            throw AuthenticationException::invalidResetCode();
        }

        // Mark code as used
        $resetCode->markAsUsed();

        // Update password
        $credentials = $user->getCredentials();
        if ($credentials) {
            $credentials->setPasswordHash($newPasswordHash);
        }

        $this->entityManager->flush();

        return $user;
    }

    public function resendVerificationCode(string $email): void
    {
        $user = $this->userRepository->findByEmail($email);
        if (!$user) {
            throw AuthenticationException::userNotFound();
        }

        if ($user->isEmailVerified()) {
            throw AuthenticationException::emailAlreadyVerified();
        }

        $this->sendVerificationCode($user);
    }

    // ========================================
    // PRE-REGISTRATION VERIFICATION METHODS
    // ========================================

    /**
     * Send a verification code to an email address (pre-registration, no user exists yet)
     * This is used during registration flow to verify email before creating the account
     */
    public function sendVerificationCodeForEmail(string $email): void
    {
        $normalizedEmail = strtolower(trim($email));

        // Check if email is already registered
        $existingUser = $this->userRepository->findByEmail($normalizedEmail);
        if ($existingUser) {
            throw AuthenticationException::emailAlreadyExists();
        }

        // Check cooldown
        $latestCode = $this->codeRepository->findLatestByEmailAndType($normalizedEmail, 'pre_registration');
        if ($latestCode && !$this->canResend($latestCode)) {
            $waitTime = $this->getWaitTime($latestCode);
            throw AuthenticationException::verificationCodeCooldown($waitTime);
        }

        // Invalidate previous codes
        $this->codeRepository->invalidateAllForEmail($normalizedEmail, 'pre_registration');

        // Create new code
        $verificationCode = EmailVerificationCode::createForEmail($normalizedEmail, 'pre_registration');
        $this->codeRepository->save($verificationCode, true);

        // Send email
        $this->sendPreRegistrationEmail($normalizedEmail, $verificationCode->getCode());
    }

    /**
     * Verify a code for an email address (pre-registration)
     * Returns true if the code is valid, without consuming it
     */
    public function verifyCodeOnly(string $email, string $code): bool
    {
        $normalizedEmail = strtolower(trim($email));
        $verificationCode = $this->codeRepository->findValidCodeByEmail($normalizedEmail, $code, 'pre_registration');
        return $verificationCode !== null;
    }

    /**
     * Verify a code and mark it as used
     * Returns true if the code was valid and has been consumed
     */
    public function verifyAndConsumeCode(string $email, string $code): bool
    {
        $normalizedEmail = strtolower(trim($email));
        $verificationCode = $this->codeRepository->findValidCodeByEmail($normalizedEmail, $code, 'pre_registration');

        if (!$verificationCode) {
            return false;
        }

        $verificationCode->markAsUsed();
        $this->entityManager->flush();

        return true;
    }

    /**
     * Send pre-registration verification email
     */
    private function sendPreRegistrationEmail(string $email, string $code): void
    {
        $emailMessage = (new Email())
            ->from($this->fromEmail)
            ->to($email)
            ->subject("{$this->appName} - Vérification de votre adresse email")
            ->html($this->getPreRegistrationEmailTemplate($email, $code));

        $this->mailer->send($emailMessage);
    }

    /**
     * Get pre-registration email template
     */
    private function getPreRegistrationEmailTemplate(string $email, string $code): string
    {
        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .code { font-size: 32px; font-weight: bold; letter-spacing: 5px; color: #007bff; text-align: center; padding: 20px; background: #f8f9fa; border-radius: 8px; margin: 20px 0; }
        .footer { margin-top: 30px; font-size: 12px; color: #666; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Vérification de votre email</h1>
        <p>Bonjour,</p>
        <p>Vous avez demandé à créer un compte sur {$this->appName}. Pour continuer votre inscription, veuillez entrer le code de vérification suivant :</p>
        <div class="code">{$code}</div>
        <p>Ce code est valide pendant 15 minutes.</p>
        <p>Si vous n'avez pas demandé ce code, vous pouvez ignorer cet email.</p>
        <div class="footer">
            <p>Cet email a été envoyé automatiquement, merci de ne pas y répondre.</p>
            <p>© {$this->appName}</p>
        </div>
    </div>
</body>
</html>
HTML;
    }


    private function sendVerificationEmail(Identity $user, string $code): void
    {
        $email = (new Email())
            ->from($this->fromEmail)
            ->to($user->getEmail())
            ->subject("{$this->appName} - Vérification de votre adresse email")
            ->html($this->getVerificationEmailTemplate($user, $code));

        $this->mailer->send($email);
    }

    private function sendPasswordResetEmail(Identity $user, string $code): void
    {
        $email = (new Email())
            ->from($this->fromEmail)
            ->to($user->getEmail())
            ->subject("{$this->appName} - Réinitialisation de votre mot de passe")
            ->html($this->getPasswordResetEmailTemplate($user, $code));

        $this->mailer->send($email);
    }

    private function getVerificationEmailTemplate(Identity $user, string $code): string
    {
        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .code { font-size: 32px; font-weight: bold; letter-spacing: 5px; color: #007bff; text-align: center; padding: 20px; background: #f8f9fa; border-radius: 8px; margin: 20px 0; }
        .footer { margin-top: 30px; font-size: 12px; color: #666; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Vérification de votre email</h1>
        <p>Bonjour,</p>
        <p>Merci de vous être inscrit sur {$this->appName}. Pour finaliser votre inscription, veuillez entrer le code de vérification suivant :</p>
        <div class="code">{$code}</div>
        <p>Ce code est valide pendant 15 minutes.</p>
        <p>Si vous n'avez pas créé de compte sur {$this->appName}, vous pouvez ignorer cet email.</p>
        <div class="footer">
            <p>Cet email a été envoyé automatiquement, merci de ne pas y répondre.</p>
            <p>© {$this->appName}</p>
        </div>
    </div>
</body>
</html>
HTML;
    }

    private function getPasswordResetEmailTemplate(Identity $user, string $code): string
    {
        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .code { font-size: 32px; font-weight: bold; letter-spacing: 5px; color: #dc3545; text-align: center; padding: 20px; background: #f8f9fa; border-radius: 8px; margin: 20px 0; }
        .footer { margin-top: 30px; font-size: 12px; color: #666; }
        .warning { color: #dc3545; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Réinitialisation de votre mot de passe</h1>
        <p>Bonjour,</p>
        <p>Vous avez demandé la réinitialisation de votre mot de passe sur {$this->appName}. Voici votre code de réinitialisation :</p>
        <div class="code">{$code}</div>
        <p>Ce code est valide pendant 15 minutes.</p>
        <p class="warning">Si vous n'avez pas demandé cette réinitialisation, veuillez ignorer cet email et votre mot de passe restera inchangé.</p>
        <div class="footer">
            <p>Cet email a été envoyé automatiquement, merci de ne pas y répondre.</p>
            <p>© {$this->appName}</p>
        </div>
    </div>
</body>
</html>
HTML;
    }

    private function canResend(EmailVerificationCode $code): bool
    {
        $createdAt = $code->getCreatedAt();
        $cooldownEnd = $createdAt->modify('+' . self::RESEND_COOLDOWN_SECONDS . ' seconds');
        return DateHelper::nowUTC() >= $cooldownEnd;
    }

    private function getWaitTime(EmailVerificationCode $code): int
    {
        $createdAt = $code->getCreatedAt();
        $cooldownEnd = $createdAt->modify('+' . self::RESEND_COOLDOWN_SECONDS . ' seconds');
        $now = DateHelper::nowUTC();

        if ($now >= $cooldownEnd) {
            return 0;
        }

        return $cooldownEnd->getTimestamp() - $now->getTimestamp();
    }
}
