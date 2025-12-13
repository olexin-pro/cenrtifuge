<?php

declare(strict_types=1);

use OlexinPro\Cenrtifuge\Routing\RpcRouter;


/** @var RpcRouter $rpc */

// Global middleware
// $rpc->middleware([
//     \App\Centrifuge\Middleware\LogRpcRequest::class,
// ]);

// Public methods
// $rpc->register('ping', \App\Centrifuge\Handlers\PingHandler::class);

// Group with authentication
// $rpc->group('', [\App\Centrifuge\Middleware\RequireAuth::class], function (RpcRouter $rpc) {
//     $rpc->group('posts', [], function (RpcRouter $rpc) {
//         $rpc->register('create', \App\Centrifuge\Handlers\CreatePostHandler::class);
//     });
// });
