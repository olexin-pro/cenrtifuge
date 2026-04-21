<?php

declare(strict_types=1);

namespace OlexinPro\Centrifuge\Transport;

use RoadRunner\Centrifugo\Exception\CentrifugoApiResponseException;
use RoadRunner\Centrifugo\RPCCentrifugoApi;
use Spiral\Goridge\RPC\Exception\RPCException;
use OlexinPro\Centrifuge\Contracts\CentrifugoTransportInterface;
use OlexinPro\Centrifuge\Exceptions\CentrifugoApiException;

final readonly class RpcTransport implements CentrifugoTransportInterface
{
    public function __construct(
        private RPCCentrifugoApi $api,
    ) {}

    /**
     * @throws CentrifugoApiException
     */
    public function send(string $method, array $params): array
    {
        try {
            return match ($method) {
                'publish'        => $this->publish($params),
                'broadcast'      => $this->broadcast($params),
                'presence'       => $this->api->presence($params['channel']),
                'presence_stats' => $this->api->presenceStats($params['channel']),
                'channels'       => $this->api->channels($params['pattern'] ?? null),
                'disconnect'     => $this->disconnect($params),
                'unsubscribe'    => $this->unsubscribe($params),
                default          => throw new CentrifugoApiException(
                    "Method '{$method}' is not supported by RPC transport.",
                ),
            };
        } catch (CentrifugoApiResponseException | RPCException $e) {
            throw CentrifugoApiException::transportError($method, $e->getMessage(), $e);
        }
    }

    private function publish(array $params): array
    {
        $this->api->publish(
            $params['channel'],
            json_encode($params['data'], JSON_THROW_ON_ERROR),
        );

        return [];
    }

    private function broadcast(array $params): array
    {
        $this->api->broadcast(
            $params['channels'],
            json_encode($params['data'], JSON_THROW_ON_ERROR),
        );

        return [];
    }

    private function disconnect(array $params): array
    {
        $this->api->disconnect($params['user']);

        return [];
    }

    private function unsubscribe(array $params): array
    {
        $this->api->unsubscribe($params['channel'], $params['user']);

        return [];
    }
}
