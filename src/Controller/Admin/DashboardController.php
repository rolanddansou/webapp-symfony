<?php

namespace App\Controller\Admin;

use App\Entity\Access\Identity;
use App\Entity\Access\UserCredentials;
use App\Entity\System\AppBanner;
use App\Entity\System\ContactMessage;
use App\Entity\System\Faq;
use App\Entity\System\FaqCategory;
use App\Entity\System\StaticContent;
use App\Feature\Shared\Domain\IRoleManager;
use App\Service\CacheService;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_BACKEND')]
class DashboardController extends AbstractDashboardController
{
    public function __construct(
        private readonly IRoleManager $roleManager,
        private readonly CacheService $cacheService,
        #[Autowire('%app_name%')]
        private string $appName
    ) {
    }

    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
        // Check if user has admin or merchant role
        if (!$this->isGranted('ROLE_BACKEND')) {
            throw $this->createAccessDeniedException('Access denied');
        }

        // Render custom dashboard with Vue.js analytics
        $response = $this->render('admin/dashboard.html.twig');

        // Cache 5 minutes (dashboard relativement stable pour utilisateur connecté)
        return $this->cacheService->cachePrivate($response, 300);
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle($this->appName.' Admin Dashboard')
            ->disableDarkMode()
            ->renderContentMaximized();
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-chart-line');

        // Merchants section - visible to all
        yield MenuItem::section('Merchants');

        // Access Management - admin only
        if ($this->roleManager->isAdmin()) {
            yield MenuItem::section('Access Management');
            yield MenuItem::linkToCrud('Identities', 'fa fa-user', Identity::class);
            yield MenuItem::linkToCrud('Credentials', 'fa fa-key', UserCredentials::class);

            yield MenuItem::section('System');
            yield MenuItem::linkToCrud('Banners', 'fa fa-image', AppBanner::class);
            yield MenuItem::linkToCrud('Contenu Statique', 'fa fa-file-alt', StaticContent::class);

            yield MenuItem::section('Support');
            yield MenuItem::linkToCrud('Messages Contact', 'fa fa-envelope', ContactMessage::class);
            yield MenuItem::linkToCrud('FAQ Catégories', 'fa fa-tags', FaqCategory::class);
            yield MenuItem::linkToCrud('FAQ Questions', 'fa fa-question-circle', Faq::class);
        }
    }

    public function configureAssets(): Assets
    {
        return parent::configureAssets()
            ->addWebpackEncoreEntry('admin');
    }
}
