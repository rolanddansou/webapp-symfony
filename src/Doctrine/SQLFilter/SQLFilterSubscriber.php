<?php

namespace App\Doctrine\SQLFilter;

use App\Feature\Shared\Domain\IRoleManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

readonly class SQLFilterSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private EntityManagerInterface $manager,
        private IRoleManager           $roleManager
    ){}

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', -50],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $filters = $this->manager->getFilters();

        if(!$this->roleManager->isConnected() || $this->roleManager->isFrontEndUser()){
            $filters->enable('enabled_disabled_filter')->setParameter('enabled', 1);
        }
    }
}
