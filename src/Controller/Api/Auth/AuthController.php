<?php

namespace App\Controller\Api\Auth;

use App\Entity\DTO\UserLoginDTO;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

#[Route(
    '/api/auth',
    name: 'api_auth_',
    defaults: ['show_exception_as_json' => true],
)]
class AuthController extends AbstractController
{
    #[Route(
        '/login',
        name: 'login',
        methods: ['POST'],
    )]
    #[OA\Tag(name: 'auth')]
    #[OA\RequestBody(content: new Model(type: UserLoginDTO::class))]
    #[OA\Response(
        response: 200,
        description: 'Success',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: 'token',
                    type: 'string',
                    default: 'xxxxxxxxxx'
                ),
                new OA\Property(
                    property: 'refreshToken',
                    type: 'string',
                    default: 'xxxxxxxxxx'
                ),
                new OA\Property(
                    property: 'refreshTokenExpiration',
                    type: 'int',
                    default: 1737707969
                ),
            ],
        )
    )]
    public function login(
        JWTTokenManagerInterface $JWTTokenManager,
        UserInterface $user
    ): JsonResponse {
        $token = $JWTTokenManager->create($user);

        return $this->json(['token' => $token]);
    }

    #[Route(
        '/logout',
        name: 'logout',
        methods: ['POST'],
    )]
    #[OA\Tag(name: 'auth')]
    #[OA\RequestBody(content: new OA\JsonContent(
        properties: [
            new OA\Property(
                property: 'refreshToken',
                description: 'Refresh token (optional)',
                type: 'string',
                default: 'xxxxxxxxxx',
                nullable: true
            ),
        ],
    ))]
    #[OA\Response(
        response: 200,
        description: 'Success',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: 'message',
                    type: 'string',
                    default: 'Successfully logged out'
                ),
            ],
        )
    )]
    #[OA\Parameter(
        name: 'Authorization',
        description: 'Bearer token for authentication',
        in: 'header',
        required: true,
        schema: new OA\Schema(type: 'string')
    )]
    public function logout(): JsonResponse
    {
        return $this->json(['message' => 'Successfully logged out']);
    }
}
