<?php

declare(strict_types=1);

namespace OlexinPro\Cenrtifuge\Auth;

use Illuminate\Broadcasting\BroadcastManager;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;
use OlexinPro\Cenrtifuge\Contracts\ChannelAccessControl;

final readonly class BroadcastingAccessControl implements ChannelAccessControl
{
    public function __construct(
        private BroadcastManager $broadcast
    ) {}

    public function canSubscribe(?Authenticatable $user, string $channel): bool
    {
        return $this->checkAccess($user, $channel);
    }

    public function canPublish(?Authenticatable $user, string $channel): bool
    {
        return $this->checkAccess($user, $channel);
    }

    private function checkAccess(?Authenticatable $user, string $channel): bool
    {
        try {
            $request = $this->createAuthRequest($user, $channel);
            $result = $this->broadcast->auth($request);

            return $result !== false;
        } catch (\Throwable) {
            return false;
        }
    }

    private function createAuthRequest(?Authenticatable $user, string $channelName): Request
    {
        $request = Request::create('/broadcasting/auth', 'POST', [
            'channel_name' => $channelName,
        ]);

        if ($user) {
            $request->setUserResolver(fn() => $user);
        }

        return $request;
    }
}
