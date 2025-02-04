<?php

namespace App\View\Filter;

use App\Entity\UserType;
use App\Repository\UserTypeRepositoryInterface;
use App\Utils\ArrayUtils;

class UserTypeFilter {
    private UserTypeRepositoryInterface $userTypeRepository;

    public function __construct(UserTypeRepositoryInterface $userTypeRepository) {
        $this->userTypeRepository = $userTypeRepository;
    }

    public function handle($userType, ?UserType $defaultType = null) {
        $types = ArrayUtils::createArrayWithKeys($this->userTypeRepository->findAll(), function(UserType $type) {
            return (string)$type->getUuid();
        });

        if($userType === null || is_numeric($userType)) {
            $type = $defaultType;
        } else {
            $type = $types[$userType] ?? $defaultType;
        }

        return new UserTypeFilterView($types, $type);
    }
}