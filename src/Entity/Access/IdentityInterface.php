<?php

namespace App\Entity\Access;

interface IdentityInterface
{
    public function getUserId(): string;
    public function getUserEmail(): string;
}
