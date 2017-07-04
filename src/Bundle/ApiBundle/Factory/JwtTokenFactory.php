<?php

namespace ApiBundle\Factory;

use ApiBundle\Entity\User;
use Component\Doctrine\UserProviderInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;

class JwtTokenFactory
{
    const EXPIRATION_TIME = 31536000;

    /**
     * @var UserProviderInterface
     */
    private $userManager;
    /**
     * @var UserPasswordEncoderInterface
     */
    private $passwordEncoder;
    /**
     * @var JWTEncoderInterface
     */
    private $encoder;

    public function __construct(
        UserProviderInterface $userManager,
        UserPasswordEncoderInterface $passwordEncoder,
        JWTEncoderInterface $encoder
    ) {
        $this->userManager = $userManager;
        $this->passwordEncoder = $passwordEncoder;
        $this->encoder = $encoder;
    }

    public function createToken($username, $password)
    {
        /** @var User $user */
        $user = $this->userManager->findUserByUsername($username);

        if (!$user) {
            throw new NotFoundHttpException();
        }

        $isValid = $this->passwordEncoder->isPasswordValid($user, $password);

        if (!$isValid) {
            throw new BadCredentialsException();
        }

        return $this->encoder
            ->encode(
                [
                    'username' => $user->getUsername(),
                    'exp'      => time() + self::EXPIRATION_TIME,
                ]
            );
    }
}