<?php

declare(strict_types=1);

namespace OlexinPro\Centrifuge;

use OlexinPro\Centrifuge\Contracts\CentrifugoApiInterface;
use OlexinPro\Centrifuge\Contracts\CentrifugoTransportInterface;
use OlexinPro\Centrifuge\Exceptions\CentrifugoApiException;

final readonly class CentrifugoApi implements CentrifugoApiInterface
{
    public function __construct(
        private CentrifugoTransportInterface $transport,
    ) {}

    /**
     * @throws CentrifugoApiException
     */
    public function publish(string $channel, array $data): array
    {
        return $this->transport->send('publish', [
            'channel' => $channel,
            'data'    => $data,
        ]);
    }

    /**
     * @throws CentrifugoApiException
     */
    public function broadcast(array $channels, array $data): array
    {
        return $this->transport->send('broadcast', [
            'channels' => $channels,
            'data'     => $data,
        ]);
    }

    /**
     * @throws CentrifugoApiException
     */
    public function presence(string $channel): array
    {
        return $this->transport->send('presence', [
            'channel' => $channel,
        ]);
    }

    /**
     * @throws CentrifugoApiException
     */
    public function presenceStats(string $channel): array
    {
        return $this->transport->send('presence_stats', [
            'channel' => $channel,
        ]);
    }

    /**
     * @throws CentrifugoApiException
     */
    public function history(string $channel, int $limit = 0, bool $reverse = false): array
    {
        $params = ['channel' => $channel];

        if ($limit > 0) {
            $params['limit'] = $limit;
        }

        if ($reverse) {
            $params['reverse'] = true;
        }

        return $this->transport->send('history', $params);
    }

    /**
     * @throws CentrifugoApiException
     */
    public function historyRemove(string $channel): array
    {
        return $this->transport->send('history_remove', [
            'channel' => $channel,
        ]);
    }

    /**
     * @throws CentrifugoApiException
     */
    public function disconnect(string $userId): array
    {
        return $this->transport->send('disconnect', [
            'user' => $userId,
        ]);
    }

    /**
     * @throws CentrifugoApiException
     */
    public function unsubscribe(string $userId, string $channel): array
    {
        return $this->transport->send('unsubscribe', [
            'user'    => $userId,
            'channel' => $channel,
        ]);
    }

    /**
     * @throws CentrifugoApiException
     */
    public function channels(string $pattern = ''): array
    {
        $params = [];

        if ($pattern !== '') {
            $params['pattern'] = $pattern;
        }

        return $this->transport->send('channels', $params);
    }

    /**
     * @throws CentrifugoApiException
     */
    public function info(): array
    {
        return $this->transport->send('info', []);
    }
}
