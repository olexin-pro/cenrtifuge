<?php

declare(strict_types=1);

namespace OlexinPro\Cenrtifuge\Auth;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use OlexinPro\Centrifuge\Contracts\Authenticator;
use Illuminate\Session\SessionManager;
use Illuminate\Contracts\Config\Repository as Config;
use RoadRunner\Centrifugo\Request\RequestInterface;

final readonly class MultiGuardAuthenticator implements Authenticator
{
    public function __construct(
        private AuthFactory $auth,
        private SessionManager $session,
        private Config $config,
        private array $guards = ['sanctum', 'session']
    ) {}

    public function authenticate(RequestInterface $request): ?Authenticatable
    {
        if ($token = $this->extractBearerToken($request)) {
            return $this->authenticateViaToken($token);
        }

        if ($user = $this->authenticateViaSession($request)) {
            return $user;
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

    private function authenticateViaSession(RequestInterface $request): ?Authenticatable
    {
        $sessionId = $this->extractSessionId($request);

        if (!$sessionId) {
            return null;
        }
        $sessionHandler = $this->session->getHandler();
        $sessionData = $sessionHandler->read($sessionId);

        if (!$sessionData) {
            return null;
        }

        $data = @unserialize($sessionData);

        if (!is_array($data)) {
            return null;
        }
        $defaultGuard = $this->config->get('auth.defaults.guard', 'web');
        $authKey = 'login_' . $defaultGuard . '_' . sha1(static::class);
        $userId = $data[$authKey] ?? null;

        if (!$userId) {
            return null;
        }

        $userModel = $this->config->get('auth.providers.users.model', 'App\\Models\\User');

        if (!class_exists($userModel)) {
            return null;
        }

        return $userModel::find($userId);
    }

    private function extractSessionId(RequestInterface $request): ?string
    {
        $cookieHeader = $request->headers['cookie'] ?? null;

        if (!$cookieHeader) {
            return null;
        }

        $cookieString = is_array($cookieHeader) ? $cookieHeader[0] : $cookieHeader;
        $sessionName = $this->config->get('session.cookie', 'laravel_session');

        if (preg_match('/' . preg_quote($sessionName, '/') . '=([^;]+)/', $cookieString, $matches)) {
            return urldecode($matches[1]);
        }

        return null;
    }
}
