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

class RecordFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('catalog', 'entity', array(
                'class' => 'Melodia\CatalogBundle\Entity\Catalog',
                'property' => 'name',
                'empty_value' => '',
                'required' => true,
            ))
            ->add('data', 'text')
            ->add('keyword', 'text', array('required' => false))
            ->add('order', 'integer', array('required' => true))
            ->add('Add record', 'submit')
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Melodia\CatalogBundle\Entity\Record',
        ));
    }

    public function getName()
    {
        return '';
    }
}
