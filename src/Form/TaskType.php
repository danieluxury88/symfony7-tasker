<?php

namespace App\Form;

use App\Entity\Task;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TaskType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', null, [
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Enter task title...',
                    'required' => true,
                    'minlength' => 3,
                    'maxlength' => 255,
                ],
                'required' => true,
            ])
            ->add('description', null, [
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 4,
                    'placeholder' => 'Enter task description...',
                    'maxlength' => 1000,
                ],
                'required' => false,
            ])
            ->add('isCompleted', null, [
                'attr' => [
                    'class' => 'form-check-input',
                ],
                'required' => false,
            ])
            ->add('createdAt', null, [
                'widget' => 'single_text',
                'attr' => [
                    'class' => 'form-control',
                    'type' => 'datetime-local',
                    'required' => true,
                    'max' => (new \DateTime('+1 minute'))->format('Y-m-d\TH:i'),
                ],
                'required' => true,
                'data' => new \DateTimeImmutable(), // Default to current date/time
            ])
            ->add('createdBy', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'email',
                'attr' => [
                    'class' => 'form-select',
                ],
                'required' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Task::class,
        ]);
    }
}
