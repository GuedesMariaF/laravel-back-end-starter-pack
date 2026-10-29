<?php

namespace App\Scramble;

use App\Exceptions\ApiException;
use Dedoc\Scramble\Extensions\ExceptionToResponseExtension;
use Dedoc\Scramble\Support\Generator\Reference;
use Dedoc\Scramble\Support\Generator\Response;
use Dedoc\Scramble\Support\Generator\Schema;
use Dedoc\Scramble\Support\Generator\Types as OpenApiTypes;
use Dedoc\Scramble\Support\Type\ObjectType;
use Dedoc\Scramble\Support\Type\Type;
use Illuminate\Support\Str;

/**
 * Documenta a ApiException com o envelope padrão da API (ReturnApi).
 *
 * O status da ApiException é definido em tempo de execução, então o Scramble
 * não consegue inferi-lo; documentamos o padrão (500), usado pelos catch
 * genéricos dos controllers. Os 404 vêm da ModelNotFoundOperationExtension.
 */
class ApiExceptionToResponseExtension extends ExceptionToResponseExtension
{
    public function shouldHandle(Type $type): bool
    {
        return $type instanceof ObjectType
            && $type->isInstanceOf(ApiException::class);
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
                    ->example('Erro inesperado')
            )
            ->addProperty('data', new OpenApiTypes\MixedType)
            ->setRequired(['error', 'message', 'data']);

        return Response::make(500)
            ->setDescription('Erro interno')
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
