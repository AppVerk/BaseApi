<?php

namespace Component\Doctrine;

use Component\Model\ApiClientInterface;
use Symfony\Component\Security\Core\User\UserInterface;

interface ApiAccessTokenManagerInterface
{
    public function bindTokenToUser(string $token, UserInterface $user, ApiClientInterface $apiClient);
}
