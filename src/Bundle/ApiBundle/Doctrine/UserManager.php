<?php

namespace ApiBundle\Doctrine;

use ApiBundle\Repository\UserRepository;
use Component\Doctrine\AbstractManager;
use Component\Doctrine\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserManager extends AbstractManager implements UserProviderInterface
{
    public function loadUserByUsername(string $username): UserInterface
    {
        return $this->getRepository()->loadUserByUsername($username);
    }

    /**
     * @return UserRepository
     */
    public function getRepository(): UserRepository
    {
        /** @var UserRepository $userRepository */
        $userRepository = $this->objectManager->getRepository($this->className);

        return $userRepository;
    }

    public function findUserByUsername(string $username): UserInterface
    {
        return $this->getRepository()->findUserByUsername($username);
    }

}