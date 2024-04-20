<?php

declare(strict_types=1);

namespace Api\Ui\Controller\Product;

use Api\Application\Command\Product\CreateProductCommand;
use Api\Domain\Collection\Common\FormErrorDtoCollection;
use Api\Domain\Dto\Common\FormErrorDto;
use Api\Domain\Dto\Common\FormResponseDto;
use Api\Domain\Service\User\UserWebTransformer;
use Api\Ui\Request\Product\CreateProductRequest;
use App\Domain\Dto\Product\DetalleProduct;
use App\Domain\Exception\Model\User\UserWeb\UserWebNotFound;
use App\Infrastructure\Security\User\SfUserWeb;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use App\Domain\CommandBus\CommandBusRead;
use App\Domain\CommandBus\CommandBusWrite;

#[AsController]
final class CreateProductController extends AbstractController
{

    public const TIPO_FORM = 'create-product';

    /**
     * @var CommandBusWrite
     */
    private CommandBusWrite $commandBusWrite;

    /**
     * @var CommandBusRead
     */
    private CommandBusRead $commandBusRead;

    /**
     * @var ValidatorInterface
     */
    private ValidatorInterface $validator;


    private SfUserWeb $userWeb;

    #[Route(
        path: '/form/{tipoForm}',
        name: 'api_create_product_form',
        defaults: [
            '_api_resource_class' => FormResponseDto::class,
            '_api_item_operation_name' => 'product_form',
        ],
        methods: ['POST'],
    )]
    public function createProduct(
        Request            $request,
        string             $tipoForm,
        CommandBusWrite    $commandBusWrite,
        CommandBusRead     $commandBusRead,
        ValidatorInterface $validator
    ): JsonResponse
    {
        $this->commandBusWrite = $commandBusWrite;
        $this->commandBusRead = $commandBusRead;
        $this->validator = $validator;

        $this->userWeb       = UserWebTransformer::transform($this->getUser());

        $postData = $this->getPostData($request);
        $formResponseDto = null;
        if ($tipoForm === self::TIPO_FORM) {
            $formResponseDto = $this->formCreateProduct($postData);
        }

        if ($formResponseDto === null) {
            return new JsonResponse(FormResponseDto::formFail(
                self::TIPO_FORM,
                $this->errorMessage ?? 'Error al procesar el formulario',
                $this->errors
            )->toArray(), Response::HTTP_BAD_REQUEST);
        }

        return new JsonResponse($formResponseDto->toArray(), Response::HTTP_CREATED);
    }

    private function getPostData(Request $request): array
    {
        $postData = json_decode($request->getContent(), true);

        if (!$postData) {
            $postData = [];
        }

        return $postData;
    }

    /**
     * @param array $postData
     *
     * @return FormResponseDto|null
     */
    private function formCreateProduct(array $postData): ?FormResponseDto
    {
        $request = CreateProductRequest::fromArray($postData, $this->validator);

        if (!$request->isValid()) {
            $this->errorMessage = 'No se ha podido crear el producto';
            $this->errors = $request->getErrors();

            return null;
        }

        try {
            $product = $this->commandBusWrite->handle(
                new CreateProductCommand(
                    $this->userWeb->getId(),
                    $request->name,
                    $request->description,
                    $request->price,
                    $request->iva
                )
            );

            /** @var DetalleProduct $product */
            return FormResponseDto::formSuccess(
                self::TIPO_FORM,
                'Se ha creado el producto',
                [
                    'product_id' => $product->id->asString(),
                ],
            );
        } catch (\Throwable $e) {
            $this->errorMessage = 'No se ha podido crear el producto';
            $this->errors = FormErrorDtoCollection::fromElements(
                [
                    FormErrorDto::create(
                        'error',
                        $e->getMessage()
                    ),
                ]
            );

            if ($e instanceof UserWebNotFound) {
                $this->errorMessage = 'El usuario no existe';
            }

            return null;
        }
    }
}