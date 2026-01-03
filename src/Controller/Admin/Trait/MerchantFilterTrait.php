<?php

namespace App\Controller\Admin\Trait;

use App\Entity\Merchant\MerchantAccount;
use Doctrine\ORM\QueryBuilder;

/**
 * Trait for filtering admin queries by merchant context
 * Use this in CRUD controllers to automatically filter data for merchant users
 */
trait MerchantFilterTrait
{
    /**
     * Check if current user is a merchant (not an admin)
     */
    protected function isMerchantUser(): bool
    {
        return $this->isGranted('ROLE_MERCHANT') && !$this->isGranted('ROLE_ADMIN');
    }

    /**
     * Get the MerchantAccount associated with the current user
     * Returns null if user is admin or has no associated merchant
     */
    protected function getCurrentMerchant(): ?MerchantAccount
    {
        if (!$this->isMerchantUser()) {
            return null;
        }

        $user = $this->getUser();
        if (!$user) {
            return null;
        }

        // Get Identity from JwtUser or direct Identity
        $identity = method_exists($user, 'getIdentity')
            ? $user->getIdentity()
            : $user;

        // Find MerchantAccount by user identity
        $entityManager = $this->container->get('doctrine')->getManager();
        $merchantRepo = $entityManager->getRepository(MerchantAccount::class);

        return $merchantRepo->findOneBy(['user' => $identity]);
    }

    /**
     * Apply merchant filter to a query builder
     * 
     * @param QueryBuilder $qb The query builder to filter
     * @param string $merchantAlias The alias used for the merchant in the query (e.g., 'entity.merchant')
     */
    protected function applyMerchantFilter(QueryBuilder $qb, string $merchantAlias): void
    {
        $merchant = $this->getCurrentMerchant();

        if ($merchant !== null) {
            $qb->andWhere("$merchantAlias = :currentMerchant")
                ->setParameter('currentMerchant', $merchant);
        }
    }
}
