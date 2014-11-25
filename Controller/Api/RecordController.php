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
use Melodia\CatalogBundle\Entity\Record;
use Melodia\CatalogBundle\Form\Type\RecordFormType;

/**
 * Record controller
 *
 * @author Alexey Ryzhkov <alioch@yandex.ru>
 */
class RecordController extends RestController
{
    /**
     * @ApiDoc(
     *  section="Catalog record",
     *  description="Получение списка всех записей для одного каталога.",
     *  parameters={
     *      {
     *          "name"="catalogId",
     *          "dataType"="string",
     *          "required"=true,
     *          "description"="Id of the Catalog."
     *      }
     *  },
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
    public function getRecordsAction(Request $request)
    {
        $catalogId = $request->query->get('catalogId') ?: null;
        $page = $request->query->get('page') ?: 1;
        // By default this method returns all records
        $limit = $request->query->get('limit') ?: 0;

        // catalogId is required
        if (!$catalogId) {
            return new JsonResponse(null, 400);
        }

        // Check filters' format
        if (!is_numeric($page) || !is_numeric($limit)) {
            return new JsonResponse(null, 400);
        }

        $records = $this->getDoctrine()->getRepository(Record::REPOSITORY)
            ->findRecords($catalogId, $page, $limit);
        $records = $this->get('jms_serializer')->serialize($records, 'json',
            SerializationContext::create()->setGroups(array("getAllRecords"))
        );

        return new Response($records, 200);
    }

    /**
     * @ApiDoc(
     *  section="Catalog record",
     *  description="Получение детальной информации о записи.",
     *  requirements={
     *      {
     *          "name"="id",
     *          "dataType"="integer",
     *          "requirement"="\d+",
     *          "description"="Record id"
     *      }
     *  }
     * )
     */
    public function getRecordAction($id)
    {
        $record = $this->getDoctrine()->getRepository(Record::REPOSITORY)
            ->findOneBy(array('id' => $id));

        if (!$record) {
            return new JsonResponse(null, 404);
        }

        $record = $this->get('jms_serializer')->serialize($record, 'json',
            SerializationContext::create()->setGroups(array("getOneRecord", "getAllCatalogs"))
        );

        return new Response($record, 200);
    }

    /**
     * @ApiDoc(
     *  section="Catalog record",
     *  description="Создание новой записи.",
     *  input="Melodia\CatalogBundle\Form\Type\RecordFormType",
     *  output="Melodia\CatalogBundle\Entity\Record"
     * )
     */
    public function postRecordsAction(Request $request)
    {
        $record = new Record();

        $form = $this->createForm(new RecordFormType(), $record);
        $form->submit($request->request->all());

        if (!$form->isValid()) {
            return new JsonResponse($this->getErrorMessages($form), 400);
        }

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($record);
        $entityManager->flush();

        $record = $this->get('jms_serializer')->serialize($record, 'json',
            SerializationContext::create()->setGroups(array("getOneRecord", "getAllCatalogs"))
        );

        return new Response($record, 201);
    }

    /**
     * @ApiDoc(
     *  section="Catalog record",
     *  description="Редактирование записи.",
     *  requirements={
     *      {
     *          "name"="id",
     *          "dataType"="integer",
     *          "requirement"="\d+",
     *          "description"="Record id"
     *      }
     *  },
     *  input="Melodia\CatalogBundle\Form\Type\RecordFormType",
     *  output="Melodia\CatalogBundle\Entity\Record"
     * )
     */
    public function putRecordsAction($id, Request $request)
    {
        $record = $this->getDoctrine()->getRepository(Record::REPOSITORY)
            ->findOneBy(array('id' => $id));

        if (!$record) {
            return new JsonResponse(null, 404);
        }

        $oldOrder = $record->getOrder();

        $form = $this->createForm(new RecordFormType(), $record);
        $form->submit($request->request->all());

        if (!$form->isValid()) {
            return new JsonResponse($this->getErrorMessages($form), 400);
        }

        $entityManager = $this->getDoctrine()->getManager();

        $newOrder = $record->getOrder();
        foreach ($record->getCatalog()->getRecords() as $rec) {
            if ($newOrder > $oldOrder) {
                $recOrder = $rec->getOrder();
                if ($recOrder > $oldOrder &&  $recOrder <= $newOrder) {
                    $rec->setOrder($recOrder - 1);
                }
            } else if ($newOrder < $oldOrder) {
                $recOrder = $rec->getOrder();
                if ($recOrder < $oldOrder &&  $recOrder >= $newOrder) {
                    $rec->setOrder($recOrder + 1);
                }
            } else {
                // Don't change anything
                break;
            }

            $entityManager->persist($rec);
            $entityManager->flush();
        }

        // Restore new order value in case we've mangled it in the loop
        $record->setOrder($newOrder);

        $entityManager->persist($record);
        $entityManager->flush();

        $record = $this->get('jms_serializer')->serialize($record, 'json',
            SerializationContext::create()->setGroups(array("getOneRecord", "getAllCatalogs"))
        );

        return new Response($record, 200);
    }

    /**
     * @ApiDoc(
     *  section="Catalog record",
     *  description="Удаление записи.",
     *  requirements={
     *      {
     *          "name"="id",
     *          "dataType"="integer",
     *          "requirement"="\d+",
     *          "description"="Record id"
     *      }
     *  }
     * )
     */
    public function deleteRecordAction($id)
    {
        $record = $this->getDoctrine()->getRepository(Record::REPOSITORY)
            ->findOneBy(array('id' => $id));

        if (!$record) {
            return new JsonResponse(null, 404);
        }

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($record);
        $entityManager->flush();

        return new JsonResponse(null, 200);
    }
}
