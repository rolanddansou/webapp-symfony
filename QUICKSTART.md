# QUICKSTART - Démarrer un nouveau projet en 5 minutes

Guide ultra-rapide pour démarrer votre premier projet avec ce template.

## 🚀 Installation rapide

### 1. Cloner ou créer depuis le template

```bash
# Option A : Cloner
git clone <repository-url> my-app
cd my-app

# Option B : GitHub "Use this template"
# Cliquer sur le bouton vert "Use this template"
```

### 2. Installation dépendances

```bash
# Tout en une commande
make install

# Ou manuellement
composer install
npm install
php bin/console lexik:jwt:generate-keypair
```

### 3. Configuration d'environnement

```bash
# Copier le fichier d'exemple
cp .env.example .env

# Éditer .env avec vos paramètres
nano .env

# Au minimum, modifier:
# DATABASE_URL=mysql://root:password@127.0.0.1:3306/my_database
```

### 4. Base de données

```bash
# Tout en une commande
make db-setup

# Ou étape par étape
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
php bin/console doctrine:fixtures:load
```

### 5. Démarrer le serveur

```bash
# Terminal 1 : Serveur Symfony
symfony server:start -d

# Terminal 2 : Assets en watch
npm run watch

# Accéder à http://localhost:8000
```

✅ **Voilà !** Vous avez un backend Symfony complet et prêt à développer.

---

## 📂 Créer votre première Feature

Créer une nouvelle Feature `Post` (blog) :

```bash
# Générer la structure automatiquement (optionnel)
php bin/create-feature.php Post

# Ou créer manuellement
mkdir -p src/Feature/Post/{DTO,Service,Event,Exception}
```

### 1. Créer l'entité

```php
// src/Entity/Post.php
<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Entity\Trait\IdTrait;
use App\Entity\Trait\TimestampTrait;

#[ORM\Entity]
#[ORM\Table(name: 'post')]
class Post
{
    use IdTrait;
    use TimestampTrait;

    #[ORM\Column(length: 255)]
    private string $title;

    #[ORM\Column(type: 'text')]
    private string $content;

    #[ORM\ManyToOne(targetEntity: Identity::class)]
    #[ORM\JoinColumn(nullable: false)]
    private Identity $author;

    // Getters & setters...
}
```

### 2. Créer les DTOs

```php
// src/Feature/Post/DTO/CreatePostRequest.php
<?php

namespace App\Feature\Post\DTO;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class CreatePostRequest
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Length(min: 5)]
        public string $title,

        #[Assert\NotBlank]
        #[Assert\Length(min: 10)]
        public string $content,
    ) {}
}

// src/Feature/Post/DTO/PostResponse.php
final readonly class PostResponse
{
    public function __construct(
        public string $id,
        public string $title,
        public string $content,
        public string $authorName,
        public string $createdAt,
    ) {}
}
```

### 3. Créer le Service

```php
// src/Feature/Post/Service/PostService.php
<?php

namespace App\Feature\Post\Service;

use App\Entity\Post;
use App\Entity\Access\Identity;
use App\Feature\Post\DTO\CreatePostRequest;
use App\Feature\Post\DTO\PostResponse;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;

final readonly class PostService implements PostServiceInterface
{
    public function __construct(
        private PostRepository $repository,
        private EntityManagerInterface $em,
        private EventDispatcherInterface $dispatcher,
    ) {}

    public function create(CreatePostRequest $request, Identity $author): PostResponse
    {
        $post = new Post();
        $post->setTitle($request->title);
        $post->setContent($request->content);
        $post->setAuthor($author);

        $this->em->persist($post);
        $this->em->flush();

        // Dispatcher un événement
        $this->dispatcher->dispatch(new PostCreatedEvent($post));

        return $this->toResponse($post);
    }

    public function getAll(): array
    {
        return array_map(
            $this->toResponse(...),
            $this->repository->findAll()
        );
    }

    private function toResponse(Post $post): PostResponse
    {
        return new PostResponse(
            id: (string) $post->getId(),
            title: $post->getTitle(),
            content: $post->getContent(),
            authorName: $post->getAuthor()->getEmail(),
            createdAt: $post->getCreatedAt()->format('Y-m-d H:i:s'),
        );
    }
}
```

