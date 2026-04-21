<?php

declare(strict_types=1);

namespace OlexinPro\Centrifuge\Contracts;

use OlexinPro\Centrifuge\Exceptions\CentrifugoApiException;

interface CentrifugoTransportInterface
{
    /**
     * Send a command to Centrifugo.
     *
     * @param  string  $method  Centrifugo API method (e.g. "publish", "broadcast")
     * @param  array   $params  Method parameters
     * @return array            Decoded response body
     *
     * @throws CentrifugoApiException
     */
    public function send(string $method, array $params): array;
}
