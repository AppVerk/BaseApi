<?php

namespace ApiBundle\Tests\Cases;

use ApiBundle\Entity\ApiClient;
use ApiBundle\Factory\JwtTokenFactory;
use AppVerk\ApiTestCasesBundle\Api\Cases\JsonApiTestCase;

abstract class JwtJsonApiTestCase extends JsonApiTestCase
{
    protected function authenticateFixtureUser(
        string $userFixturePath = 'cases/jwt_user.yml',
        $withClient = true,
        $expired = JwtTokenFactory::EXPIRATION_TIME
    )
    {
        $this->loadFixturesFromFile($userFixturePath);

        $tokenData = [
            'username' => 'test',
            'exp'      => time() + $expired,
        ];

        if($withClient){
            $this->loadFixturesFromFile('cases/jwt_client.yml');

            $apiClient = $this->getService('doctrine.orm.entity_manager')
                ->getRepository(ApiClient::class)->findOneBy([
                    'clientId' => 1
                ]);

            $tokenData['client'] = $apiClient->getClientId();
        }

        $token = $this->getService('lexik_jwt_authentication.encoder')->encode($tokenData);

        self::$staticClient->setDefaultOption('headers/Authorization', 'Bearer '.$token);

        return $tokenData;
    }
}