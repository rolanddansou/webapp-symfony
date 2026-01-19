<?php

namespace App\Controller\Api;

use App\Feature\Access\DTO\AuthResponse;
use App\Feature\Access\DTO\ForgotPasswordRequest;
use App\Feature\Access\DTO\LoginRequest;
use App\Feature\Access\DTO\RefreshTokenRequest;
use App\Feature\Access\DTO\RegisterRequest;
use App\Feature\Access\DTO\ResendVerificationRequest;
use App\Feature\Access\DTO\ResetPasswordRequest;
use App\Feature\Access\DTO\SendVerificationCodeRequest;
use App\Feature\Access\DTO\UserProfileResponse;
use App\Feature\Access\DTO\VerifyCodeRequest;
use App\Feature\Access\DTO\VerifyEmailRequest;
use App\Feature\Access\DTO\VerifyResetCodeRequest;
use App\Feature\Access\Exception\AuthenticationException;
use App\Feature\Access\Service\AuthService;
use App\Feature\Access\Service\EmailVerificationService;
use App\Feature\Access\Service\IdentityUser;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route('/api/auth', name: 'api_auth_')]
#[OA\Tag(name: 'Auth')]
final class AuthController extends AbstractController
{
    public function __construct(
        private readonly AuthService $authService,
        private readonly EntityManagerInterface $entityManager,
        private readonly EmailVerificationService $emailVerificationService,
    ) {
    }

