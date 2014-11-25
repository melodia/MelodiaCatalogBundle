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
use Melodia\CatalogBundle\Entity\Catalog;
use Melodia\CatalogBundle\Form\Type\CatalogFormType;

class CatalogController extends Controller
{
    public function addAction(Request $request)
    {
        $catalog = new Catalog();
        $form = $this->createForm(new CatalogFormType(), $catalog);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($catalog);
            $entityManager->flush();

            return new Response('The catalog was added.');
        }

        return $this->render('MelodiaCatalogBundle:Catalog:add.html.twig',
            array('form' => $form->createView())
        );
    }
}
