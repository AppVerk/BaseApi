<?php

namespace ApiBundle\Entity;

use Component\Model\ApiClientInterface;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Component\Model\ApiAccessTokenInterface;

/**
 * @Serializer\ExclusionPolicy("all")
 * @ORM\Entity(repositoryClass="ApiBundle\Repository\UserRepository")
 */
class ApiAccessToken implements ApiAccessTokenInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string")
     */
    private $accessToken;

    /**
     * @ORM\ManyToOne(targetEntity="ApiClient")
     * @ORM\JoinColumn(nullable=false)
     */
    protected $client;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     */
    protected $user;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getAccessToken()
    {
        return $this->accessToken;
    }

    /**
     * @param mixed $accessToken
     */
    public function setAccessToken($accessToken)
    {
        $this->accessToken = $accessToken;
    }

    /**
     * Set client
     *
     * @param ApiClientInterface $client
     *
     * @return ApiAccessToken
     */
    public function setClient(ApiClientInterface $client)
    {
        $this->client = $client;

        return $this;
    }

    /**
     * Get client
     *
     * @return ApiClient
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * Set user
     *
     * @param User $user
     *
     * @return ApiAccessToken
     */
    public function setUser(User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }
}
