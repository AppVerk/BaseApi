<?php

namespace ApiBundle\Security;

use AppVerk\ApiExceptionBundle\Api\ApiProblem;
use AppVerk\ApiExceptionBundle\Component\Factory\ResponseFactoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Lexik\Bundle\JWTAuthenticationBundle\TokenExtractor\AuthorizationHeaderTokenExtractor;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;

class JwtTokenAuthenticator extends AbstractGuardAuthenticator
{
    private $jwtEncoder;
    private $entityManager;
    private $responseFactory;

    public function __construct(
        JWTEncoderInterface $jwtEncoder,
        EntityManagerInterface $entityManager,
        ResponseFactoryInterface $responseFactory
    ) {
        $this->jwtEncoder = $jwtEncoder;
        $this->entityManager = $entityManager;
        $this->responseFactory = $responseFactory;
    }

    public function getCredentials(Request $request)
    {
        $extractor = new AuthorizationHeaderTokenExtractor(
            'Bearer',
            'Authorization'
        );

        $token = $extractor->extract($request);

        if (!$token) {
            return;
        }

        return $token;
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        $data = $this->jwtEncoder->decode($credentials);

        if ($data === false) {
            throw new CustomUserMessageAuthenticationException('Invalid Token');
        }

        $username = $data['username'];

        return $this->entityManager
            ->getRepository('ApiBundle:User')
            ->findOneBy(['username' => $username]);
    }

    public function checkCredentials($credentials, UserInterface $user)
    {
        return true;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        $ApiProblem = new ApiProblem(401);
        // you could translate this
        $ApiProblem->set('detail', $exception->getMessageKey());

        return $this->responseFactory->createResponse($ApiProblem);
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
    }

    public function start(Request $request, AuthenticationException $authException = null)
    {
        $ApiProblem = new ApiProblem(401);
        // you could translate this
        $message = $authException ? $authException->getMessageKey() : 'Missing credentials';
        $ApiProblem->set('detail', $message);

        return $this->responseFactory->createResponse($ApiProblem);
    }

    public function supportsRememberMe()
    {
        return false;
    }
}
