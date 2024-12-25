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
        if ($previousException instanceof ValidationFailedException) {
            $errors['fatal'] = [
                'code' => Response::HTTP_UNPROCESSABLE_ENTITY,
                'message' => Response::$statusTexts[Response::HTTP_UNPROCESSABLE_ENTITY],
            ];

            foreach ($previousException->getViolations() as $error) {
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

        $response = new JsonResponse();
        $response->setData([
            'errors' => $errors,
        ]);
        $response->setStatusCode(
            method_exists($throwable, 'getStatusCode')
                ? $throwable->getStatusCode()
                : Response::HTTP_INTERNAL_SERVER_ERROR
        );

        $event->setResponse($response);
    }
}
