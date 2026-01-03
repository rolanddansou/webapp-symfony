<?php

namespace App\Feature\Access\Exception;

class AuthenticationException extends \Exception
{
    public static function invalidCredentials(): self
    {
        return new self('E-mail ou mot de passe incorrect. Veuillez réessayer.', 401);
    }

    public static function userNotFound(): self
    {
        return new self("Nous n'avons pas trouvé un compte associé à cette adresse.", 404);
    }

    public static function accountDisabled(): self
    {
        return new self("Votre compte n'est pas actif. Veuillez contacter l'assistance.", 403);
    }

    public static function invalidRefreshToken(): self
    {
        return new self('Votre session a expiré. Veuillez vous reconnecter.', 401);
    }

    public static function invalidResetToken(): self
    {
        return new self("Le lien de réinitialisation n'est plus valide ou a expiré.", 400);
    }

    public static function emailAlreadyExists(): self
    {
        return new self("Impossible d'utiliser cette adresse e-mail pour créer votre compte pour le moment.", 409);
    }

    public static function invalidVerificationCode(): self
    {
        return new self('Le code de vérification est incorrect ou a expiré.', 400);
    }

    public static function emailAlreadyVerified(): self
    {
        return new self('Votre adresse e-mail a déjà été confirmée.', 400);
    }

    public static function emailNotVerified(): self
    {
        return new self('Veuillez confirmer votre adresse e-mail avant de continuer.', 403);
    }

    public static function verificationCodeCooldown(int $secondsRemaining): self
    {
        return new self("Veuillez patienter {$secondsRemaining} secondes avant de demander un nouveau code.", 429);
    }

    public static function invalidResetCode(): self
    {
        return new self('Le code de réinitialisation est incorrect ou a expiré.', 400);
    }
}
