<?php

namespace App\Scramble;

use Dedoc\Scramble\Extensions\OperationExtension;
use Dedoc\Scramble\Support\Generator\Operation;
use Dedoc\Scramble\Support\RouteInfo;
use Dedoc\Scramble\Support\Type\ObjectType;
use Dedoc\Scramble\Support\Type\Type;
use Illuminate\Auth\AuthenticationException;

/**
 * Anexa a AuthenticationException a todo endpoint protegido pelo grupo
 * "auth.api" (JwtMiddleware).
 *
 * O ErrorResponsesExtension do Scramble só reconhece os middlewares "auth" e
 * "auth:*", então não documenta o 401 das rotas deste projeto sozinho.
 */
class AuthenticationOperationExtension extends OperationExtension
{
    public function handle(Operation $operation, RouteInfo $routeInfo): void
    {
        if (! $methodType = $routeInfo->getActionType()) {
            return;
        }

        if (! in_array('auth.api', $routeInfo->route->gatherMiddleware(), true)) {
            return;
        }

        $alreadyAttached = collect($methodType->exceptions)
            ->contains(fn (Type $e) => $e->isInstanceOf(AuthenticationException::class));

        if ($alreadyAttached) {
            return;
        }

        $methodType->exceptions = [
            ...$methodType->exceptions,
            new ObjectType(AuthenticationException::class),
        ];
    }
}
