<?php

declare(strict_types=1);

namespace OlexinPro\Cenrtifuge\Auth;

use Illuminate\Contracts\Auth\Authenticatable;
use OlexinPro\Cenrtifuge\Contracts\ChannelAccessControl;
use OlexinPro\Cenrtifuge\Routing\ChannelRouter;

final readonly class RoutingAccessControl implements ChannelAccessControl
{
    public function __construct(
        private ChannelRouter $router
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
        $callback = $this->router->resolve($channel);

        if (!$callback) {
            return false;
        }

        $parameters = $this->router->extractParameters($channel);

        return $callback($user, ...$parameters) === true;
    }
}
