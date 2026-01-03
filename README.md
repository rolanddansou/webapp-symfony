# 🚀 Symfony Backend Template - Production Ready

[![PHP Version](https://img.shields.io/badge/PHP-8.2+-blue.svg)](https://www.php.net/)
[![Symfony Version](https://img.shields.io/badge/Symfony-7.4-black.svg)](https://symfony.com/)
[![License](https://img.shields.io/badge/license-proprietary-red.svg)]()

Template backend Symfony professionnel, optimisé pour **hébergement partagé**, scalable et prêt pour la production. Architecture moderne avec PHP 8.2+, Domain-Driven Design, et optimisations de performance testées en production.

---

## 🎯 Vue d'Ensemble

Ce template fournit une base solide pour développer des applications backend Symfony avec :

- ✅ **Architecture moderne** : Domain-Driven Design, Feature-based organization
- ✅ **Optimisé hébergement partagé** : Cache filesystem agressif, pagination, queries optimisées
- ✅ **Sécurité robuste** : JWT + Session dual auth, rate limiting, RBAC
- ✅ **Performance** : Cache à plusieurs niveaux, lazy loading, async processing
- ✅ **Monitoring** : Logs structurés, performance tracking, alertes
- ✅ **Developer Experience** : Makefile 30+ commandes, documentation complète

### Capacités

| Métrique | Hébergement Partagé | VPS/Dédié |
|----------|---------------------|-----------|
| **Utilisateurs simultanés** | 500-1,000 | 5,000-10,000+ |
| **Requêtes/seconde** | 80-150 | 500-2,000+ |
| **Temps réponse (P95)** | 50-150ms | 10-50ms |
| **Infrastructure** | MySQL unique | + Redis, Read Replicas |

---

## 📋 Table des Matières

- [Technologies](#-technologies)
- [Démarrage Rapide](#-démarrage-rapide)
- [Architecture](#-architecture)
- [Features](#-features)
- [Optimisations Performance](#-optimisations-performance)
- [Configuration](#️-configuration)
- [Déploiement](#-déploiement)
- [Documentation](#-documentation)
- [Contribuer](#-contribuer)

---

## 🛠️ Technologies

### Backend
- **PHP 8.2+** : Readonly classes, constructor property promotion, typed properties
- **Symfony 7.4** : Latest stable avec MicroKernelTrait
- **Doctrine ORM 3.6** : Second-level cache, lazy ghost objects, advanced features
- **MySQL 8.0** : InnoDB optimisé (compatible MariaDB 10.11+)

### Frontend
- **Inertia.js** : SPA-like experience sans API REST
- **Vue 3** : Composition API, TypeScript support
- **Webpack Encore** : Asset compilation optimisée
- **Tailwind CSS** : Utility-first styling
- **Pinia** : State management

### Infrastructure
- **Symfony Messenger** : Async processing (emails, notifications)
- **Monolog** : Multi-channel structured logging
- **JWT Authentication** : Stateless API authentication
- **EasyAdmin** : Admin panel auto-généré
- **Flysystem** : Storage abstraction (local, S3, etc.)

### Qualité
- **PHPStan Level 7** : Static analysis strict
- **PHP CS Fixer** : PSR-12 compliance
- **PHPUnit** : Unit & integration tests
- **Doctrine Migrations** : Database versioning

---

## 🚀 Démarrage Rapide

### Prérequis

```bash
# Requis
PHP >= 8.2
Composer 2.x
MySQL 8.0 / MariaDB 10.11+
Node.js >= 18.x
npm >= 9.x

# Extensions PHP requises
ext-ctype, ext-iconv, ext-intl, ext-pdo_mysql
ext-mbstring, ext-xml, ext-json
```

### Installation (5 minutes)

```bash
# 1. Cloner le template
git clone <votre-repo> mon-projet
cd mon-projet

# 2. Installer dépendances
composer install
npm install

# 3. Configuration environnement
cp .env.example .env
# Éditer .env : DATABASE_URL, APP_SECRET, JWT keys

# 4. Générer clés JWT
php bin/console lexik:jwt:generate-keypair

# 5. Créer base de données
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate -n

# 6. Charger fixtures (optionnel)
php bin/console doctrine:fixtures:load -n

# 7. Build assets frontend
npm run dev

# 8. Démarrer serveur
symfony server:start
# ou
php -S localhost:8000 -t public/
```

**Application accessible sur http://localhost:8000** 🎉

### Makefile (Recommandé)

```bash
# Installation complète en une commande
make install

# Autres commandes utiles
make start              # Démarrer serveur dev
make test              # Lancer tests
make fix               # Fixer code style
make analyse           # PHPStan analyse
make cache-clear       # Clear cache
make db-reset          # Reset database avec fixtures
```

Voir tous les commandes : `make help`

---

## 🏗️ Architecture

### Organisation Feature-Based

```
src/
├── Feature/
│   ├── Access/          # Authentification, autorisation, users
│   │   ├── Domain/      # Interfaces, DTOs, Events
│   │   ├── Service/     # Business logic
│   │   ├── Repository/  # Data access
│   │   └── EventSubscriber/
│   ├── Activity/        # User activity tracking
│   ├── Media/           # File upload, storage
│   ├── Notification/    # Emails, SMS, push notifications
│   └── Shared/          # Code partagé entre features
├── Controller/          # HTTP endpoints
├── Entity/              # Doctrine entities
├── Command/             # CLI commands
├── EventSubscriber/     # Global event listeners
└── Twig/               # Twig extensions
```

### Patterns Utilisés

- **Domain-Driven Design** : Séparation claire domaine/infrastructure
- **Repository Pattern** : Abstraction accès données
- **Service Layer** : Business logic isolée
- **Event-Driven** : Communication découplée via events
- **DTO Pattern** : Transfer objects pour APIs
- **Dependency Injection** : Autowiring Symfony

📖 **Documentation complète** : [docs/ARCHITECTURE.md](docs/ARCHITECTURE.md)

---

## ✨ Features

### 1. Access Management (Authentification & Autorisation)

- **Dual Authentication**
  - JWT pour APIs (stateless, 30 jours)
  - Session pour admin panel (30 jours remember-me)
- **Multi-rôles** : ADMIN, USER, BACKEND avec hiérarchie
- **User Management** : CRUD complet avec profiles
- **Email Verification** : Confirmation obligatoire
- **Password Reset** : Tokens sécurisés avec expiration
- **Login Attempts** : Tracking et throttling

### 2. Activity Tracking

- **User Activity Logging** : Actions, timestamps, IP, user-agent
- **Audit Trail** : Historique complet modifications
- **Analytics Ready** : Export vers outils analytics

### 3. Media Management

- **File Upload** : Multiple files, validation type/taille
- **Storage Abstraction** : Flysystem (local, S3, Azure, GCS)
- **File Versioning** : Historique des versions
- **Thumbnails** : Génération automatique images

### 4. Notification System

- **Multi-channel** : Email, SMS, Push notifications
- **Templates** : Gestion templates avec variables
- **Async Processing** : Queue Messenger (non-bloquant)
- **Delivery Tracking** : Status et retry automatique
- **Provider Abstraction** : Facile changer fournisseur SMS/Email

📖 **Documentation détaillée** : [docs/FEATURES.md](docs/FEATURES.md)

---

## ⚡ Optimisations Performance

### Système de Cache Multi-Niveaux

#### 1. Doctrine Second-Level Cache
```yaml
# 90 jours TTL pour données statiques
write_rare:
  lifetime: 7776000  # Rôles, permissions, settings
append_only:
  lifetime: 7776000  # Logs, historique
```

#### 2. Cache Repository Applicatif
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

#### 3. HTTP Cache Headers
```php
use App\Service\CacheService;

#[Route('/api/settings')]
public function settings(CacheService $cache): JsonResponse
{
    $response = new JsonResponse($data);
    return $cache->cachePublic($response, 3600);  // 1h cache
}
```

### Pagination Universelle

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

### Monitoring Performance

```php
// PerformanceSubscriber auto-activé
// Logs automatiques si :
// - Requête > 1 seconde
// - Memory > 100MB
// - Queries > 20

[warning] Performance issue: SLOW REQUEST (1.34s) | EXCESSIVE QUERIES (28)
{
  "uri": "/api/users",
  "duration_ms": 1340.25,
  "memory_peak_mb": 87.5,
  "queries": 28
}
```

### Async Processing (Hébergement Partagé)

```bash
# Cron job - traite messages async
*/5 * * * * cd /path/to/project && php bin/console app:process-async-messages --quiet

# Ou déclenchement probabiliste (10% requêtes)
# Configuré dans public/index.php
```

📖 **Guide complet** : [OPTIMIZATIONS.md](OPTIMIZATIONS.md)

---

## ⚙️ Configuration

### Variables Environnement Principales

```bash
# Base
APP_ENV=prod
APP_SECRET=<générer-avec-symfony-console-secret>
APP_DEBUG=0

# Database
DATABASE_URL="mysql://user:pass@localhost:3306/dbname?serverVersion=8.0"

# JWT (générer avec lexik:jwt:generate-keypair)
JWT_SECRET_KEY=%kernel.project_dir%/config/jwt/private.pem
JWT_PUBLIC_KEY=%kernel.project_dir%/config/jwt/public.pem
JWT_PASSPHRASE=<votre-passphrase>

# Messenger (async processing)
MESSENGER_TRANSPORT_DSN=doctrine://default

# Mailer
MAILER_DSN=smtp://user:pass@smtp.example.com:587

# Storage (local par défaut, S3 pour production)
STORAGE_ADAPTER=local
# AWS_S3_BUCKET=your-bucket
# AWS_S3_REGION=eu-west-1
```

### Fichiers Environnement

- `.env.example` : Template avec documentation
- `.env` : Local development (git-ignored)
- `.env.prod` : Production (à configurer sur serveur)
- `.env.test` : Tests automatisés

---

## 🚀 Déploiement

### Hébergement Partagé (cPanel, Plesk)

```bash
# 1. Upload fichiers (FTP/SSH)
# 2. Pointer DocumentRoot vers public/

# 3. Configuration
cp .env.example .env
# Éditer .env avec credentials production

# 4. Install dependencies (si composer disponible)
composer install --no-dev --optimize-autoloader

# 5. Database
php bin/console doctrine:migrations:migrate -n

# 6. Assets
npm run build

# 7. Cache
php bin/console cache:clear --env=prod
php bin/console cache:warmup --env=prod

# 8. Permissions
chmod -R 755 var/cache var/log
chown -R www-data:www-data var/

# 9. Cron job (panel admin)
*/5 * * * * cd /home/user/public_html && php bin/console app:process-async-messages --quiet
```

### VPS/Serveur Dédié (Linux)

```bash
# Installation automatisée
make deploy-prod

# Ou manuel
./scripts/deploy.sh production

# Configure Nginx + PHP-FPM
# Configure systemd pour Messenger workers
# Configure Let's Encrypt SSL
```

📖 **Guide détaillé** : [docs/DEPLOYMENT.md](docs/DEPLOYMENT.md)

---

## 📚 Documentation

### Guides Principaux

| Document | Description |
|----------|-------------|
| [QUICKSTART.md](QUICKSTART.md) | Démarrer en 5 minutes |
| [OPTIMIZATIONS.md](OPTIMIZATIONS.md) | Optimisations performance |
| [docs/ARCHITECTURE.md](docs/ARCHITECTURE.md) | Architecture & patterns |
| [docs/FEATURES.md](docs/FEATURES.md) | Features détaillées |
| [docs/SECURITY.md](docs/SECURITY.md) | Sécurité & authentification |
| [docs/DEPLOYMENT.md](docs/DEPLOYMENT.md) | Déploiement production |
| [docs/DATABASE.md](docs/DATABASE.md) | Base de données & migrations |
| [docs/FRONTEND.md](docs/FRONTEND.md) | Inertia.js + Vue 3 |

### Référence Rapide

```bash
# Commandes Make les plus utiles
make help              # Liste toutes les commandes
make install           # Installation complète
make start             # Démarrer serveur dev
make test              # Lancer tests
make analyse           # PHPStan analyse
make fix               # Fix code style
make db-reset          # Reset DB avec fixtures
make cache-clear       # Clear all caches

# Commandes Symfony fréquentes
php bin/console debug:container     # Liste services
php bin/console debug:router        # Liste routes
php bin/console make:entity         # Créer entité
php bin/console make:migration      # Créer migration
php bin/console messenger:consume   # Worker async
```

---

## 🧪 Tests

```bash
# Tous les tests
make test
# ou
php bin/phpunit

# Tests spécifiques
php bin/phpunit tests/Unit/
php bin/phpunit tests/Feature/

# Avec couverture
php bin/phpunit --coverage-html var/coverage

# Tests performance
php bin/console app:benchmark
```

---

## 📊 Monitoring & Logs

### Canaux de Logs

```
var/log/
├── prod.log            # Application générale
├── security.log        # Authentification, authorization
├── business.log        # Business logic errors
├── performance.log     # Slow requests, high memory
└── notification.log    # Emails, SMS delivery
```

### Métriques Automatiques

Le `PerformanceSubscriber` logue automatiquement :
- ⏱️ Temps d'exécution requêtes
- 💾 Memory peak usage
- 🗄️ Nombre de queries SQL
- 🚨 Alertes si seuils dépassés

En mode debug, headers ajoutés :
```
X-Debug-Duration: 245.3ms
X-Debug-Memory: 64.2MB
X-Debug-Queries: 12
```

---

## 🔒 Sécurité

### Fonctionnalités

- ✅ **JWT Authentication** : Tokens stateless avec refresh
- ✅ **Rate Limiting** : Login (10/min), Register (5/h), API (100/min)
- ✅ **CSRF Protection** : Formulaires protégés
- ✅ **Password Hashing** : Bcrypt/Argon2
- ✅ **Login Attempts** : Tracking et blocage temporaire
- ✅ **Email Verification** : Confirmation obligatoire
- ✅ **Role Hierarchy** : ADMIN > BACKEND > USER

### Checklist Production

```bash
# 1. Changer APP_SECRET
# 2. Générer nouvelles clés JWT
# 3. Configurer HTTPS (Let's Encrypt)
# 4. Activer rate limiting
# 5. Configurer firewall serveur
# 6. Backup automatique database
# 7. Monitoring logs security
```

📖 **Guide complet** : [docs/SECURITY.md](docs/SECURITY.md)

---

## 🤝 Contribuer

### Standards

- **PHP** : PSR-12, PHPStan Level 7
- **Commits** : Conventional Commits
- **Branches** : Git Flow (main, develop, feature/*, hotfix/*)
- **Tests** : Couverture minimale 80%

### Workflow

```bash
# 1. Fork & clone
git clone <your-fork>

# 2. Créer branche
git checkout -b feature/nouvelle-fonctionnalite

# 3. Développer avec qualité
make fix      # Fix code style
make analyse  # Vérifier PHPStan
make test     # Lancer tests

# 4. Commit & push
git commit -m "feat: ajouter fonctionnalité X"
git push origin feature/nouvelle-fonctionnalite

# 5. Créer Pull Request
```

---

## 📝 License

Proprietary. Voir [LICENSE](LICENSE) pour détails.

---

## 🎯 Roadmap

### v2.0 (Q1 2026)
- [ ] Support PostgreSQL natif
- [ ] GraphQL API endpoint
- [ ] WebSocket real-time
- [ ] Redis cache adapter
- [ ] Docker Compose production-ready

### v2.1 (Q2 2026)
- [ ] Elasticsearch integration
- [ ] Advanced analytics dashboard
- [ ] Multi-tenancy support
- [ ] API rate limiting par user

---

## 💡 Support

### Resources

- **Documentation** : [docs/](docs/)
- **Issues** : [GitHub Issues](https://github.com/yourrepo/issues)
- **Discussions** : [GitHub Discussions](https://github.com/yourrepo/discussions)

### Contact

- **Email** : support@example.com
- **Website** : https://example.com

---

## 🙏 Remerciements

Construit avec :
- [Symfony](https://symfony.com/)
- [Doctrine](https://www.doctrine-project.org/)
- [Vue.js](https://vuejs.org/)
- [Inertia.js](https://inertiajs.com/)
- [Tailwind CSS](https://tailwindcss.com/)

---

**Fait avec ❤️ pour la communauté Symfony**

⭐ Si ce template vous aide, pensez à mettre une étoile !
