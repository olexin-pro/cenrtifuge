<?php

declare(strict_types=1);

namespace OlexinPro\Cenrtifuge\Auth;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use OlexinPro\Centrifuge\Contracts\Authenticator;
use RoadRunner\Centrifugo\Request\RequestInterface;

final readonly class MultiGuardAuthenticator implements Authenticator
{
    public function __construct(
        private AuthFactory $auth,
        private array $guards = ['sanctum', 'session']
    ) {}

    public function authenticate(RequestInterface $request): ?Authenticatable
    {
        if ($token = $this->extractBearerToken($request)) {
            return $this->authenticateViaToken($token);
        }

        return null;
    }

    private function authenticateViaToken(string $token): ?Authenticatable
    {
        foreach ($this->guards as $guard) {
            try {
                $guardInstance = $this->auth->guard($guard);

                if (!method_exists($guardInstance, 'setToken')) {
                    continue;
                }

                if ($user = $guardInstance->setToken($token)->user()) {
                    return $user;
                }
            } catch (\Throwable) {
                continue;
            }
        }

        return null;
    }

    private function extractBearerToken(RequestInterface $request): ?string
    {
        $authHeader = $request->headers['authorization'] ?? null;

        if (!$authHeader) {
            return null;
        }

        $header = is_array($authHeader) ? $authHeader[0] : $authHeader;

        if (preg_match('/Bearer\s+(.+)$/i', $header, $matches)) {
            return $matches[1];
        }

        return null;
    }
}
