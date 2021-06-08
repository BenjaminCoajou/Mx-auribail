<?php

namespace App\Form;

use App\Entity\Training;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TrainingType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('trainingDate', null, [
                'label' => 'Date d\'entrainement'
            ])
            ->add('slot', NumberType::class, [
                'label' => 'Places'
            ])
            ->add('info', TextType::class, [
                'label' => 'Informations'
            ])
            ->add('openingRegistrationDate', DateType::class, [
                'label' => 'Date d\'enregistrement'
            ])
            ->add('adult', null, [
                'attr' => array('checked' => 'checked'),
                'label' => 'Adulte'
            ])
            ->add('Envoyer', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Training::class,
        ]);
    }
}
