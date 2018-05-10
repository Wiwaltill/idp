<?php

namespace App\Import\User;

use App\Import\AbstractImporter;
use App\Entity\ServiceAttribute;
use App\Entity\User;
use App\Entity\UserType;
use App\Service\AttributePersister;
use Doctrine\ORM\EntityManager;
use JMS\Serializer\SerializerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserImporter extends AbstractImporter {

    private $entityManager;

    private $attributePersister;

    public function __construct(AttributePersister $attributePersister, EntityManager $manager, SerializerInterface $serialiser, ValidatorInterface $validator, LoggerInterface $logger = null) {
        parent::__construct($serialiser, $validator, $logger);

        $this->attributePersister = $attributePersister;
        $this->entityManager = $manager;
    }

    public function import($json) {
        /** @var UserImportData $userImportData */
        $userImportData = $this->parseJson($json, UserImportData::class);

        /** @var UserType[] $types */
        $types = $this->entityManager->getRepository(UserType::class)
            ->findAll();
        /** @var ServiceAttribute[] $attributes */
        $attributes = $this->entityManager->getRepository(ServiceAttribute::class)
            ->findAll();

        $this->entityManager->beginTransaction();

        try {
            foreach($userImportData->getUsers() as $userData) {
                $user = $this->entityManager->getRepository(User::class)
                    ->findOneByUsername($userData->username);

                if($user === null) {
                    $user = (new User())
                        ->setUsername($userData->username);
                }

                $user->setFirstname($userData->firstname);
                $user->setLastname($userData->lastname);
                $user->setEmail($userData->email);

                foreach($types as $type) {
                    if($type->getAlias() === $userData->type) {
                        $user->setType($type);
                    }
                }

                $this->attributePersister->persistUserAttributes($userData->attributes, $user);
                $this->entityManager->persist($user);
            }

            $this->entityManager->flush();
            $this->entityManager->commit();
        } catch(\Exception $e) {
            $this->entityManager->rollback();
        }
    }
}