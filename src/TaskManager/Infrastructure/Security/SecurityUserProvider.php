<?php

namespace App\TaskManager\Infrastructure\Security;

use Exception;
use Symfony\Component\Security\Core\User\UserInterface;
use App\TaskManager\Infrastructure\Security\SecurityUser;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use App\TaskManager\Infrastructure\Repository\Doctrine\UserRepository;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;

class SecurityUserProvider implements UserProviderInterface
{
    private $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function loadUserByIdentifier($username): UserInterface
    {
        return $this->fetchUser($username);
    }

    public function refreshUser(UserInterface $user)
    {
        if (!$user instanceof SecurityUser) {
            throw new UnsupportedUserException(
                sprintf('Instances of "%s" are not supported.', get_class($user))
            );
        }

        $username = $user->getUserIdentifier();

        return $this->fetchUser($username);
    }

    public function supportsClass($class)
    {
        return SecurityUser::class === $class;
    }

    private function fetchUser($username)
    {
        if (null === ($user = $this->userRepository->findOneBy(['email' => $username]))) {
            throw new Exception(
                sprintf('Username "%s" does not exist.', $username)
            );
        }

        return new SecurityUser($user);
    }
}