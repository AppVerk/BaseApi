<?php

namespace ApiBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;

class ProfileController extends AbstractController
{
    /**
     * @Route("/api/profile/me")
     * @Method("GET")
     */
    public function meAction()
    {
        return new JsonResponse(['user' => $this->getUser()]);
    }
}
