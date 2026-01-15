<?php

namespace App\Feature\Access\Service;

use App\Entity\Access\Identity;
use App\Entity\Access\UserCredentials;
use App\Entity\Access\UserProfile\UserProfile;
use App\Feature\Access\DTO\AuthResponse;
use App\Feature\Access\DTO\LoginRequest;
use App\Feature\Access\DTO\RegisterRequest;
use App\Feature\Access\DTO\UserProfileResponse;
use App\Feature\Access\Event\PasswordResetRequestedEvent;
use App\Feature\Access\Event\UserLoggedInEvent;
use App\Feature\Access\Event\UserLoggedOutEvent;
use App\Feature\Access\Event\UserRegisteredEvent;
use App\Feature\Access\Exception\AuthenticationException;
use App\Repository\Access\UserCredentialsRepository;
use App\Repository\Access\UserProfile\UserProfileRepository;
use App\Repository\Access\UserRepository;
use App\Repository\Access\UserRoleRepository;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final readonly class AuthService implements AuthServiceInterface
{
    public function __construct(
        private UserRepository                 $userRepository,
        private UserCredentialsRepository      $credentialsRepository,
        private UserRoleRepository             $roleRepository,
        private UserProfileRepository          $customerRepository,
        private TokenServiceInterface          $tokenService,
        private PasswordHasherFactoryInterface $passwordHasherFactory,
        private EventDispatcherInterface       $eventDispatcher,
        private ?EmailVerificationService      $emailVerificationService = null,
        private ?SecurityThrottleService       $throttleService = null,
        private bool                           $requireEmailVerification = false,
    ) {}

    public function register(RegisterRequest $request): AuthResponse
    {
        // Check rate limit
        $this->throttleService?->checkRegistrationAllowed();

        // Check if email already exists
        $existingUser = $this->userRepository->findByEmail($request->email);
        if ($existingUser) {
            throw AuthenticationException::emailAlreadyExists();
        }

        // Create Identity
        $identity = new Identity();
        $identity->setEmail($request->email);
        $identity->setEmailVerified(true);

        // Assign default role
        $defaultRole = $this->roleRepository->getOrCreateRole(UserProfile::ROLE_USER_PROFILE, 'Default user role');
        $identity->addRole($defaultRole);

        // Create UserCredentials
        $credentials = new UserCredentials();
        $credentials->setRelativeUser($identity);
        $credentials->setIsEnabled(true);

        // Hash password
        $hasher = $this->passwordHasherFactory->getPasswordHasher(IdentityUser::class);
        $hashedPassword = $hasher->hash($request->password);
        $credentials->setPasswordHash($hashedPassword);

        // Save identity and credentials
        $this->userRepository->save($identity, true);
        $this->credentialsRepository->save($credentials, true);

        // Create Customer profile
        $customer = new UserProfile($identity, $request->fullName);
        if ($request->phoneNumber) {
            $customer->setPhoneNumber($request->phoneNumber);
        }
        $this->customerRepository->save($customer, true);

        // Send verification code if email verification is enabled
        if ($this->requireEmailVerification && $this->emailVerificationService) {
            try {
                $this->emailVerificationService->sendVerificationCode($identity);
            } catch (\Throwable $e) {
                // Log error but don't fail registration
            }
        }

        // Generate tokens
        $accessToken = $this->tokenService->generateAccessToken($identity);
        $deviceId = $request->deviceId ?? 'default';
        $refreshToken = $this->tokenService->generateRefreshToken($identity, $deviceId);

        // Dispatch event
        $this->eventDispatcher->dispatch(
            new UserRegisteredEvent($identity, $deviceId),
            UserRegisteredEvent::NAME
        );

        return AuthResponse::create(
            accessToken: $accessToken,
            refreshToken: $refreshToken->getToken(),
            expiresIn: $this->tokenService->getAccessTokenTtl(),
            user: UserProfileResponse::fromEntity($identity),
            emailVerificationRequired: $this->requireEmailVerification && !$identity->isEmailVerified(),
        );
    }

    public function login(LoginRequest $request): AuthResponse
    {
        // Check rate limit and account lockout
        $this->throttleService?->checkLoginAllowed($request->email);

        // Find user by email
        $identity = $this->userRepository->findByEmail($request->email);
        if (!$identity) {
            $this->throttleService?->recordFailedLogin($request->email, 'User not found');
            throw AuthenticationException::invalidCredentials();
        }

        // Check credentials
        $credentials = $identity->getCredentials();
        if (!$credentials || !$credentials->getIsEnabled()) {
            $this->throttleService?->recordFailedLogin($request->email, 'Account disabled');
            throw AuthenticationException::accountDisabled();
        }

        // Check email verification if required
        if ($this->requireEmailVerification && !$identity->isEmailVerified()) {
            $this->throttleService?->recordFailedLogin($request->email, 'Email not verified');
            throw AuthenticationException::emailNotVerified();
        }

        // Verify password
        $hasher = $this->passwordHasherFactory->getPasswordHasher(IdentityUser::class);
        if (!$hasher->verify($credentials->getPasswordHash(), $request->password)) {
            $this->throttleService?->recordFailedLogin($request->email, 'Invalid password');
            throw AuthenticationException::invalidCredentials();
        }

        // Record successful login
        $this->throttleService?->recordSuccessfulLogin($request->email);

        // Generate tokens
        $accessToken = $this->tokenService->generateAccessToken($identity);
        $refreshToken = $this->tokenService->generateRefreshToken($identity, $request->deviceId);

        // Dispatch event
        $this->eventDispatcher->dispatch(
            new UserLoggedInEvent($identity, $request->deviceId, ''),
            UserLoggedInEvent::NAME
        );

        return AuthResponse::create(
            accessToken: $accessToken,
            refreshToken: $refreshToken->getToken(),
            expiresIn: $this->tokenService->getAccessTokenTtl(),
            user: UserProfileResponse::fromEntity($identity),
        );
    }

    public function refreshToken(string $refreshToken, string $deviceId): AuthResponse
    {
        $identity = $this->tokenService->validateRefreshToken($refreshToken, $deviceId);

        if (!$identity) {
            throw AuthenticationException::invalidRefreshToken();
        }

        // Check if account is still enabled
        $credentials = $identity->getCredentials();
        if (!$credentials || !$credentials->getIsEnabled()) {
            throw AuthenticationException::accountDisabled();
        }

        // Generate new tokens
        $newAccessToken = $this->tokenService->generateAccessToken($identity);
        $newRefreshToken = $this->tokenService->generateRefreshToken($identity, $deviceId);

        return AuthResponse::create(
            accessToken: $newAccessToken,
            refreshToken: $newRefreshToken->getToken(),
            expiresIn: $this->tokenService->getAccessTokenTtl(),
            user: UserProfileResponse::fromEntity($identity),
        );
    }

    public function logout(Identity $user, string $deviceId): void
    {
        $this->tokenService->invalidateAllRefreshTokens($user);

        $this->eventDispatcher->dispatch(
            new UserLoggedOutEvent($user, $deviceId),
            UserLoggedOutEvent::NAME
        );
    }

    public function getCurrentUser(Identity $user): Identity
    {
        return $user;
    }

    public function requestPasswordReset(string $email): void
    {
        // Use email verification service if available
        if ($this->emailVerificationService) {
            $this->emailVerificationService->sendPasswordResetCode($email);
            return;
        }

        $identity = $this->userRepository->findByEmail($email);

        // Don't reveal if email exists or not
        if (!$identity) {
            return;
        }

        // Generate reset token (store it somewhere - could be in UserCredentials or a separate table)
        $resetToken = bin2hex(random_bytes(32));

        // Dispatch event to send email
        $this->eventDispatcher->dispatch(
            new PasswordResetRequestedEvent($identity, $resetToken),
            PasswordResetRequestedEvent::NAME
        );
    }

    public function resetPassword(string $token, string $newPassword): void
    {
        // This would need a PasswordResetToken entity to properly implement
        // For now, this is a placeholder
        throw new \RuntimeException('Password reset not fully implemented - requires PasswordResetToken entity');
    }

    public function resetPasswordWithCode(string $email, string $code, string $newPassword): void
    {
        if (!$this->emailVerificationService) {
            throw new \RuntimeException('Email verification service is not configured');
        }

        $hasher = $this->passwordHasherFactory->getPasswordHasher(IdentityUser::class);
        $hashedPassword = $hasher->hash($newPassword);

        $this->emailVerificationService->resetPasswordWithCode($email, $code, $hashedPassword);
    }

    public function verifyPasswordResetCode(string $email, string $code): bool
    {
        if (!$this->emailVerificationService) {
            return false;
        }

        try {
            $this->emailVerificationService->verifyPasswordResetCode($email, $code);
            return true;
        } catch (\Throwable) {
            return false;
        }
    }
}
