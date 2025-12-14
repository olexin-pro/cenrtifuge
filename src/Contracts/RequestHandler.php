<?php

declare(strict_types=1);

namespace OlexinPro\Centrifuge\Contracts;

use RoadRunner\Centrifugo\Request\RequestInterface;

interface RequestHandler
{
    public function handle(RequestInterface $request): void;
}
