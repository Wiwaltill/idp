<?php

namespace App\Service;

use App\Entity\ServiceProvider;
use App\Entity\User;
use App\Entity\UserRole;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Resolves services which users are enabled for.
 */
class UserServiceProviderResolver {
    private $tokenStorage;

    public function __construct(TokenStorageInterface $tokenStorage) {
        $this->tokenStorage = $tokenStorage;
    }

    private function getUser() {
        $token = $this->tokenStorage->getToken();

        if($token === null) {
            return null;
        }

        $user = $token->getUser();
        return $user;
    }

    /**
     * Returns the list of services the currently loggedin user is enabled for.
     *
     * @return ArrayCollection
     */
    public function getServicesForCurrentUser() {
        $user = $this->getUser();
        return $this->getServices($user);
    }

    /**
     * Returns a list of services (ServiceProvider) the given user is enabled for.
     *
     * @param User|null $user
     * @return ArrayCollection
     */
    public function getServices(User $user = null) {
        if($user === null) {
            return new ArrayCollection();
        }

        /** @var ServiceProvider[] $userServices */
        $userServices = $user->getEnabledServices();
        /** @var ServiceProvider[] $typeServices */
        $typeServices = $user->getType()->getEnabledServices();

        $services = [ ];

        foreach($userServices as $service) {
            $services[$service->getId()] = $service;
        }

        foreach($typeServices as $service) {
            $services[$service->getId()] = $service;
        }

        /** @var UserRole[] $userRoles */
        $userRoles = $user->getUserRoles();

        foreach($userRoles as $role) {
            /** @var ServiceProvider[] $roleServices */
            $roleServices = $role->getEnabledServices();

            foreach($roleServices as $service) {
                $services[$service->getId()] = $service;
            }
        }

        // sort by name
        usort($services, function(ServiceProvider $serviceProviderA, ServiceProvider $serviceProviderB) {
            return strcmp($serviceProviderA->getName(), $serviceProviderB->getName());
        });

        return new ArrayCollection(array_values($services));
    }
}