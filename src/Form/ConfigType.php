<?php

namespace Gupalo\ConfigBundle\Form;

use Gupalo\ConfigBundle\Entity\Config;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ConfigType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'config.col.name',
            ])
            ->add('value', TextareaType::class, [
                'label' => 'config.col.value',
                'attr' => [
                    'rows' => 5,
                    'style' => 'font-family: Consolas, "Courier New", monospaced; font-size: 13px',
                    'wrap' => 'off',
                ],
            ])
            ->add('save', SubmitType::class, ['label' => 'config.btn.save']);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Config::class,
        ]);
    }
}
