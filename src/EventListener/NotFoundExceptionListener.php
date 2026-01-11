<?php

namespace App\EventListener;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class NotFoundExceptionListener
{
    private $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    public function onKernelException(ExceptionEvent $event)
    {
        // Взимаме обекта на грешката
        $exception = $event->getThrowable();

        // Проверяваме дали грешката е точно 404 (невалиден URL)
        if ($exception instanceof NotFoundHttpException) {
            // Генерираме URL към началната страница (app_home е името на твоя route)
            $response = new RedirectResponse($this->urlGenerator->generate('app_home'));

            // Изпращаме редиректа към браузъра
            $event->setResponse($response);
        }
    }
}