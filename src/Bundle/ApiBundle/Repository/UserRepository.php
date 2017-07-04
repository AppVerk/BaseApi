<?php

namespace ApiBundle\Repository;

use ApiBundle\Entity\User;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;

class UserRepository extends EntityRepository implements UserLoaderInterface
{
    public function loadUserByUsername($username)
    {
        $user = $this->findUserByUsername($username);

        if (!$user) {
            $user = $this->findUserByEmail($username);
        }

        if (!$user) {
            throw new UsernameNotFoundException(sprintf('Email "%s" does not exist.', $username));
        }

        return $user;
    }

    /**
     * @param $username
     * @return User
     */
    public function findUserByUsername($username)
    {
        /** @var User $user */
        $user = $this->findOneBy(
            [
                'username' => $username,
            ]
        );

        return $user;
    }

    /**
     * @param $email
     * @return User
     */
    public function findUserByEmail($email)
    {
        /** @var User $user */
        $user = $this->findOneBy(
            [
                'email' => $email,
            ]
        );

        return $user;
    }
}
