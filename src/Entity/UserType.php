<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Ramsey\Uuid\Uuid;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity()
 * @UniqueEntity(fields={"alias"})
 */
class UserType {

    use IdTrait;
    use UuidTrait;

    /**
     * @ORM\Column(type="string")
     * @Assert\NotBlank()
     */
    private $name;

    /**
     * @ORM\Column(type="string", unique=true)
     * @Assert\NotBlank()
     */
    private $alias;

    /**
     * @var string[]
     * @ORM\Column(type="json")
     * @Assert\Count(min="1")
     * @Serializer\Type("array<string>")
     */
    private $eduPerson;

    /**
     * @ORM\OneToMany(targetEntity="User", mappedBy="type")
     * @Serializer\Exclude()
     */
    private $users;

    /**
     * @ORM\ManyToMany(targetEntity="ServiceProvider")
     * @ORM\JoinTable(
     *  joinColumns={@ORM\JoinColumn(onDelete="CASCADE")},
     *  inverseJoinColumns={@ORM\JoinColumn(onDelete="CASCADE")}
     * )
     * @Serializer\Exclude()
     */
    private $enabledServices;

    /**
     * @ORM\OneToMany(targetEntity="ServiceAttributeUserTypeValue", mappedBy="userType")
     * @Serializer\Exclude()
     */
    private $attributes;

    /**
     * @ORM\OneToMany(targetEntity="ActiveDirectorySyncOption", mappedBy="userType")
     * @Serializer\Exclude()
     */
    private $syncOptions;

    /**
     * @ORM\Column(type="boolean")
     * @Serializer\Exclude()
     */
    private $canChangeName = true;

    /**
     * @ORM\Column(type="boolean")
     * @Serializer\Exclude()
     */
    private $canChangeEmail = true;

    /**
     * @ORM\Column(type="boolean")
     * @Serializer\Exclude()
     * @var bool
     */
    private $canLinkStudents = false;

    /**
     * @ORM\Column(type="boolean")
     * @var bool
     */
    private $isBuiltIn = false;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @var string|null
     */
    private $icon;

    public function __construct() {
        $this->uuid = Uuid::uuid4();

        $this->enabledServices = new ArrayCollection();
        $this->users = new ArrayCollection();
        $this->syncOptions = new ArrayCollection();
        $this->attributes = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function getName() {
        return $this->name;
    }

    /**
     * @param string $name
     * @return UserType
     */
    public function setName($name) {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getAlias() {
        return $this->alias;
    }

    /**
     * @param string $alias
     * @return UserType
     */
    public function setAlias($alias) {
        $this->alias = $alias;
        return $this;
    }

    /**
     * @return string[]
     */
    public function getEduPerson() {
        return $this->eduPerson;
    }

    /**
     * @param string[] $eduPerson
     * @return UserType
     */
    public function setEduPerson(array $eduPerson) {
        sort($eduPerson);

        $this->eduPerson = $eduPerson;
        return $this;
    }

    /**
     * @param User $user
     */
    public function addUser(User $user) {
        $this->users->add($user);
    }

    /**
     * @param User $user
     */
    public function removeUser(User $user) {
        $this->users->removeElement($user);
    }

    /**
     * @return Collection
     */
    public function getUsers(): Collection {
        return $this->users;
    }

    /**
     * @param ServiceProvider $serviceProvider
     */
    public function addEnabledService(ServiceProvider $serviceProvider) {
        $this->enabledServices->add($serviceProvider);
    }

    /**
     * @param ServiceProvider $serviceProvider
     */
    public function removeEnabledService(ServiceProvider $serviceProvider) {
        $this->enabledServices->removeElement($serviceProvider);
    }

    /**
     * @return Collection
     */
    public function getEnabledServices(): Collection {
        return $this->enabledServices;
    }

    /**
     * @return Collection
     */
    public function getAttributes(): Collection {
        return $this->attributes;
    }

    /**
     * @return Collection
     */
    public function getSyncOptions(): Collection {
        return $this->syncOptions;
    }

    /**
     * @return bool
     */
    public function canChangeName() {
        return $this->canChangeName;
    }

    /**
     * @param bool $canChangeName
     * @return UserType
     */
    public function setCanChangeName($canChangeName) {
        $this->canChangeName = $canChangeName;
        return $this;
    }

    /**
     * @return bool
     */
    public function canChangeEmail() {
        return $this->canChangeEmail;
    }

    /**
     * @param bool $canChangeEmail
     * @return UserType
     */
    public function setCanChangeEmail($canChangeEmail) {
        $this->canChangeEmail = $canChangeEmail;
        return $this;
    }

    /**
     * @return bool
     */
    public function isCanLinkStudents(): bool {
        return $this->canLinkStudents;
    }

    /**
     * @param bool $canLinkStudents
     * @return UserType
     */
    public function setCanLinkStudents(bool $canLinkStudents): UserType {
        $this->canLinkStudents = $canLinkStudents;
        return $this;
    }

    /**
     * @return bool
     */
    public function isBuiltIn(): bool {
        return $this->isBuiltIn;
    }

    /**
     * @param bool $isBuiltIn
     * @return UserType
     */
    public function setIsBuiltIn(bool $isBuiltIn): UserType {
        $this->isBuiltIn = $isBuiltIn;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getIcon(): ?string {
        return $this->icon;
    }

    /**
     * @param string|null $icon
     * @return UserType
     */
    public function setIcon(?string $icon): UserType {
        $this->icon = $icon;
        return $this;
    }
}