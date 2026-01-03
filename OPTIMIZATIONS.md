# Optimisations Backend Symfony - Hébergement Partagé

## 🎯 Résumé des Optimisations Implémentées

Cette documentation décrit les optimisations appliquées pour améliorer les performances de l'application Symfony sur un hébergement partagé (sans accès serveur, MySQL unique, pas de Redis).

**Gain attendu:** 2-5x amélioration des performances, support de 500-1,000 utilisateurs/jour.

---

## ✅ Optimisations Réalisées

### 1. **CacheableRepositoryTrait** - Système de Cache Réutilisable
📁 `src/Repository/Traits/CacheableRepositoryTrait.php`

**Fonctionnalités:**
- `cachedQuery()` - Wrapper cache automatique pour queries
- `invalidateCache()` - Invalidation après UPDATE/DELETE
- `generateCacheKey()` - Génération clés normalisées

**Utilisation:**
```php
use App\Repository\Traits\CacheableRepositoryTrait;

class UserRepository extends ServiceEntityRepository
{
    use CacheableRepositoryTrait;
    
    public function findByEmail(string $email): ?User
    {
        return $this->cachedQuery(
            'user.email.' . md5($email),
            fn() => $this->createQueryBuilder('u')
                ->andWhere('u.email = :email')
                ->setParameter('email', $email)
                ->getQuery()
                ->getOneOrNullResult(),
            ttl: 300  // 5 minutes
        );
    }
}
```

**Recommandations TTL:**
- 60-300s : Données changeant fréquemment (users connectés)
- 600-3600s : Données modérément stables (settings, configs)
- 3600-86400s : Données très stables (rôles, permissions)

---

### 2. **PaginatorTrait** - Pagination Universelle
📁 `src/Repository/Traits/PaginatorTrait.php`

**Fonctionnalités:**
- `paginate()` - Pagination automatique QueryBuilder
- `getPaginationMetadata()` - Métadonnées (total, pages, hasNext)
- `getPaginatedResult()` - Format API standard

**Utilisation:**
```php
use App\Repository\Traits\PaginatorTrait;

class UserRepository extends ServiceEntityRepository
{
    use PaginatorTrait;
    
    public function findActivePaginated(int $page = 1): Paginator
    {
        $qb = $this->createQueryBuilder('u')
            ->andWhere('u.enabled = true')
            ->orderBy('u.createdAt', 'DESC');
        
        return $this->paginate($qb, $page, limit: 20);
    }
}
```

**Limite recommandée:** 20-50 items par page (évite memory exhaustion).

---

### 3. **Doctrine Cache TTL Optimisé**
📁 `config/packages/doctrine.yaml`

**Modifications:**
- `write_rare`: 10 jours → **90 jours**
- `append_only`: 30 jours → **90 jours**

**Impact:** Réduit requêtes DB sur données système (rôles, permissions, settings).

---

### 4. **Repositories Optimisés**

#### UserRepository
📁 `src/Repository/Access/UserRepository.php`

**Ajouts:**
- ✅ Cache sur `findByEmail()` (5 min)
- ✅ `findActivePaginated()` pour listings
- ✅ `countTotal()` avec cache (30 min)
- ✅ Invalidation cache sur `save()`

#### SystemSettingRepository
📁 `src/Repository/System/SystemSettingRepository.php`

**Ajouts:**
- ✅ `findAllCached()` - Cache 1h
- ✅ `findByKeyCached()` - Cache 1h par clé
- ✅ `save()` avec invalidation automatique

---

### 5. **PerformanceSubscriber** - Monitoring Production
📁 `src/EventSubscriber/PerformanceSubscriber.php`

**Mesure automatiquement:**
- ⏱️ Durée requêtes (alerte si >1s)
- 💾 Memory peak (alerte si >100MB)
- 🗄️ Query count (alerte si >20 queries)

**Configuration:** Auto-enregistré via `EventSubscriberInterface`

**Logs produits:**
```
[warning] Performance issue: SLOW REQUEST (1.34s) | EXCESSIVE QUERIES (28)
{
  "method": "GET",
  "uri": "/api/users",
  "route": "api_users_list",
  "status": 200,
  "duration_ms": 1340.25,
  "memory_peak_mb": 87.5,
  "queries": 28
}
```

**Headers debug (en dev):**
- `X-Debug-Duration: 245.3ms`
- `X-Debug-Memory: 64.2MB`
- `X-Debug-Queries: 12`

---

### 6. **ProcessAsyncMessagesCommand** - Messenger pour Hébergement Partagé
📁 `src/Command/ProcessAsyncMessagesCommand.php`

**Contraintes hébergement partagé:**
- ❌ Pas de worker permanent (systemd)
- ✅ Commande limitée temps/mémoire/messages
- ✅ Callable par cron ou probabilistiquement

**Usage:**

**Option A - Cron Job (recommandé):**
```bash
# Toutes les 5 minutes
*/5 * * * * cd /home/user/public_html && php bin/console app:process-async-messages --quiet
```

**Option B - Déclenchement probabiliste:**
```php
// public/index.php - après $response->send()
if (mt_rand(1, 100) <= 10) {  // 10% des requêtes
    exec('php bin/console app:process-async-messages --limit=10 > /dev/null 2>&1 &');
}
```

**Paramètres:**
- `--limit=50` : Max 50 messages par exécution
- `--time-limit=240` : Max 4 minutes (safe pour cron 5 min)
- `--memory-limit=128M` : Limite mémoire
- `--transport=async` : Transport à consumer

---

### 7. **CacheService** - HTTP Cache Headers
📁 `src/Service/CacheService.php`

