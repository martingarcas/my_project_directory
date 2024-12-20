<?php

namespace App\Form;

use App\Entity\Pregunta;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PreguntaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('enunciado')
            ->add('fecha_inicio', null, [
                'widget' => 'single_text',
            ])
            ->add('fecha_fin', null, [
                'widget' => 'single_text',
            ])
            ->add('respuesta1')
            ->add('respuesta2')
            ->add('respuesta3')
            ->add('respuesta4')
            ->add('respuesta_correcta')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Pregunta::class,
        ]);
    }
}
