<?php

declare(strict_types=1);

namespace OlexinPro\Centrifuge\Auth;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Config\Repository as Config;
use OlexinPro\Centrifuge\Contracts\Authenticator;
use RoadRunner\Centrifugo\Request\Publish;
use RoadRunner\Centrifugo\Request\Refresh;
use RoadRunner\Centrifugo\Request\RequestInterface;
use RoadRunner\Centrifugo\Request\RPC as RpcRequest;
use RoadRunner\Centrifugo\Request\SubRefresh;
use RoadRunner\Centrifugo\Request\Subscribe;

final readonly class CentrifugoAuthenticator implements Authenticator
{
    public function __construct(
        private Config $config
    ) {}

    public function authenticate(RequestInterface $request): ?Authenticatable
    {
        if (!($request instanceof Subscribe
            || $request instanceof Publish
            || $request instanceof Refresh
            || $request instanceof SubRefresh
            || $request instanceof RpcRequest)) {
            return null;
        }

        $userID = $request->user;
        if (!$userID) {
            return null;
        }

        $userModel = $this->config->get('auth.providers.users.model', 'App\\Models\\User');

        if (!class_exists($userModel)) {
            return null;
        }

        return $userModel::find($userID);
    }
}
