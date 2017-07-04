<?php

namespace ApiBundle\Tests\Controller;

use AppVerk\ApiTestCasesBundle\Api\Cases\JsonApiTestCase;
use Symfony\Component\HttpFoundation\Response;

class SecurityControllerApiTest extends JsonApiTestCase
{
    public function testPostCreateToken()
    {
        $this->loadFixturesFromFile('cases/jwt_user.yml');
        $data = [
            '_username' => 'test',
            '_password' => 'test',
        ];

        $response = $this->client->post(
            '/api/security/token',
            [
                'body' => $data,
            ]
        );

        $this->assertResponse($response, 'token/create_response', Response::HTTP_OK);
    }
}