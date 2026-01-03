<?php

namespace App\Controller;

use Rompetomp\InertiaBundle\Architecture\InertiaInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'home', options: ['expose' => true])]
    public function index(InertiaInterface $inertia): Response
    {
        return $inertia->render('Home');
    }
}
