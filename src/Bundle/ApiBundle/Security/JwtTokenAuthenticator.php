<?php

namespace ApiBundle\Security;

use ApiBundle\Doctrine\UserManager;
use AppVerk\ApiExceptionBundle\Component\Factory\ApiProblemFactoryInterface;
use AppVerk\ApiExceptionBundle\Component\Factory\ResponseFactoryInterface;
use AppVerk\ApiExceptionBundle\Exception\ApiProblemException;
use Component\Doctrine\ApiClientManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Lexik\Bundle\JWTAuthenticationBundle\TokenExtractor\AuthorizationHeaderTokenExtractor;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;

class JwtTokenAuthenticator extends AbstractGuardAuthenticator
{
    private $jwtEncoder;
    private $responseFactory;
    private $userManager;
    /**
     * @var ApiClientManagerInterface
     */
    private $apiClientManager;
    /**
     * @var ApiProblemFactoryInterface
     */
    private $apiProblemFactory;

    public function __construct(
        JWTEncoderInterface $jwtEncoder,
        ResponseFactoryInterface $responseFactory,
        UserManager $userManager,
        ApiClientManagerInterface $apiClientManager,
        ApiProblemFactoryInterface $apiProblemFactory
    ) {
        $this->jwtEncoder = $jwtEncoder;
        $this->responseFactory = $responseFactory;
        $this->userManager = $userManager;
        $this->apiClientManager = $apiClientManager;
        $this->apiProblemFactory = $apiProblemFactory;
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
        try {
            $data = $this->jwtEncoder->decode($credentials);
        } catch (\Exception $e) {
            $ApiProblem = $this->apiProblemFactory->create(401, null, 'security.token.error.invalid');
            throw new ApiProblemException($ApiProblem);
        }

        if ($data === false) {
            $ApiProblem = $this->apiProblemFactory->create(401, null, 'security.token.error.invalid');
            throw new ApiProblemException($ApiProblem);
        }

        if (!isset($data['client'])) {
            $ApiProblem = $this->apiProblemFactory->create(401, null, 'security.token.error.data_not_found');
            throw new ApiProblemException($ApiProblem);
        }

        $client = $this->apiClientManager->clientExists($data['client']);

        if ($client === false) {
            $ApiProblem = $this->apiProblemFactory->create(401, null, 'security.token.error.client_not_found');
            throw new ApiProblemException($ApiProblem);
        }

        $username = $data['username'];

        return $this->userManager->findUserByUsername($username);
    }

    public function checkCredentials($credentials, UserInterface $user)
    {
        return true;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        $ApiProblem = $this->apiProblemFactory->create(401, null, $exception->getMessageKey());

        return $this->responseFactory->createResponse($ApiProblem);
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
    }

    public function start(Request $request, AuthenticationException $authException = null)
    {
        $message = $authException ? $authException->getMessageKey() : 'Missing credentials';
        $ApiProblem = $this->apiProblemFactory->create(401, null, $message);

        return $this->responseFactory->createResponse($ApiProblem);
    }

    public function supportsRememberMe()
    {
        return false;
    }
}
