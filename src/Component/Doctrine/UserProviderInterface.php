<?php

namespace Component\Doctrine;

interface UserProviderInterface
{
    public function findUserByUsername(string $username);
}
