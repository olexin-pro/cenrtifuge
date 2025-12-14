<?php

declare(strict_types=1);

namespace OlexinPro\Centrifuge\Handlers;

use OlexinPro\Centrifuge\Contracts\Authenticator;
use OlexinPro\Centrifuge\Contracts\RequestHandler;
use Psr\Log\LoggerInterface;
use RoadRunner\Centrifugo\Payload\ConnectResponse;
use RoadRunner\Centrifugo\Request\Connect;
use RoadRunner\Centrifugo\Request\RequestInterface;

final readonly class ConnectHandler implements RequestHandler
{
    public function __construct(
        private Authenticator   $authenticator,
        private LoggerInterface $logger
    ) {}

    public function handle(RequestInterface $request): void
    {
        \assert($request instanceof Connect);

        $this->logger->debug('Client connecting', [
            'client' => $request->client,
            'transport' => $request->transport,
        ]);

        $user = $this->authenticator->authenticate($request);

        $request->respond(new ConnectResponse(
            user: $user ? (string) $user->getAuthIdentifier() : '',
            data: $user ? $this->getUserData($user) : []
        ));
    }

    private function getUserData($user): array
    {
        return [
            'name' => $user->name ?? 'User',
        ];
    }
}
