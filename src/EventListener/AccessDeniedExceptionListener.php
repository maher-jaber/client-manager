<?php

namespace App\EventListener;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Environment;

class AccessDeniedExceptionListener
{
    public function __construct(
        private Environment $twig,
        private RequestStack $requestStack
    ) {}

    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        if ($exception->getMessage() == 'Access Denied.') {


            $request = $this->requestStack->getCurrentRequest();

            $content = $this->twig->render('security/access_denied.html.twig', [
                'message' => 'Vous n’avez pas la permission pour accéder à cette page.',
                'route' => $request?->attributes->get('_route'),
            ]);

            $response = new Response($content, Response::HTTP_FORBIDDEN);
            $event->setResponse($response);
        }
    }
}
