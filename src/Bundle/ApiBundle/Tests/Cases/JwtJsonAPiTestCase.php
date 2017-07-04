<?php

namespace ApiBundle\Tests\Cases;

use ApiBundle\Factory\JwtTokenFactory;
use AppVerk\ApiTestCasesBundle\Api\Cases\JsonApiTestCase;

abstract class JwtJsonApiTestCase extends JsonApiTestCase
{
    protected function authenticateFixtureUser(string $userFixturePath = 'cases/jwt_user.yml')
    {
        $this->loadFixturesFromFile($userFixturePath);
        $token = $this->getService('lexik_jwt_authentication.encoder')->encode(
            [
                'username' => 'test',
                'exp'      => time() + JwtTokenFactory::EXPIRATION_TIME,
            ]
        );

        self::$staticClient->setDefaultOption('headers/Authorization', 'Bearer '.$token);
    }
}