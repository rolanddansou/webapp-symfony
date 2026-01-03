<?php

namespace App\Doctrine\SQLFilter;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\Filter\SQLFilter;

class EnabledDisabledFilter extends SQLFilter
{
    public function addFilterConstraint(ClassMetadata $targetEntity, string $targetTableAlias): string
    {
        if($targetEntity->getReflectionClass()->hasProperty("isEnabled")){
            $enabled = $this->getParameter('enabled');
            return sprintf('%s.is_enabled = %s', $targetTableAlias, $enabled);
        }

        return '';
    }
}
