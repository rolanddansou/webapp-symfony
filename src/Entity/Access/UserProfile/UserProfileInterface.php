<?php

namespace App\Entity\Access\UserProfile;

interface UserProfileInterface
{
    public function getCustomerId(): ?string;
    public function getCustomerFullName(): string;
    public function getCustomerEmail(): string;
}
