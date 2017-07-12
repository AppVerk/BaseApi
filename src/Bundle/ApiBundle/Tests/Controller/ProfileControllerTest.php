<?php

namespace ApiBundle\Tests\Controller;

use ApiBundle\Tests\Cases\JwtJsonApiTestCase;
use Symfony\Component\HttpFoundation\Response;

class ProfileControllerApiTest extends JwtJsonApiTestCase
{
    public function testMeActionSuccess()
    {
        $this->authenticateFixtureUser('profile/user.yml');
        $response = $this->client->get('/api/profile/me');

        echo $response->getEffectiveUrl();
        $this->assertResponse($response, 'profile/me/success', Response::HTTP_OK);
    }

    public function testMeActionFailedInvalidToken()
    {
        $this->authenticateFixtureUser('profile/user.yml');
        self::$staticClient->setDefaultOption('headers/Authorization', 'Bearer xxx');

        $response = $this->client->get('/api/profile/me');

        $this->assertResponse($response, 'profile/me/failed_invalid_token', Response::HTTP_UNAUTHORIZED);
    }

    public function testMeActionFailedClientDataNotFound()
    {
        $this->authenticateFixtureUser('profile/user.yml', false);

        $response = $this->client->get('/api/profile/me');

        $this->assertResponse($response, 'profile/me/failed_client_data_not_found', Response::HTTP_UNAUTHORIZED);
    }

    public function testMeActionFailedClientDeleted()
    {
        $data = $this->authenticateFixtureUser('profile/user.yml');
        $this->getService('ApiBundle\Doctrine\ApiClientManager')->removeByClientId($data['client']);

        $response = $this->client->get('/api/profile/me');

        $this->assertResponse($response, 'profile/me/failed_client_deleted', Response::HTTP_UNAUTHORIZED);
    }
}