**Méthodes:**
- `cachePublic()` - Cache public (navigateur + proxy)
- `cachePrivate()` - Cache privé (navigateur uniquement)
- `cacheWithETag()` - Validation conditionnelle (304 Not Modified)
- `cacheWithLastModified()` - Validation par date
- `noCache()` - Désactive cache (données sensibles)
- `cacheImmutable()` - Assets statiques (1 an)

**Exemple DashboardController:**
```php
#[Route('/admin')]
public function index(CacheService $cache): Response
{
    $response = $this->render('admin/dashboard.html.twig');
    return $cache->cachePrivate($response, 300);  // 5 min
}
```

**Exemple API:**
```php
#[Route('/api/settings')]
public function settings(CacheService $cache): JsonResponse
{
    $data = $this->settingRepository->findAllCached();
    $response = new JsonResponse($data);
    
    return $cache->cacheWithETag($response, json_encode($data), 3600);
}
```

---

## 📋 Checklist Déploiement

### ✅ Configuration Requise

1. **Cache Symfony**
   - [ ] Vérifier `var/cache` writable
   - [ ] Clear cache prod: `php bin/console cache:clear --env=prod`
   - [ ] Warmup cache: `php bin/console cache:warmup --env=prod`

2. **Permissions**
   - [ ] `var/cache` → 755 ou 775
   - [ ] `var/log` → 755 ou 775
   - [ ] Owner: utilisateur web (ex: www-data)

3. **Messenger**
   - [ ] Configurer cron job ou déclenchement probabiliste
   - [ ] Tester: `php bin/console app:process-async-messages --limit=5`
   - [ ] Vérifier table `messenger_messages` existe

4. **Logging**
   - [ ] Canal `performance` configuré dans `monolog.yaml`
   - [ ] Vérifier logs écrits dans `var/log/performance.log`
   - [ ] Configurer rotation logs (logrotate ou équivalent)

5. **Assets**
   - [ ] Build production: `npm run build`
   - [ ] Vérifier versioning activé (Webpack Encore)
   - [ ] Assets accessibles dans `public/build/`

---

## 🎯 Utilisation dans Nouveaux Repositories

### Template Repository avec Cache & Pagination

```php
<?php

namespace App\Repository\YourDomain;

use App\Entity\YourDomain\YourEntity;
use App\Repository\Traits\CacheableRepositoryTrait;
use App\Repository\Traits\PaginatorTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<YourEntity>
 */
class YourEntityRepository extends ServiceEntityRepository
{
    use CacheableRepositoryTrait;
    use PaginatorTrait;
    
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, YourEntity::class);
    }
    
    /**
     * Trouve par critère avec cache.
     */
    public function findByCriteriaCached(string $criteria): ?YourEntity
    {
        return $this->cachedQuery(
            $this->generateCacheKey('entity.criteria', ['value' => $criteria]),
            fn() => $this->findOneBy(['criteria' => $criteria]),
            ttl: 600  // 10 minutes
        );
    }
    
    /**
     * Liste paginée avec tri.
     */
    public function findAllPaginated(int $page = 1, string $sort = 'createdAt'): Paginator
    {
        $qb = $this->createQueryBuilder('e')
            ->orderBy('e.' . $sort, 'DESC');
        
        return $this->paginate($qb, $page, limit: 20);
    }
    
    /**
     * Save avec invalidation cache.
     */
    public function save(YourEntity $entity, bool $flush = true): void
    {
        $this->getEntityManager()->persist($entity);
        
        if ($flush) {
            $this->getEntityManager()->flush();
            
            // Invalider caches pertinents
            $this->invalidateCacheMultiple([
                $this->generateCacheKey('entity.criteria', ['value' => $entity->getCriteria()]),
                'entity.list.all',
            ]);
        }
    }
}
```

---

## 📊 Métriques de Performance

### Avant Optimisations
- Utilisateurs simultanés: 50-100
- Temps réponse moyen: 200-500ms
- Queries par page: 15-30
- Memory par requête: 30-80MB

### Après Optimisations (Attendu)
- Utilisateurs simultanés: **500-1,000** ✅
- Temps réponse moyen: **50-150ms** ✅
- Queries par page: **5-15** ✅
- Memory par requête: **20-50MB** ✅
- Cache hit rate: **70-90%** ✅

---

## 🚀 Prochaines Étapes Recommandées

### Court Terme (1-2 semaines)
1. Appliquer traits aux autres repositories critiques
2. Ajouter HTTP cache sur APIs publiques
3. Monitorer logs performance pendant 1 semaine
4. Optimiser queries N+1 détectées

### Moyen Terme (1-2 mois)
5. Profiling détaillé (Blackfire.io gratuit)
6. Ajouter index database sur colonnes fréquentes
7. Optimiser eager loading relations
8. CDN gratuit pour assets (CloudFlare)

### Long Terme (Migration VPS)
9. Activer APCu (changer adapter dans cache.yaml)
10. Migrer Messenger vers Redis
11. Ajouter read replicas MySQL
12. Load balancer + multiple app servers

---

## 📖 Ressources

- [Symfony Performance](https://symfony.com/doc/current/performance.html)
- [Doctrine Caching](https://www.doctrine-project.org/projects/doctrine-orm/en/latest/reference/caching.html)
- [HTTP Caching](https://symfony.com/doc/current/http_cache.html)
- [Messenger Component](https://symfony.com/doc/current/messenger.html)

---

**Date:** 2026-01-03  
**Version:** 1.0  
**Auteur:** Optimisations Backend Symfony
