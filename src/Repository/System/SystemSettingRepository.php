<?php

namespace App\Repository\System;

use App\Entity\System\SystemSetting;
use App\Repository\Traits\CacheableRepositoryTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SystemSetting>
 */
class SystemSettingRepository extends ServiceEntityRepository
{
    use CacheableRepositoryTrait;
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SystemSetting::class);
    }
    
    /**
     * Récupère tous les paramètres système avec cache agressif.
     * Settings changent rarement = cache long.
     * 
     * @return SystemSetting[]
     */
    public function findAllCached(): array
    {
        return $this->cachedQuery(
            'system.settings.all',
            fn() => $this->findAll(),
            ttl: 3600  // 1 heure
        );
    }
    
    /**
     * Récupère un paramètre par sa clé avec cache.
     */
    public function findByKeyCached(string $key): ?SystemSetting
    {
        return $this->cachedQuery(
            'system.setting.key.' . md5($key),
            fn() => $this->findOneBy(['key' => $key]),
            ttl: 3600  // 1 heure
        );
    }
    
    /**
     * Sauvegarde un paramètre et invalide le cache.
     */
    public function save(SystemSetting $setting, bool $flush = true): void
    {
        $this->getEntityManager()->persist($setting);
        
        if ($flush) {
            $this->getEntityManager()->flush();
            
            // Invalider les caches
            $this->invalidateCacheMultiple([
                'system.settings.all',
                'system.setting.key.' . md5($setting->getKey())
            ]);
        }
    }
}
