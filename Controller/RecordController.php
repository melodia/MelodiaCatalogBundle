<?php

/*
 * This file is part of the Melodia Catalog Bundle
 *
 * (c) Alexey Ryzhkov <alioch@yandex.ru>
 */

namespace Melodia\CatalogBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Melodia\CatalogBundle\Entity\Record;
use Melodia\CatalogBundle\Form\Type\RecordFormType;

class RecordController extends Controller
{
    public function addAction(Request $request)
    {
        $record = new Record();
        $form = $this->createForm(new RecordFormType(), $record);

        $form->handleRequest($request);

        if ($form->isValid()) {
//            if (!$record->getOrder()) {
//                $record->setOrder(count($record->getCatalog()->getRecords()));
//            }

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($record);
            $entityManager->flush();

            return new Response('The record was added.');
        }

        return $this->render('MelodiaCatalogBundle:Record:add.html.twig',
            array('form' => $form->createView())
        );
    }
}
