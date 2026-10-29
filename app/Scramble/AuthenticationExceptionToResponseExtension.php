<?php

namespace App\Scramble;

use Dedoc\Scramble\Extensions\ExceptionToResponseExtension;
use Dedoc\Scramble\Support\Generator\Reference;
use Dedoc\Scramble\Support\Generator\Response;
use Dedoc\Scramble\Support\Generator\Schema;
use Dedoc\Scramble\Support\Generator\Types as OpenApiTypes;
use Dedoc\Scramble\Support\Type\ObjectType;
use Dedoc\Scramble\Support\Type\Type;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Str;

/**
 * Documenta respostas 401 com o envelope padrão da API (ReturnApi),
 * conforme o JwtMiddleware (grupo "auth.api").
 */
class AuthenticationExceptionToResponseExtension extends ExceptionToResponseExtension
{
    public function shouldHandle(Type $type): bool
    {
        return $type instanceof ObjectType
            && $type->isInstanceOf(AuthenticationException::class);
    }

    public function toResponse(Type $type): Response
    {
        $responseBodyType = (new OpenApiTypes\ObjectType)
            ->addProperty(
                'error',
                (new OpenApiTypes\BooleanType)
                    ->example(true)
            )
            ->addProperty(
                'message',
                (new OpenApiTypes\StringType)
                    ->setDescription('Motivo da falha de autenticação.')
                    ->enum(['Não autorizado', 'Não Autorizado', 'Token Inválido', 'Token Expirado'])
            )
            ->addProperty('data', new OpenApiTypes\NullType)
            ->setRequired(['error', 'message', 'data']);

        return Response::make(401)
            ->setDescription('Não autenticado')
            ->setContent(
                'application/json',
                Schema::fromType($responseBodyType),
            );
    }

    public function reference(ObjectType $type): Reference
    {
        return new Reference('responses', Str::start($type->name, '\\'), $this->components);
    }
}
