<?php

declare(strict_types=1);

use Laravel\Octane\ApplicationFactory;
use OlexinPro\Cenrtifuge\Contracts\RequestHandler;
use Psr\Log\LoggerInterface;
use RoadRunner\Centrifugo\CentrifugoWorker as RRCentrifugoWorker;
use RoadRunner\Centrifugo\Request\Invalid;
use RoadRunner\Centrifugo\Request\RequestFactory;
use RoadRunner\Centrifugo\Request\RequestInterface;
use Spiral\RoadRunner\Worker as RoadRunnerWorker;
use Spiral\RoadRunnerLaravel\OctaneWorker;
use Spiral\RoadRunnerLaravel\WorkerInterface;
use Spiral\RoadRunnerLaravel\WorkerOptionsInterface;

final class CentrifugoWorker implements WorkerInterface
{
    public function start(WorkerOptionsInterface $options): void
    {
        $octaneWorker = new OctaneWorker(
            appFactory: new ApplicationFactory($options->getAppBasePath()),
        );

        $octaneWorker->boot();
        $app = $octaneWorker->application();
        $logger = $app->make(LoggerInterface::class);

        $logger->info('Centrifugo worker started');

        $handlers = $app->get('config')->get('centrifuge.handlers', []);

        $rrWorker = RoadRunnerWorker::create();
        $requestFactory = new RequestFactory($rrWorker);
        $centrifugoWorker = new RRCentrifugoWorker($rrWorker, $requestFactory);

        $logger->info('Centrifugo worker waiting for requests');

        while ($request = $centrifugoWorker->waitRequest()) {
            try {
                $this->handleRequest($request, $app, $handlers, $logger);
            } catch (\Throwable $e) {
                $logger->error('Request handling error', [
                    'error' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'type' => get_class($request),
                ]);

                $this->respondWithError($request, $e);
            }
        }
    }

    private function handleRequest(
        RequestInterface $request, $app,
        array $handlers,
        LoggerInterface $logger
    ): void {
        if ($request instanceof Invalid) {
            return;
        }

        $requestType = get_class($request);
        $handlerClass = $handlers[$requestType] ?? null;

        if (!$handlerClass) {
            $logger->warning('No handler registered', ['type' => $requestType]);
            $this->respondWithError($request, new \RuntimeException('Unsupported request type'), 400);
            return;
        }

        /** @var RequestHandler $handler */
        $handler = $app->make($handlerClass);
        $handler->handle($request);
    }

    private function respondWithError(RequestInterface $request, \Throwable $e, ?int $code = null): void
    {
        if ($request instanceof Invalid) {
            return;
        }

        $errorCode = $code ?? ($e->getCode() ?: 500);
        $request->error($errorCode, $e->getMessage());
    }
}
