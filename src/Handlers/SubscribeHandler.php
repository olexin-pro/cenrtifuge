<?php

declare(strict_types=1);

namespace OlexinPro\Centrifuge\Handlers;

use OlexinPro\Cenrtifuge\Contracts\Authenticator;
use OlexinPro\Cenrtifuge\Contracts\ChannelAccessControl;
use OlexinPro\Cenrtifuge\Contracts\RequestHandler;
use Psr\Log\LoggerInterface;
use RoadRunner\Centrifugo\Payload\SubscribeResponse;
use RoadRunner\Centrifugo\Request\RequestInterface;
use RoadRunner\Centrifugo\Request\Subscribe;

final readonly class SubscribeHandler implements RequestHandler
{
    public function __construct(
        private Authenticator        $authenticator,
        private ChannelAccessControl $accessControl,
        private LoggerInterface      $logger
    ) {}

    public function handle(RequestInterface $request): void
    {
        \assert($request instanceof Subscribe);

        $this->logger->debug('Client subscribing', [
            'client' => $request->client,
            'channel' => $request->channel,
        ]);

        $user = $this->authenticator->authenticate($request);

        if (!$this->accessControl->canSubscribe($user, $request->channel)) {
            $request->disconnect(403, 'Subscription forbidden');
            return;
        }

        $request->respond(new SubscribeResponse());
    }
}
