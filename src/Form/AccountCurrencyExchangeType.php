<?php

namespace App\Form;

use App\DTO\AccountCurrencyExchangeDTO;
use App\Entity\Account;
use App\Entity\BusinessPartner;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\FormError;

class AccountCurrencyExchangeType extends AbstractType
{
    public function __construct(private EntityManagerInterface $em) {}

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('businessPartner', EntityType::class, [
                'class' => BusinessPartner::class,
                'choice_label' => 'name',
                'required' => true,
                'placeholder' => 'Select Business Partner',
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Business Partner is required']),
                ],
                'attr' => [
                    'id' => 'partner-select',
                ]
            ])
            ->add('fromAccount', EntityType::class, [
                'class' => Account::class,
                'choice_label' => fn(Account $a) => sprintf('%s (%s)', $a->getName(), $a->getCurrencyCode()),
                'query_builder' => function(EntityRepository $er) {
                    // Initially load no accounts - they'll be filtered via listener
                    return $er->createQueryBuilder('a')->where('1=0');
                },
                'placeholder' => 'Select an account to sell from',
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank(['message' => 'From Account is required']),
                ]
            ])
            ->add('toAccount', EntityType::class, [
                'class' => Account::class,
                'choice_label' => fn(Account $a) => sprintf('%s (%s)', $a->getName(), $a->getCurrencyCode()),
                'query_builder' => function(EntityRepository $er) {
                    // Initially load no accounts - they'll be filtered via listener
                    return $er->createQueryBuilder('a')->where('1=0');
                },
                'placeholder' => 'Select account to buy to',
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank(['message' => 'To Account is required']),
                ]
            ])
            ->add('amount', NumberType::class, [
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Amount is required']),
                    new Assert\Positive(['message' => 'Amount must be greater than 0']),
                ]
            ])
            ->addEventListener(FormEvents::POST_SUBMIT, function(FormEvent $event) {
                $data = $event->getData();
                $form = $event->getForm();

                // Validate that fromAccount and toAccount are different
                if ($data->fromAccount && $data->toAccount) {
                    if ($data->fromAccount->getId() === $data->toAccount->getId()) {
                        $form->addError(new FormError('From Account and To Account must be different.'));
                    }

                    if ($data->fromAccount->getCurrency()->getId() === $data->toAccount->getCurrency()->getId()) {
                        $form->addError(new FormError('From Account and To Account must have different currencies for an exchange.'));
                    }

                    if ($data->fromAccount->getBusinessPartner()->getId() !== $data->toAccount->getBusinessPartner()->getId()) {
                        $form->addError(new FormError('Both accounts must belong to the same business partner.'));
                    }

                    if ($data->fromAccount->getBalance() < $data->amount) {
                        $form->addError(new FormError('Insufficient balance for the exchange.'));
                    }
                }
            });

        $formModifier = function ($form, ?BusinessPartner $partner) {
            $accounts = $partner
                ? $this->em->getRepository(Account::class)->findBy(['businessPartner' => $partner])
                : [];

            $form->add('fromAccount', EntityType::class, [
                'class' => Account::class,
                'choices' => $accounts,
                'placeholder' => 'Select an account to sell from',
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank(['message' => 'From Account is required']),
                ],
                'choice_label' => fn(Account $a) => sprintf('%s (%s)', $a->getName(), $a->getCurrencyCode()),
            ]);

            $form->add('toAccount', EntityType::class, [
                'class' => Account::class,
                'choices' => $accounts,
                'placeholder' => 'Select an account to buy to',
                'required' => true,
                'constraints' => [
                    new Assert\NotBlank(['message' => 'To Account is required']),
                ],
                'disabled' => !$partner,
                'choice_label' => fn(Account $a) => sprintf('%s (%s)', $a->getName(), $a->getCurrencyCode()),
            ]);
        };

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function(FormEvent $event) use ($formModifier) {
            $data = $event->getData();
            $partnerId = $data['businessPartner'] ?? null;
            $partner = $partnerId
                ? $this->em->getRepository(BusinessPartner::class)->find($partnerId)
                : null;

            $formModifier($event->getForm(), $partner);
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => AccountCurrencyExchangeDTO::class
        ]);
    }
}
