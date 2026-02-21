<?php

namespace App\Form;

use App\Entity\BusinessPartner;
use App\Entity\Account;
use App\Entity\Currency;
use Dom\Text;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Validator\Constraints\NotBlank;

class AccountType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            ->add('businessPartner', EntityType::class, [
                'class' => BusinessPartner::class,
                'choice_label' => 'name',
                'disabled' => $options['is_edit'],
                'data' => $options['business_partner'] ?? null,
            ])
            ->add('currency', EntityType::class, [
                'class' => Currency::class,
                'disabled' => $options['is_edit'],
                'choice_label' => 'code',
            ])
            ->add('balance', NumberType::class, [
                'scale' => 4,
                'data' => 0,
                'disabled' => $options['is_edit'],
                'required' => true
            ])
            ->add('accountNumber', TextType::class, [
                'required' => true,
                'disabled' => $options['is_edit']
            ])
            ->add('isActive', CheckboxType::class, [
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Account::class,
            'business_partner' => null,
            'is_edit' => false,
        ]);
    }
}
