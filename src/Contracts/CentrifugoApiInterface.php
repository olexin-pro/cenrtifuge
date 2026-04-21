<?php

declare(strict_types=1);

namespace OlexinPro\Centrifuge\Contracts;

use OlexinPro\Centrifuge\Exceptions\CentrifugoApiException;

interface CentrifugoApiInterface
{
    /**
     * Publish data to a single channel.
     *
     * @throws CentrifugoApiException
     */
    public function publish(string $channel, array $data): array;

    /**
     * Broadcast data to multiple channels at once.
     *
     * @param  string[]  $channels
     * @throws CentrifugoApiException
     */
    public function broadcast(array $channels, array $data): array;

    /**
     * Get presence info (currently subscribed clients) for a channel.
     *
     * @throws CentrifugoApiException
     */
    public function presence(string $channel): array;

    /**
     * Get presence stats (num clients / users) for a channel.
     *
     * @throws CentrifugoApiException
     */
    public function presenceStats(string $channel): array;

    /**
     * Get publication history for a channel.
     *
     * @throws CentrifugoApiException
     */
    public function history(string $channel, int $limit = 0, bool $reverse = false): array;

    /**
     * Remove history for a channel.
     *
     * @throws CentrifugoApiException
     */
    public function historyRemove(string $channel): array;

    /**
     * Forcibly disconnect a user by user ID.
     *
     * @throws CentrifugoApiException
     */
    public function disconnect(string $userId): array;

    /**
     * Forcibly unsubscribe a user from a channel.
     *
     * @throws CentrifugoApiException
     */
    public function unsubscribe(string $userId, string $channel): array;

    /**
     * Get a list of active channels.
     *
     * @throws CentrifugoApiException
     */
    public function channels(string $pattern = ''): array;

    /**
     * Get Centrifugo server info.
     *
     * @throws CentrifugoApiException
     */
    public function info(): array;
}
