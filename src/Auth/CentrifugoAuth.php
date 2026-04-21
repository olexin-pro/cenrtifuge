<?php

declare(strict_types=1);

namespace OlexinPro\Centrifuge\Auth;


final readonly class CentrifugoAuth
{
    public function __construct(
        private string $hmacToken
    ) { }

    public function generateConnectionToken(string $userId = '', int $exp = 0, array $info = [], array $channels = []): string
    {
        $header = ['typ' => 'JWT', 'alg' => 'HS256'];
        $payload = ['sub' => $userId];
        if (!empty($info)) {
            $payload['info'] = $info;
        }
        if (!empty($channels)) {
            $payload['channels'] = $channels;
        }
        if ($exp) {
            $payload['exp'] = now()->addSeconds($exp)->timestamp;
            $payload['iat'] = now()->timestamp;
        }
        $segments = [];
        $segments[] = $this->urlsafeB64Encode(json_encode($header));
        $segments[] = $this->urlsafeB64Encode(json_encode($payload));
        $signing_input = implode('.', $segments);
        $signature = $this->sign($signing_input, $this->hmacToken);
        $segments[] = $this->urlsafeB64Encode($signature);

        return implode('.', $segments);
    }

    /**
     * Sign message with secret key.
     *
     * @param string $msg
     * @param string $key
     *
     * @return string
     */
    private function sign(string $msg, string $key): string
    {
        return hash_hmac('sha256', $msg, $key, true);
    }

    /**
     * Safely encode string in base64.
     *
     * @param string $input
     *
     * @return string
     */
    private function urlsafeB64Encode(string $input): string
    {
        return str_replace('=', '', strtr(base64_encode($input), '+/', '-_'));
    }
}
