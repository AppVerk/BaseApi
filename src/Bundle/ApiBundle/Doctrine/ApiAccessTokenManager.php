<?php

namespace ApiBundle\Doctrine;

use ApiBundle\Entity\ApiAccessToken;
use ApiBundle\Repository\UserRepository;
use Component\Doctrine\AbstractManager;
use Component\Doctrine\ApiAccessTokenManagerInterface;
use Component\Doctrine\ManagerInterface;
use Component\Model\ApiClientInterface;
use Doctrine\Common\Persistence\ObjectRepository;
use Symfony\Component\Security\Core\User\UserInterface;

class ApiAccessTokenManager extends AbstractManager implements ManagerInterface, ApiAccessTokenManagerInterface
{
    /**
     * @return ObjectRepository
     */
    public function getRepository(): ObjectRepository
    {
        /** @var UserRepository $userRepository */
        $userRepository = $this->objectManager->getRepository($this->className);

        return $userRepository;
    }

    public function bindTokenToUser(string $token, UserInterface $user, ApiClientInterface $apiClient)
    {
        $apiAccessToken = new ApiAccessToken();
        $apiAccessToken->setAccessToken($token);
        $apiAccessToken->setUser($user);
        $apiAccessToken->setClient($apiClient);

        $this->objectManager->persist($apiAccessToken);
        $this->objectManager->flush();
    }

}