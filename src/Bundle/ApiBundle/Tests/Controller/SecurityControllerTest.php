<?php

namespace ApiBundle\Tests\Controller;

use AppVerk\ApiTestCasesBundle\Api\Cases\JsonApiTestCase;
use Symfony\Component\HttpFoundation\Response;

class SecurityControllerApiTest extends JsonApiTestCase
{
    private $postData = [
        '_username' => 'test',
        '_password' => 'test',
        '_client'   => 1,
        '_secret'   => 'secret',
    ];

    public function testNewTokenActionSuccess()
    {
        $this->loadFixturesFromFile('security/user.yml');
        $this->loadFixturesFromFile('security/client.yml');

        $data = $this->postData;

        $response = $this->client->post('/api/security/token', ['body' => $data]);

        $this->assertResponse($response, 'security/newTokenAction/success', Response::HTTP_OK);
    }

    public function testNewTokenActionFailedClientNotFound()
    {
        $this->loadFixturesFromFile('security/user.yml');

        $data = $this->postData;

        $response = $this->client->post('/api/security/token', ['body' => $data]);

        $this->assertResponse($response, 'security/newTokenAction/failed_client_not_found', Response::HTTP_NOT_FOUND);
    }

    public function testNewTokenActionFailedClientBlocked()
    {
        $this->loadFixturesFromFile('security/user.yml');
        $this->loadFixturesFromFile('security/client_blocked.yml');

        $data = $this->postData;

        $response = $this->client->post('/api/security/token', ['body' => $data]);

        $this->assertResponse($response, 'security/newTokenAction/failed_client_blocked', Response::HTTP_NOT_FOUND);
    }

    public function testNewTokenActionFailedUserNotFound()
    {
        $this->loadFixturesFromFile('security/client.yml');

        $data = $this->postData;

        $response = $this->client->post('/api/security/token', ['body' => $data]);

        $this->assertResponse($response, 'security/newTokenAction/failed_user_not_found', Response::HTTP_NOT_FOUND);
    }
}