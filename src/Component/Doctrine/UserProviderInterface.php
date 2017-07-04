<?php

namespace Component\Doctrine;

use Symfony\Component\Security\Core\User\UserInterface;

interface UserProviderInterface
{
    public function findUserByUsername(string $username): UserInterface;
}