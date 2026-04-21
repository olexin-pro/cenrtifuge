<?php

declare(strict_types=1);

namespace OlexinPro\Centrifuge\Transport;

use OlexinPro\Centrifuge\Contracts\CentrifugoTransportInterface;
use OlexinPro\Centrifuge\Exceptions\CentrifugoApiException;

final class GrpcTransport implements CentrifugoTransportInterface
{
    // TODO: inject generated gRPC client (e.g. \Centrifugal\Centrifugo\ApiClient)
    //       and implement send() by mapping $method → gRPC call.
    //
    // Requires: ext-grpc, google/protobuf, centrifugal/centrifugo-proto

    public function send(string $method, array $params): array
    {
        throw new CentrifugoApiException('gRPC transport is not implemented yet.');
    }
}