    #[Route('/register', name: 'register', methods: ['POST'])]
    #[OA\Post(
        description: 'Creates a new user account and sends email verification code',
        summary: 'Register a new user',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: new Model(type: RegisterRequest::class))
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Registration successful',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'success',
                            type: 'boolean',
                            example: true
                        ),
                        new OA\Property(
                            property: 'message',
                            type: 'string',
                            example: 'Registration successful. Please check your email for the verification code.'
                        ),
                        new OA\Property(
                            property: 'data',
                            ref: new Model(type: AuthResponse::class)
                        ),
                    ]
                )
            ),
            new OA\Response(response: 409, description: 'Email already exists'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function register(#[MapRequestPayload] RegisterRequest $request): JsonResponse
    {
        try {
            $response = $this->authService->register($request);

            return $this->json([
                'success' => true,
                'message' => $response->emailVerificationRequired
                    ? 'Registration successful. Please check your email for the verification code.'
                    : 'Registration successful.',
                'data' => $response,
            ], Response::HTTP_CREATED);
        } catch (AuthenticationException $e) {
            return $this->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], $e->getCode());
        }
    }

    #[Route('/verify-email', name: 'verify_email', methods: ['POST'])]
    #[OA\Post(
        description: 'Verifies user email with the 6-digit code sent via email',
        summary: 'Verify email address',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['email', 'code'],
                properties: [
                    new OA\Property(property: 'email', type: 'string', format: 'email'),
                    new OA\Property(property: 'code', type: 'string', example: '123456'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Email verified successfully'),
            new OA\Response(response: 400, description: 'Invalid or expired code'),
        ]
    )]
    public function verifyEmail(
        #[MapRequestPayload] VerifyEmailRequest $request
    ): JsonResponse {
        try {
            $user = $this->emailVerificationService->verifyEmail($request->email, $request->code);

            return $this->json([
                'success' => true,
                'message' => 'Email verified successfully.',
                'data' => [
                    'emailVerified' => true,
                    'user' => UserProfileResponse::fromEntity($user),
                ],
            ]);
        } catch (AuthenticationException $e) {
            return $this->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], $e->getCode());
        }
    }

    #[Route('/resend-verification', name: 'resend_verification', methods: ['POST'])]
    #[OA\Post(
        description: 'Sends a new verification code to the user email',
        summary: 'Resend verification code',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['email'],
                properties: [
                    new OA\Property(property: 'email', type: 'string', format: 'email'),
                ]
            )
        ),
        responses: [
            new OA\Response(response: 200, description: 'Code sent successfully'),
            new OA\Response(response: 400, description: 'Email already verified'),
            new OA\Response(response: 429, description: 'Too many requests'),
        ]
    )]
    public function resendVerification(
        #[MapRequestPayload] ResendVerificationRequest $request
    ): JsonResponse {
        try {
            $this->emailVerificationService->resendVerificationCode($request->email);

            return $this->json([
                'success' => true,
                'message' => 'Verification code has been sent to your email.',
            ]);
        } catch (AuthenticationException $e) {
            return $this->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], $e->getCode());
        }
    }

    // ========================================
    // PRE-REGISTRATION VERIFICATION ROUTES
    // ========================================

    #[Route('/verification/send', name: 'verification_send', methods: ['POST'])]
    #[OA\Post(
        description: 'Sends a verification code to an email address before registration. Use this to verify email ownership before creating an account.',
        summary: 'Send verification code (pre-registration)',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['email'],
                properties: [
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'user@example.com'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Code sent successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Verification code has been sent to your email.'),
                    ]
                )
            ),
            new OA\Response(response: 409, description: 'Email already registered'),
            new OA\Response(response: 429, description: 'Too many requests - please wait before requesting a new code'),
        ]
    )]
    public function sendVerificationCode(
        #[MapRequestPayload] SendVerificationCodeRequest $request
    ): JsonResponse {
        try {
            $this->emailVerificationService->sendVerificationCodeForEmail($request->email);

            return $this->json([
                'success' => true,
                'message' => 'Verification code has been sent to your email.',
            ]);
        } catch (AuthenticationException $e) {
            return $this->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], $e->getCode());
        }
    }

    #[Route('/verification/verify', name: 'verification_verify', methods: ['POST'])]
    #[OA\Post(
        description: 'Verifies the code sent to an email address. Call this before registration to confirm email ownership.',
        summary: 'Verify code (pre-registration)',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['email', 'code'],
                properties: [
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'user@example.com'),
                    new OA\Property(property: 'code', type: 'string', example: '123456'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Code verified successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Email verified successfully. You can now proceed with registration.'),
                        new OA\Property(property: 'data', properties: [
                            new OA\Property(property: 'verified', type: 'boolean', example: true),
                            new OA\Property(property: 'email', type: 'string', example: 'user@example.com'),
                        ], type: 'object'),
                    ]
                )
            ),
            new OA\Response(response: 400, description: 'Invalid or expired verification code'),
        ]
    )]
    public function verifyCode(
        #[MapRequestPayload] VerifyCodeRequest $request
    ): JsonResponse {
        $isValid = $this->emailVerificationService->verifyCodeOnly($request->email, $request->code);

        if (!$isValid) {
            return $this->json([
                'success' => false,
                'error' => 'Invalid or expired verification code.',
            ], Response::HTTP_BAD_REQUEST);
        }

        return $this->json([
            'success' => true,
            'message' => 'Email verified successfully. You can now proceed with registration.',
            'data' => [
                'verified' => true,
                'email' => strtolower(trim($request->email)),
            ],
        ]);
    }

    #[Route('/login', name: 'login', methods: ['POST'])]
    #[OA\Post(
        description: 'Authenticates user and returns JWT tokens',
        summary: 'Login user',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: new Model(type: LoginRequest::class))
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Login successful',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'success',
                            type: 'boolean',
                            example: true
                        ),
                        new OA\Property(
                            property: 'data',
                            ref: new Model(type: AuthResponse::class)
                        ),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Invalid credentials'),
            new OA\Response(response: 403, description: 'Email not verified'),
        ]
    )]
    public function login(
        #[MapRequestPayload] LoginRequest $request
    ): JsonResponse {
        try {
            $response = $this->authService->login($request);

            return $this->json([
                'success' => true,
                'data' => $response,
            ]);
        } catch (AuthenticationException $e) {
            return $this->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], $e->getCode());
        }
    }

    #[Route('/refresh', name: 'refresh', methods: ['POST'])]
    public function refresh(
        #[MapRequestPayload] RefreshTokenRequest $request
    ): JsonResponse {
        try {
            $response = $this->authService->refreshToken(
                $request->refreshToken,
                $request->deviceId
            );

            return $this->json([
                'success' => true,
                'data' => $response,
            ]);
        } catch (AuthenticationException $e) {
            return $this->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], $e->getCode());
        }
    }

    #[Route('/logout', name: 'logout', methods: ['POST'])]
    public function logout(
        Request $request,
        #[CurrentUser] ?IdentityUser $user
    ): JsonResponse {
        if (!$user) {
            return $this->json([
                'success' => false,
                'error' => 'Not authenticated',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $deviceId = $request->headers->get('X-Device-Id', 'default');
        $this->authService->logout($user->getIdentity(), $deviceId);

        return $this->json([
            'success' => true,
            'message' => 'Successfully logged out',
        ]);
    }

    #[Route('/me', name: 'me', methods: ['GET'])]
    public function me(
        #[CurrentUser] ?IdentityUser $user,

    ): JsonResponse {
        if (!$user) {
            return $this->json([
                'success' => false,
                'error' => 'Not authenticated',
            ], Response::HTTP_UNAUTHORIZED);
        }

        return $this->json([
            'success' => true,
            'data' => UserProfileResponse::fromEntity($user->getIdentity()),
        ]);
    }

    #[Route('/forgot-password', name: 'forgot_password', methods: ['POST'])]
    public function forgotPassword(
        #[MapRequestPayload] ForgotPasswordRequest $request
    ): JsonResponse {
        $this->authService->requestPasswordReset($request->email);

        // Always return success to not reveal if email exists
        return $this->json([
            'success' => true,
            'message' => 'If the email exists, a password reset code has been sent.',
        ]);
    }

    #[Route('/verify-reset-code', name: 'verify_reset_code', methods: ['POST'])]
    public function verifyResetCode(
        #[MapRequestPayload] VerifyResetCodeRequest $request
    ): JsonResponse {
        $isValid = $this->authService->verifyPasswordResetCode($request->email, $request->code);

        if (!$isValid) {
            return $this->json([
                'success' => false,
                'error' => 'Invalid or expired reset code.',
            ], Response::HTTP_BAD_REQUEST);
        }

        return $this->json([
            'success' => true,
            'message' => 'Reset code is valid. You can now reset your password.',
            'data' => [
                'valid' => true,
            ],
        ]);
    }

    #[Route('/reset-password', name: 'reset_password', methods: ['POST'])]
    public function resetPassword(
        #[MapRequestPayload] ResetPasswordRequest $request
    ): JsonResponse {
        try {
            $this->authService->resetPasswordWithCode(
                $request->email,
                $request->code,
                $request->newPassword
            );

            return $this->json([
                'success' => true,
                'message' => 'Password has been reset successfully.',
            ]);
        } catch (AuthenticationException $e) {
            return $this->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], $e->getCode());
        } catch (\Throwable $e) {
            return $this->json([
                'success' => false,
                'error' => 'Failed to reset password.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
