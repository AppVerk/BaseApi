<?php

namespace ApiBundle\Doctrine;

use ApiBundle\Repository\UserRepository;
use Component\Doctrine\AbstractManager;
use Component\Doctrine\ApiClientManagerInterface;
use Doctrine\Common\Persistence\ObjectRepository;

class ApiClientManager extends AbstractManager implements ApiClientManagerInterface
{
    public function findClientByCredentials(string $clientId, string $secret)
    {
        return $this->getRepository()->findOneBy(
            [
                'clientId' => $clientId,
                'secret'   => $secret,
            ]
        );
    }

    /**
     * @return ObjectRepository
     */
    public function getRepository(): ObjectRepository
    {
        /** @var UserRepository $userRepository */
        $userRepository = $this->objectManager->getRepository($this->className);

        return $userRepository;
    }

    public function clientExists(string $clientId): bool
    {
        return ($this->getRepository()->findOneBy(['clientId' => $clientId]) !== null);
    }

    public function removeByClientId(string $clientId)
    {
        $client = $this->getRepository()->findOneBy(
            [
                'clientId' => $clientId,
            ]
        );

        $this->objectManager->remove($client);
        $this->objectManager->flush();
    }

}
