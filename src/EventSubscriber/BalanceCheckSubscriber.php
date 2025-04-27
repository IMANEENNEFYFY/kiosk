<?php

namespace App\EventSubscriber;

use App\Repository\CartePrepayeeRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class BalanceCheckSubscriber implements EventSubscriberInterface
{
    private $carteRepo;
    private $urlGenerator;
    private $protectedRoutes = [
        'app_categorie',
        'app_panier',
        'valider_commande',
        'app_confirmation_carte'
    ];

    public function __construct(CartePrepayeeRepository $carteRepo, UrlGeneratorInterface $urlGenerator)
    {
        $this->carteRepo = $carteRepo;
        $this->urlGenerator = $urlGenerator;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => 'onKernelRequest',
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        $route = $request->attributes->get('_route');

        if (!in_array($route, $this->protectedRoutes)) {
            return;
        }

        $session = $request->getSession();
        $carteId = $session->get('carte_prepayee_id');

        // Si aucune carte ou solde à 0, vider la session et rediriger vers scan
        if (!$carteId) {
            $session->clear();
            $event->setResponse(new RedirectResponse($this->urlGenerator->generate('app_scan_carte')));
            return;
        }

        $carte = $this->carteRepo->find($carteId);
        if (!$carte || $carte->getSolde() <= 0) {
            $session->clear();
            $event->setResponse(new RedirectResponse($this->urlGenerator->generate('app_scan_carte')));
            return;
        }
    }
} 