<?php

namespace App\Service\Token;

use App\Entity\User;
use Gesdinet\JWTRefreshTokenBundle\Generator\RefreshTokenGeneratorInterface;
use Gesdinet\JWTRefreshTokenBundle\Model\RefreshTokenManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

readonly class RefreshTokenService
{
    public function __construct(
        private RefreshTokenGeneratorInterface $refreshTokenGenerator,
        private RefreshTokenManagerInterface $refreshTokenManager,
        private ParameterBagInterface $parameterBag
    ) {}

    public function getRefreshTokenForUser(User $user): ?string
    {
        $ttl = $this->parameterBag->get('gesdinet_jwt_refresh_token.ttl');
        if (!is_numeric($ttl)) {
            $ttl = 86400;
        }

        $refreshToken = $this->refreshTokenGenerator->createForUserWithTtl(
            $user,
            (int) $ttl
        );
        $this->refreshTokenManager->save($refreshToken);

        return $refreshToken->getRefreshToken();
    }
}
