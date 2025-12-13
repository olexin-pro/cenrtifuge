<?php

declare(strict_types=1);

namespace OlexinPro\Cenrtifuge\Contracts;

use RoadRunner\Centrifugo\Request\RequestInterface;

interface RequestHandler
{
    public function handle(RequestInterface $request): void;
}
