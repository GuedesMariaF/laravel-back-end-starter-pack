<?php

namespace App\Scramble;

use Dedoc\Scramble\Extensions\ExceptionToResponseExtension;
use Dedoc\Scramble\Support\Generator\Reference;
use Dedoc\Scramble\Support\Generator\Response;
use Dedoc\Scramble\Support\Generator\Schema;
use Dedoc\Scramble\Support\Generator\Types as OpenApiTypes;
use Dedoc\Scramble\Support\Type\ObjectType;
use Dedoc\Scramble\Support\Type\Type;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Str;

/**
 * Documenta respostas 403 com o envelope padrão da API (ReturnApi),
 * conforme o handler de AccessDeniedHttpException em bootstrap/app.php.
 */
class AuthorizationExceptionToResponseExtension extends ExceptionToResponseExtension
{
    public function shouldHandle(Type $type): bool
    {
        return $type instanceof ObjectType
            && $type->isInstanceOf(AuthorizationException::class);
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
                    ->example('Acesso negado.')
            )
            ->addProperty(
                'data',
                (new OpenApiTypes\StringType)
                    ->setDescription('Mensagem original da exceção.')
                    ->example('This action is unauthorized.')
            )
            ->setRequired(['error', 'message', 'data']);

        return Response::make(403)
            ->setDescription('Acesso negado')
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
