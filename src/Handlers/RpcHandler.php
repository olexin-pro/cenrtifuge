<?php

declare(strict_types=1);

namespace OlexinPro\Cenrtifuge\Handlers;

use OlexinPro\Cenrtifuge\Contracts\Authenticator;
use OlexinPro\Cenrtifuge\Contracts\RequestHandler;
use OlexinPro\Cenrtifuge\Routing\RpcRouter;
use Psr\Log\LoggerInterface;
use RoadRunner\Centrifugo\Payload\RPCResponse;
use RoadRunner\Centrifugo\Request\RequestInterface;
use RoadRunner\Centrifugo\Request\RPC;

final readonly class RpcHandler implements RequestHandler
{
    public function __construct(
        private RpcRouter       $router,
        private Authenticator   $authenticator,
        private LoggerInterface $logger
    ) {}

    public function handle(RequestInterface $request): void
    {
        \assert($request instanceof RPC);

        $this->logger->info('RPC request', [
            'method' => $request->method,
            'client' => $request->client,
        ]);

        try {
            $user = $this->authenticator->authenticate($request);

            $result = $this->router->dispatch(
                method: $request->method,
                data: $request->getData(),
                user: $user,
                request: $request
            );

            $request->respond(new RPCResponse(data: $result));

        } catch (\Throwable $e) {
            $this->logger->error('RPC error', [
                'method' => $request->method,
                'error' => $e->getMessage(),
            ]);

            $request->respond(new RPCResponse(data: [
                'error' => $e->getMessage(),
            ]));
        }
    }
}