### 4. Créer un Contrôleur

```php
// src/Controller/PostController.php
<?php

namespace App\Controller;

use App\Feature\Post\DTO\CreatePostRequest;
use App\Feature\Post\Service\PostServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/posts', format: 'json')]
#[IsGranted('ROLE_USER')]
class PostController extends AbstractController
{
    public function __construct(
        private PostServiceInterface $postService,
    ) {}

    #[Route(methods: ['GET'])]
    public function list(): JsonResponse
    {
        return $this->json($this->postService->getAll());
    }

    #[Route(methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $postRequest = new CreatePostRequest(
            title: $data['title'],
            content: $data['content'],
        );

        $post = $this->postService->create($postRequest, $this->getUser());

        return $this->json($post, 201);
    }
}
```

### 5. Créer une migration

```bash
# Générer automatiquement la migration
php bin/console make:migration

# Vérifier la migration générée
cat migrations/VersionXXXXXXXXXXXXXX_*.php

# Exécuter la migration
php bin/console doctrine:migrations:migrate
```

### 6. Créer des tests

```php
// tests/Feature/Post/PostServiceTest.php
<?php

namespace App\Tests\Feature\Post;

use App\Entity\Access\Identity;
use App\Feature\Post\DTO\CreatePostRequest;
use App\Feature\Post\Service\PostServiceInterface;
use App\Tests\Factory\IdentityFactory;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class PostServiceTest extends KernelTestCase
{
    private PostServiceInterface $postService;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->postService = self::getContainer()
            ->get(PostServiceInterface::class);
    }

    public function testCreatePost(): void
    {
        $author = IdentityFactory::new()->create();

        $response = $this->postService->create(
            new CreatePostRequest(
                title: 'Test Post',
                content: 'This is a test post with enough content',
            ),
            $author
        );

        $this->assertEquals('Test Post', $response->title);
        $this->assertNotEmpty($response->id);
    }
}
```

### 7. Lancer les tests

```bash
# Tous les tests
make test

# Ou uniquement les tests Post
./bin/phpunit tests/Feature/Post/

# Avec couverture
make test-coverage
```

---

## 🔧 Commandes pratiques

```bash
# Développement
make dev-server              # Démarrer le serveur
npm run watch              # Watch des assets
make logs-tail              # Voir les logs

# Base de données
make db-migrate             # Exécuter les migrations
make db-reset               # Reset complet
make db-fixtures            # Charger les fixtures

# Tests
make test                   # Lancer les tests
make test-coverage          # Avec rapport de couverture
make analyze                # Analyse PHPStan
make cs-fix                 # Corriger le style

# Déploiement
make deploy-check           # Vérifier avant déploiement
./deploy/deploy.sh prod     # Déployer en production
```

---

## 📚 Ressources

- **[README.md](README.md)** - Vue d'ensemble complète
- **[CONTRIBUTING.md](CONTRIBUTING.md)** - Standards de développement
- **[docs/FEATURES.md](docs/FEATURES.md)** - Détail des 4 Features
- **[docs/SECURITY.md](docs/SECURITY.md)** - Auth JWT & Session
- **[docs/ARCHITECTURE.md](docs/ARCHITECTURE.md)** - Patterns et conventions
- **[docs/DEPLOYMENT.md](docs/DEPLOYMENT.md)** - Déploiement serveurs
- **[docs/MONITORING.md](docs/MONITORING.md)** - Logs et alertes

---

## 🎯 Prochaines étapes

1. **Créer vos Features** : `php bin/create-feature.php MyFeature`
2. **Définir vos entités** : Ajouter les DTOs, Services, Controllers
3. **Écrire les tests** : `tests/Feature/MyFeature/`
4. **Développer le frontend** : Vue 3 + Inertia.js
5. **Déployer** : `./deploy/deploy.sh production`

---

## ❓ Questions?

Consulter la [documentation complète](docs/) ou ouvrir une issue sur GitHub.

**Bon développement! 🚀**

