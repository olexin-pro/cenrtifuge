<?php

declare(strict_types=1);

namespace OlexinPro\Centrifuge\Transport;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use OlexinPro\Centrifuge\Contracts\CentrifugoTransportInterface;
use OlexinPro\Centrifuge\Exceptions\CentrifugoApiException;

final readonly class HttpTransport implements CentrifugoTransportInterface
{
    public function __construct(
        private ClientInterface $http,
    ) {}

    /**
     * @throws CentrifugoApiException
     */
    public function send(string $method, array $params): array
    {
        try {
            $response = $this->http->request('POST', "/api/{$method}", [
                'json' => $params,
            ]);
        } catch (GuzzleException $e) {
            throw CentrifugoApiException::transportError($method, $e->getMessage(), $e);
        }

        /** @var array{error?: array{code: int, message: string}, result?: array} $body */
        $body = json_decode((string) $response->getBody(), true) ?? [];

        if (isset($body['error'])) {
            throw CentrifugoApiException::serverError(
                method: $method,
                code: $body['error']['code'],
                message: $body['error']['message'],
            );
        }

        return $body['result'] ?? [];
    }
}
