<?php

namespace App\EventListener;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\Validator\Exception\ValidationFailedException;

class ExceptionListener
{
    public function onKernelException(ExceptionEvent $event): void
    {
        $isApi = $event->getRequest()->attributes->get('show_exception_as_json', false);
        if (!$isApi) {
            return;
        }

        $throwable = $event->getThrowable();

        $errors = [];

        $previousException = $throwable->getPrevious();
        $isValidationException = false;
        if ($throwable instanceof ValidationFailedException) {
            $isValidationException = true;
        } elseif ($previousException instanceof ValidationFailedException) {
            $throwable = $previousException;
            $isValidationException = true;
        }

        if ($isValidationException) {
            $errors['fatal'] = [
                'code' => Response::HTTP_UNPROCESSABLE_ENTITY,
                'message' => Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
            ];

            foreach ($throwable->getViolations() as $error) {
                $errors['validation'][] = [
                    'property' => $error->getPropertyPath(),
                    'message' => $error->getMessage(),
                ];
            }
        } else {
            $errors['fatal'] = [
                'code' => $throwable->getCode(),
                'message' => $throwable->getMessage(),
            ];
        }

        if ($isValidationException) {
            $responseCode = Response::HTTP_UNPROCESSABLE_ENTITY;
        } elseif (
            method_exists($throwable, 'getStatusCode')
            && $throwable->getStatusCode() >= 100
            && $throwable->getStatusCode() <= 599
        ) {
            $responseCode = $throwable->getStatusCode();
        } else {
            $responseCode = Response::HTTP_INTERNAL_SERVER_ERROR;
        }

        $event->setResponse(new JsonResponse(
            data: ['errors' => $errors],
            status: $responseCode
        ));
    }
}
