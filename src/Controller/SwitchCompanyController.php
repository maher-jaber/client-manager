<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;


class SwitchCompanyController extends AbstractController
{
    #[Route('/', name: 'switch_company')]
    public function chooseCompany(): Response
    {
        return $this->render('switch_company/index.html.twig');
    }

    #[Route('/select', name: 'switch_company_select', methods: ['POST'])]
    public function selectCompany(Request $request): RedirectResponse
    {
        $company = $request->request->get('company');

        if ($company === 'ids') {
            return $this->redirectToRoute('app_client_index');
        } elseif ($company === 'altra') {
            return $this->redirectToRoute('app_dashboard_altra');
        }

        return $this->redirectToRoute('switch_company');
    }
}
