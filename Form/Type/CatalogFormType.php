<?php

/*
 * This file is part of the Melodia Catalog Bundle
 *
 * (c) Alexey Ryzhkov <alioch@yandex.ru>
 */

namespace Melodia\CatalogBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CatalogFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('id', 'text', array('label' => 'ID'))
            ->add('name', 'text', array('label' => 'Catalog name'))
            ->add('Add catalog', 'submit')
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Melodia\CatalogBundle\Entity\Catalog',
        ));
    }

    public function getName()
    {
        return '';
    }
}