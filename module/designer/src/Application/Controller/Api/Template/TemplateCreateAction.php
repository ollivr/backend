<?php

/**
 * Copyright © Bold Brand Commerce Sp. z o.o. All rights reserved.
 * See LICENSE.txt for license details.
 */

declare(strict_types = 1);

namespace Ergonode\Designer\Application\Controller\Api\Template;

use Ergonode\Api\Application\Exception\FormValidationHttpException;
use Ergonode\Api\Application\Response\CreatedResponse;
use Ergonode\Designer\Application\Form\TemplateForm;
use Ergonode\Designer\Application\Model\Form\TemplateFormModel;
use Ergonode\Designer\Infrastructure\Factory\TemplateCommandFactory;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Swagger\Annotations as SWG;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Ergonode\EventSourcing\Infrastructure\Bus\CommandBusInterface;

/**
 * @Route("/templates", methods={"POST"})
 */
class TemplateCreateAction
{
    /**
     * @var CommandBusInterface
     */
    private CommandBusInterface $commandBus;

    /**
     * @var FormFactoryInterface
     */
    private FormFactoryInterface $formFactory;

    /**
     * @var TemplateCommandFactory
     */
    private TemplateCommandFactory $commandFactory;

    /**
     * @param CommandBusInterface    $commandBus
     * @param FormFactoryInterface   $formFactory
     * @param TemplateCommandFactory $commandFactory
     */
    public function __construct(
        CommandBusInterface $commandBus,
        FormFactoryInterface $formFactory,
        TemplateCommandFactory $commandFactory
    ) {
        $this->commandBus = $commandBus;
        $this->formFactory = $formFactory;
        $this->commandFactory = $commandFactory;
    }

    /**
     * @IsGranted("TEMPLATE_DESIGNER_CREATE")
     *
     * @SWG\Tag(name="Designer")
     * @SWG\Parameter(
     *     name="body",
     *     in="body",
     *     description="Add template",
     *     required=true,
     *     @SWG\Schema(ref="#/definitions/template")
     * )
     * @SWG\Parameter(
     *     name="language",
     *     in="path",
     *     type="string",
     *     required=true,
     *     default="en_GB",
     *     description="Language Code"
     * )
     * @SWG\Response(
     *     response=201,
     *     description="Create template"
     * )
     * @SWG\Response(
     *     response="400",
     *     description="Validation error",
     *     @SWG\Schema(ref="#/definitions/validation_error_response")
     * )
     *
     * @param Request $request
     *
     * @return Response
     *
     * @throws \Exception
     */
    public function __invoke(Request $request): Response
    {
        $model = new TemplateFormModel();
        $form = $this->formFactory->create(TemplateForm::class, $model);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $command = $this->commandFactory->getCreateTemplateCommand($form->getData());
            $this->commandBus->dispatch($command);

            return new CreatedResponse($command->getId());
        }

        throw new FormValidationHttpException($form);
    }
}
