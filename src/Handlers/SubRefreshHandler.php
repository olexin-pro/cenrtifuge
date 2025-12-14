<?php

declare(strict_types=1);

namespace OlexinPro\Centrifuge\Handlers;

use OlexinPro\Centrifuge\Contracts\Authenticator;
use OlexinPro\Centrifuge\Contracts\RequestHandler;
use Psr\Log\LoggerInterface;
use RoadRunner\Centrifugo\Payload\SubRefreshResponse;
use RoadRunner\Centrifugo\Request\RequestInterface;
use RoadRunner\Centrifugo\Request\SubRefresh;

final readonly class SubRefreshHandler implements RequestHandler
{
    public function __construct(
        private Authenticator   $authenticator,
        private LoggerInterface $logger
    ) {}

    public function handle(RequestInterface $request): void
    {
        \assert($request instanceof SubRefresh);

        $this->logger->debug('Subscription refresh', [
            'client' => $request->client,
            'channel' => $request->channel,
        ]);

        $user = $this->authenticator->authenticate($request);

        if (!$user) {
            $request->disconnect(403, 'Session expired');
            return;
        }

        $request->respond(new SubRefreshResponse(expired: false));
    }
}
