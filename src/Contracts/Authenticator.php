<?php

declare(strict_types=1);

namespace OlexinPro\Cenrtifuge\Contracts;

use Illuminate\Contracts\Auth\Authenticatable;
use RoadRunner\Centrifugo\Request\RequestInterface;

interface Authenticator
{
    public function authenticate(RequestInterface $request): ?Authenticatable;
}
