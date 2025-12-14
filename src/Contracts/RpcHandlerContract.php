<?php

declare(strict_types=1);

namespace OlexinPro\Centrifuge\Contracts;

use Illuminate\Contracts\Auth\Authenticatable;
use RoadRunner\Centrifugo\Request\RPC;

interface RpcHandlerContract
{
    /**
     * Handle RPC request
     *
     * @param array<string, mixed> $data Request data
     * @param Authenticatable|null $user Authenticated user or null
     * @param RPC $request Original RPC request
     * @return mixed Response data (will be serialized to JSON)
     */
    public function handle(array $data, ?Authenticatable $user, RPC $request): mixed;
}
