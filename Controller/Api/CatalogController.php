<?php

/*
 * This file is part of the Melodia Catalog Bundle
 *
 * (c) Alexey Ryzhkov <alioch@yandex.ru>
 */

namespace Melodia\CatalogBundle\Controller\Api;

use Engage360d\Bundle\RestBundle\Controller\RestController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use JMS\Serializer\SerializationContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Melodia\CatalogBundle\Entity\Catalog;
use Melodia\CatalogBundle\Form\Type\CatalogFormType;

/**
 * Catalog controller
 *
 * @author Alexey Ryzhkov <alioch@yandex.ru>
 */
class CatalogController extends RestController
{
    /**
     * @ApiDoc(
     *  section="Catalog",
     *  description="Получение списка всех справочников.",
     *  filters={
     *      {
     *          "name"="page",
     *          "dataType"="integer",
     *          "default"=1,
     *          "required"=false
     *      },
     *      {
     *          "name"="limit",
     *          "dataType"="integer",
     *          "default"="inf",
     *          "required"=false
     *      }
     *  }
     * )
     */
    public function getCatalogsAction(Request $request)
    {
        $page = $request->query->get('page') ?: 1;
        // By default this method returns all records
        $limit = $request->query->get('limit') ?: 0;

        // Check filters' format
        if (!is_numeric($page) || !is_numeric($limit)) {
            return new JsonResponse(null, 400);
        }

        $catalogs = $this->getDoctrine()->getRepository(Catalog::REPOSITORY)
            ->findSubset($page, $limit);
        $catalogs = $this->get('jms_serializer')->serialize($catalogs, 'json',
            SerializationContext::create()->setGroups(array("getAllCatalogs"))
        );

        return new Response($catalogs, 200);
    }

    /**
     * @ApiDoc(
     *  section="Catalog",
     *  description="Получение детальной информации о справочнике.",
     *  requirements={
     *      {
     *          "name"="id",
     *          "dataType"="string",
     *          "requirement"="[0-9A-Za-z_\-]+",
     *          "description"="Catalog id"
     *      }
     *  }
     * )
     */
    public function getCatalogAction($id)
    {
        $catalog = $this->getDoctrine()->getRepository(Catalog::REPOSITORY)
            ->findOneBy(array('id' => $id));

        if (!$catalog) {
            return new JsonResponse(null, 404);
        }

        $catalog = $this->get('jms_serializer')->serialize($catalog, 'json',
            SerializationContext::create()->setGroups(array("getOneCatalog", "getOneRecord"))
        );

        return new Response($catalog, 200);
    }

    /**
     * @ApiDoc(
     *  section="Catalog",
     *  description="Создание нового справочника.",
     *  input="Melodia\CatalogBundle\Form\Type\CatalogFormType",
     *  output="Melodia\CatalogBundle\Entity\Catalog"
     * )
     */
    public function postCatalogsAction(Request $request)
    {
        $catalog = new Catalog();

        $form = $this->createForm(new CatalogFormType(), $catalog);
        $form->submit($request->request->all());

        if (!$form->isValid()) {
            return new JsonResponse($this->getErrorMessages($form), 400);
        }

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($catalog);
        $entityManager->flush();

        $catalog = $this->get('jms_serializer')->serialize($catalog, 'json',
            SerializationContext::create()->setGroups(array("getOneCatalog", "getOneRecord"))
        );

        return new Response($catalog, 201);
    }

    /**
     * @ApiDoc(
     *  section="Catalog",
     *  description="Редактирование каталога.",
     *  requirements={
     *      {
     *          "name"="id",
     *          "dataType"="[0-9A-Za-z_\-]+",
     *          "requirement"="\d+",
     *          "description"="Catalog id"
     *      }
     *  },
     *  input="Melodia\CatalogBundle\Form\Type\CatalogFormType",
     *  output="Melodia\CatalogBundle\Entity\Catalog"
     * )
     */
    public function putCatalogsAction($id, Request $request)
    {
        $catalog = $this->getDoctrine()->getRepository(Catalog::REPOSITORY)
            ->findOneBy(array('id' => $id));

        if (!$catalog) {
            return new JsonResponse(null, 404);
        }

        $form = $this->createForm(new CatalogFormType(), $catalog);
        $form->submit($request->request->all());

        if (!$form->isValid()) {
            return new JsonResponse($this->getErrorMessages($form), 400);
        }

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($catalog);
        $entityManager->flush();

        $catalog = $this->get('jms_serializer')->serialize($catalog, 'json',
            SerializationContext::create()->setGroups(array("getOneCatalog", "getOneRecord"))
        );

        return new Response($catalog, 200);
    }

    /**
     * @ApiDoc(
     *  section="Catalog",
     *  description="Удаление записи.",
     *  requirements={
     *      {
     *          "name"="id",
     *          "dataType"="[0-9A-Za-z_\-]+",
     *          "requirement"="\d+",
     *          "description"="Catalog id"
     *      }
     *  }
     * )
     */
    public function deleteCatalogAction($id)
    {
        $catalog = $this->getDoctrine()->getRepository(Catalog::REPOSITORY)
            ->findOneBy(array('id' => $id));

        if (!$catalog) {
            return new JsonResponse(null, 404);
        }

        $entityManager = $this->getDoctrine()->getManager();

        // At first, delete all catalog's records
        foreach ($catalog->getRecords() as $record) {
            $entityManager->remove($record);
            $entityManager->flush();
        }

        $entityManager->remove($catalog);
        $entityManager->flush();

        return new JsonResponse(null, 200);
    }
}
