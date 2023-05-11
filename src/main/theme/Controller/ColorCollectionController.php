<?php

namespace Claroline\ThemeBundle\Controller;

use Claroline\AppBundle\Controller\AbstractCrudController;
use Claroline\ThemeBundle\Entity\ColorCollection;
use Claroline\ThemeBundle\Manager\ColorCollectionManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


/**
 * ColorCollectionController.
 */
class ColorCollectionController extends AbstractCrudController
{
    private ColorCollectionManager $colorCollectionManager;

    public function __construct(ColorCollectionManager $colorCollectionManager)
    {
        $this->colorCollectionManager = $colorCollectionManager;
    }

    public function getName(): string
    {
        return 'color_collection';
    }

    public function getClass(): string
    {
        return ColorCollection::class;
    }

    /**
     * @Route("/{id?}", name="apiv2_color_chart_create_update", methods={"POST", "PUT"})
     */
    public function createOrUpdateAction(Request $request, ColorCollection $colorCollection = null): JsonResponse
    {
        $colorCollectionData = json_decode( $request->getContent(), true );

        if ($colorCollection) {
            // If a ColorCollection with the provided ID already exists, update it
            $colorCollection = $this->colorCollectionManager->updateColorCollection($colorCollection, $colorCollectionData);
        } else {
            // Otherwise, create a new ColorCollection
            $colorCollection = $this->colorCollectionManager->createColorCollection($colorCollectionData);
        }

        return new JsonResponse($colorCollection);
    }

    /**
     *
     * @Route("/{id}", name="apiv2_color_chart_delete", methods={"DELETE"})
     */
    public function deleteAction(ColorCollection $colorCollection): JsonResponse
    {
        $this->colorCollectionManager->deleteColorCollection($colorCollection);

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
