<?php

declare(strict_types=1);

use Illuminate\Contracts\Auth\Authenticatable;
use OlexinPro\Centrifuge\Routing\ChannelRouter;

/** @var ChannelRouter $centrifuge */

// private channels
// $centrifuge->channel('private-user.{userId}', function (?Authenticatable $user, string $userId) {
//     return $user && (int) $user->getAuthIdentifier() === (int) $userId;
// });

// public channels
// $centrifuge->channel('public.chat', function () {
//     return true;
// });
