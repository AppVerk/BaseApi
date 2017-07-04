<?php

namespace ApiBundle\Tests\Controller;

use ApiBundle\Tests\Cases\JwtJsonApiTestCase;
use Symfony\Component\HttpFoundation\Response;

class UserControllerApiTest extends JwtJsonApiTestCase
{
    public function testGetUserData()
    {
        $this->authenticateFixtureUser('cases/jwt_user.yml');
        $response = $this->client->get('/api/user/me');

        $this->assertResponse($response, 'user/me', Response::HTTP_OK);
    }
}