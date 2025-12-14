<?php

declare(strict_types=1);

namespace OlexinPro\Centrifuge\Auth;

use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Config\Repository as Config;
use OlexinPro\Centrifuge\Contracts\Authenticator;
use RoadRunner\Centrifugo\Request\RequestInterface;

final readonly class CentrifugoAuthenticator implements Authenticator
{
    public function __construct(
        private Config $config
    ) {}

    public function authenticate(RequestInterface $request): ?Authenticatable
    {
        $userID = $request->user;
        if (!$userID) {
            return null;
        }

        $userModel = $this->config->get('auth.providers.users.model', User::class);

        if (!class_exists($userModel)) {
            return null;
        }

        return $userModel::find($userID);
    }
}
