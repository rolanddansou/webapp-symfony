<?php

namespace App\EventSubscriber;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;

final class AddJsonBodyToRequestListener
{
    public function onKernelRequest(RequestEvent $event): void
    {
        $request         = $event->getRequest();
        $requestContents = $request->getContent();

        if (!empty($requestContents) && $this->containsHeader($request, 'Content-Type', ['application/json', 'application/ld+json'])) {
            $jsonData = json_decode($requestContents, true, 512, JSON_THROW_ON_ERROR);
            if (!$jsonData) {
                throw new HttpException(Response::HTTP_BAD_REQUEST, 'Invalid json data');
            }
            $jsonDataLowerCase = [];
            foreach ($jsonData as $key => $value) {
                $jsonDataLowerCase[preg_replace_callback(
                    '/_(.)/',
                    static fn ($matches) => strtoupper($matches[1]),
                    $key
                )] = $value;
            }
            $request->request->replace($jsonDataLowerCase);
        }
    }

    private function containsHeader(Request $request, string $name, array $value): bool
    {
        $contain= false;

        foreach ($value as $item) {
            if (str_contains($request->headers->get($name, ''), $item)) {
                $contain = true;
                break;
            }
        }

        return $contain;
    }
}
