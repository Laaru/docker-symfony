<?php

namespace App\Controller\Api\Auth;

use App\Entity\DTO\UserRegisterDTO;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\User\UserRegisterService;
use Gesdinet\JWTRefreshTokenBundle\Generator\RefreshTokenGeneratorInterface;
use Gesdinet\JWTRefreshTokenBundle\Model\RefreshTokenManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;

#[Route(
    '/api/auth',
    name: 'api_auth_',
    defaults: ['show_exception_as_json' => true],
)]
class RegisterController extends AbstractController
{
    #[Route(
        '/register',
        name: 'register',
        methods: ['POST'],
    )]
    #[OA\Tag(name: 'auth')]
    #[OA\RequestBody(content: new Model(type: UserRegisterDTO::class))]
    #[OA\Response(
        response: 200,
        description: 'Success',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: 'user',
                    type: 'array',
                    items: new OA\Items(ref: new Model(type: User::class, groups: ['detail']))
                ),
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
            ],
        )
    )]
    public function register(
        #[MapRequestPayload]
        UserRegisterDTO $userRegisterDTO,
        UserRepository $userRepository,
        UserRegisterService $userRegisterService,
        JWTTokenManagerInterface $jwtManager,
        RefreshTokenGeneratorInterface $refreshTokenGenerator,
        RefreshTokenManagerInterface $refreshTokenManager,
        ParameterBagInterface $parameterBag,
    ): Response {
        $user = $userRegisterService->register($userRegisterDTO);

        $token = $jwtManager->create($user);
        $refreshToken = $refreshTokenGenerator->createForUserWithTtl(
            $user,
            $parameterBag->get('gesdinet_jwt_refresh_token.ttl')
        );
        $refreshTokenManager->save($refreshToken);

        return $this->json(
            data: [
                'user' => $userRepository->normalize($user),
                'token' => $token,
                'refreshToken' => $refreshToken->getRefreshToken(),
            ]
        );
    }
}
