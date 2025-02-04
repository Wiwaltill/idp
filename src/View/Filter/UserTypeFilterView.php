<?php

namespace App\View\Filter;

use App\Entity\UserType;

class UserTypeFilterView {

    /** @var UserType[] */
    private array $types;

    /** @var UserType|null  */
    private ?UserType $currentType;

    /** @var bool */
    private bool $handleNull = false;

    public function __construct(array $types, ?UserType $type) {
        $this->types = $types;
        $this->currentType = $type;
    }

    /**
     * @return UserType[]
     */
    public function getTypes(): array {
        return $this->types;
    }

    /**
     * @return UserType|null
     */
    public function getCurrentType(): ?UserType {
        return $this->currentType;
    }

    public function getHandleNull(): bool {
        return $this->handleNull;
    }

    public function setHandleNull(bool $handleNull): UserTypeFilterView {
        $this->handleNull = $handleNull;
        return $this;
    }

}