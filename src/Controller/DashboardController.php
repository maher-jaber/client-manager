<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DashboardController extends AbstractController
{
    #[Route('/dashboard/ids', name: 'app_dashboard_ids')]
    public function idsDashboard(): Response
    {
        return $this->render('dashboard/ids.html.twig');
    }

    #[Route('/dashboard/altra', name: 'app_dashboard_altra')]
    public function altraDashboard(): Response
    {
        return $this->render('dashboard/altra.html.twig');
    }
}
