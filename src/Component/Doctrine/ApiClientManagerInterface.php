<?php

namespace Component\Doctrine;

use Component\Model\ApiClientInterface;
use Symfony\Component\Security\Core\User\UserInterface;

interface ApiClientManagerInterface
{
    public function findClientByCredentials(string $clientId, string $secret);

    public function clientExists(string $clientId) : bool ;
}