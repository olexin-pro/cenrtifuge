<?php

declare(strict_types=1);

namespace OlexinPro\Centrifuge\Handlers;

use OlexinPro\Centrifuge\Contracts\Authenticator;
use OlexinPro\Centrifuge\Contracts\RequestHandler;
use Psr\Log\LoggerInterface;
use RoadRunner\Centrifugo\Payload\RefreshResponse;
use RoadRunner\Centrifugo\Request\Refresh;
use RoadRunner\Centrifugo\Request\RequestInterface;

final readonly class RefreshHandler implements RequestHandler
{
    public function __construct(
        private Authenticator   $authenticator,
        private LoggerInterface $logger
    ) {}

    public function handle(RequestInterface $request): void
    {
        \assert($request instanceof Refresh);

        $this->logger->debug('Connection refresh', [
            'client' => $request->client,
        ]);

        $user = $this->authenticator->authenticate($request);

        if (!$user) {
            $request->disconnect(403, 'Session expired');
            return;
        }

        $request->respond(new RefreshResponse(expired: false));
    }
}
