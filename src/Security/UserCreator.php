<?php

namespace App\Security;

use AdAuth\Response\AuthenticationResponse;
use App\ActiveDirectory\OptionResolver;
use App\Entity\ActiveDirectoryGradeSyncOption;
use App\Entity\ActiveDirectorySyncOption;
use App\Entity\ActiveDirectoryUser;
use App\Entity\UserType;
use App\Repository\ActiveDirectoryGradeSyncOptionRepositoryInterface;
use App\Repository\ActiveDirectorySyncOptionRepositoryInterface;
use Doctrine\ORM\EntityManager;

/**
 * Helper which creates users after successful Active Directory authentication.
 */
class UserCreator {
    /** @var ActiveDirectorySyncOption[] */
    private $syncOptions = null;

    /** @var ActiveDirectoryGradeSyncOption[] */
    private $gradeSyncOptions = null;

    /** @var OptionResolver */
    private $optionsResolver;

    /** @var ActiveDirectorySyncOptionRepositoryInterface  */
    private $syncOptionRepository;

    /** @var ActiveDirectoryGradeSyncOptionRepositoryInterface  */
    private $gradeSyncOptionRepository;

    /**
     * @param EntityManager $entityManager
     */
    public function __construct(ActiveDirectorySyncOptionRepositoryInterface $syncOptionRepository,
                                ActiveDirectoryGradeSyncOptionRepositoryInterface $gradeSyncOptionRepository, OptionResolver $optionResolver) {
        $this->syncOptionRepository = $syncOptionRepository;
        $this->gradeSyncOptionRepository = $gradeSyncOptionRepository;
        $this->optionsResolver = $optionResolver;
    }

    private function initialise() {
        if ($this->syncOptions === null) {
            $this->syncOptions = $this->syncOptionRepository
                ->findAll();
        }

        if ($this->gradeSyncOptions === null) {
            $this->gradeSyncOptions = $this->gradeSyncOptionRepository
                ->findAll();
        }
    }

    /**
     * Determines whether the user can be imported from Active Directory.
     *
     * @param AuthenticationResponse $response
     * @return bool
     */
    public function canCreateUser(AuthenticationResponse $response) {
        $this->initialise();
        return $this->getTargetUserType($response) !== null;
    }

    /**
     * @param AuthenticationResponse $response
     * @return UserType
     */
    private function getTargetUserType(AuthenticationResponse $response) {
        /** @var ActiveDirectorySyncOption $option */
        $option = $this->optionsResolver
            ->getOption($this->syncOptions, $response->getOu(), $response->getGroups());

        if ($option !== null) {
            return $option->getUserType();
        }

        return null;
    }

    /**
     * @param AuthenticationResponse $response
     * @return ActiveDirectoryUser
     */
    public function createUser(AuthenticationResponse $response, ActiveDirectoryUser $user = null) {
        $this->initialise();

        if ($user === null) {
            $user = new ActiveDirectoryUser();
        }

        $user->setUsername($response->getUsername());
        $user->setFirstname($response->getFirstname());
        $user->setLastname($response->getLastname());
        $user->setGrade($this->getGrade($response));
        $user->setSamAccountName($response->getUsername());
        $user->setType($this->getTargetUserType($response));
        $user->setEmail($response->getEmail());

        return $user;
    }

    /**
     * @param AuthenticationResponse $response
     * @return string|null
     */
    private function getGrade(AuthenticationResponse $response) {
        /** @var ActiveDirectoryGradeSyncOption $option */
        $option = $this->optionsResolver
            ->getOption($this->gradeSyncOptions, $response->getOu(), $response->getGroups());

        if($option !== null) {
            return $option->getGrade();
        }

        return null;
    }
}