<?php

declare(strict_types=1);

namespace OlexinPro\Centrifuge\Contracts;

use Illuminate\Contracts\Auth\Authenticatable;

interface ChannelAccessControl
{
    public function canSubscribe(?Authenticatable $user, string $channel): bool;

    public function canPublish(?Authenticatable $user, string $channel): bool;
}
