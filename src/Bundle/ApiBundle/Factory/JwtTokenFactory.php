<?php

namespace ApiBundle\Factory;

use ApiBundle\Doctrine\ApiAccessTokenManager;
use ApiBundle\Entity\ApiClient;
use ApiBundle\Entity\User;
use Component\Doctrine\ApiClientManagerInterface;
use Component\Doctrine\UserProviderInterface;
use Component\Model\ApiAccessTokenInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;

class JwtTokenFactory
{
    const EXPIRATION_TIME = 31536000;

    /**
     * @var UserProviderInterface
     */
    private $userManager;
    /**
     * @var UserPasswordEncoderInterface
     */
    private $passwordEncoder;
    /**
     * @var JWTEncoderInterface
     */
    private $encoder;
    /**
     * @var ApiClientManagerInterface
     */
    private $apiClientManager;
    /**
     * @var ApiAccessTokenInterface
     */
    private $apiAccessToken;

    public function __construct(
        UserProviderInterface $userManager,
        UserPasswordEncoderInterface $passwordEncoder,
        JWTEncoderInterface $encoder,
        ApiClientManagerInterface $apiClientManager,
        ApiAccessTokenManager $apiAccessToken
    ) {
        $this->userManager = $userManager;
        $this->passwordEncoder = $passwordEncoder;
        $this->encoder = $encoder;
        $this->apiClientManager = $apiClientManager;
        $this->apiAccessToken = $apiAccessToken;
    }

    public function createToken($username, $password, $clientId, $secret)
    {
        if ($clientId === null || $secret === null) {
            throw new NotFoundHttpException("Client data missed");
        }
        /** @var ApiClient $client */
        $apiClient = $this->apiClientManager->findClientByCredentials($clientId, $secret);
        /** @var User $user */
        $user = $this->userManager->findUserByUsername($username);

        if (!$apiClient) {
            throw new NotFoundHttpException("Client not found");
        }

        if (!$apiClient->isEnabled()) {
            throw new NotFoundHttpException("Client is blocked");
        }

        if (!$user) {
            throw new NotFoundHttpException('User not found');
        }

        $isValid = $this->passwordEncoder->isPasswordValid($user, $password);

        if (!$isValid) {
            throw new BadCredentialsException();
        }

        $token = $this->encoder
            ->encode(
                [
                    'username' => $user->getUsername(),
                    'client'   => $apiClient->getClientId(),
                    'exp'      => time() + self::EXPIRATION_TIME,
                ]
            );

        $this->apiAccessToken->bindTokenToUser($token, $user, $apiClient);

        return $token;
    }
}
