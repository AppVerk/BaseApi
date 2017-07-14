<?php

namespace ApiBundle\Controller;

use ApiBundle\Factory\JwtTokenFactory;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class SecurityController extends AbstractController
{
    /**
     * @var JwtTokenFactory
     */
    private $jwtTokenFactory;

    public function __construct(JwtTokenFactory $jwtTokenFactory)
    {
        $this->jwtTokenFactory = $jwtTokenFactory;
    }

    /**
     * @Route("/api/security/token", name="security_token")
     * @Method("POST")
     */
    public function newTokenAction(Request $request)
    {
        $token = $this->jwtTokenFactory->createToken(
            $request->request->get('_username'),
            $request->request->get('_password'),
            $request->request->get('_client'),
            $request->request->get('_secret')
        );

        return new JsonResponse(['token' => $token]);
    }
}
