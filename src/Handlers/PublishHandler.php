<?php

declare(strict_types=1);

namespace OlexinPro\Cenrtifuge\Handlers;

use OlexinPro\Centrifuge\Contracts\Authenticator;
use OlexinPro\Centrifuge\Contracts\ChannelAccessControl;
use OlexinPro\Centrifuge\Contracts\RequestHandler;
use Psr\Log\LoggerInterface;
use RoadRunner\Centrifugo\Payload\PublishResponse;
use RoadRunner\Centrifugo\Request\Publish;
use RoadRunner\Centrifugo\Request\RequestInterface;

final readonly class PublishHandler implements RequestHandler
{
    public function __construct(
        private Authenticator        $authenticator,
        private ChannelAccessControl $accessControl,
        private LoggerInterface      $logger
    ) {}

    public function handle(RequestInterface $request): void
    {
        \assert($request instanceof Publish);

        $this->logger->debug('Client publishing', [
            'client' => $request->client,
            'channel' => $request->channel,
        ]);

        $user = $this->authenticator->authenticate($request);

        if (!$this->accessControl->canPublish($user, $request->channel)) {
            $request->disconnect(403, 'Publish forbidden');
            return;
        }

        $request->respond(new PublishResponse());
    }
}
