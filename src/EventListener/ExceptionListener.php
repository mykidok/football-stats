<?php

namespace App\EventListener;

use GuzzleHttp\Exception\ClientException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\Routing\RouterInterface;

class ExceptionListener
{
    private $router;
    private $session;

    public function __construct(RouterInterface $router, Session $session)
    {
        $this->router = $router;
        $this->session = $session;
    }


    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $exception = $event->getException();

        if (!$exception instanceof ClientException) {
            return;
        }

        if (Response::HTTP_TOO_MANY_REQUESTS !==  $exception->getCode()) {
            return;
        }

        $this->session->getFlashBag()->add('danger', 'Too many requests, please try again in a minute');

        $route = $this->router->generate('index');
        $response =  new RedirectResponse($route);

        $event->setResponse($response);
    }
}