<?php

namespace ApiBundle\Security;

use ApiBundle\Doctrine\UserManager;
use AppVerk\ApiExceptionBundle\Api\ApiProblem;
use AppVerk\ApiExceptionBundle\Component\Factory\ResponseFactoryInterface;
use Component\Doctrine\ApiAccessTokenManagerInterface;
use Component\Doctrine\ApiClientManagerInterface;
use Component\Model\ApiAccessTokenInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Lexik\Bundle\JWTAuthenticationBundle\TokenExtractor\AuthorizationHeaderTokenExtractor;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
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

    public function __construct(
        JWTEncoderInterface $jwtEncoder,
        ResponseFactoryInterface $responseFactory,
        UserManager $userManager,
        ApiClientManagerInterface $apiClientManager
    ) {
        $this->jwtEncoder = $jwtEncoder;
        $this->responseFactory = $responseFactory;
        $this->userManager = $userManager;
        $this->apiClientManager = $apiClientManager;
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
        try{
            $data = $this->jwtEncoder->decode($credentials);
        }catch (\Exception $e){
            throw new CustomUserMessageAuthenticationException('Invalid Token');
        }

        if ($data === false) {
            throw new CustomUserMessageAuthenticationException('Invalid Token');
        }

        if(!isset($data['client'])){
            throw new CustomUserMessageAuthenticationException('Client data not found in token');
        }

        $client = $this->apiClientManager->clientExists($data['client']);

        if($client === false){
            throw new CustomUserMessageAuthenticationException('Client doesn\'t exist');
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
