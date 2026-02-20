<?php

namespace App\Form;

use App\Entity\Account;
use App\Entity\BusinessPartner;
use App\Entity\Transaction;
use App\Enums\TransactionTypeEnum;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TransactionType extends AbstractType
{
    public function __construct(private EntityManagerInterface $em) {}

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            ->add('businessPartner', EntityType::class, [
                'class' => BusinessPartner::class,
                'choice_label' => 'name',
                'placeholder' => 'Select any business partner',
                'required' => true,
            ])
            ->add('account', EntityType::class, [
                'class' => Account::class,
                'choice_label' => fn(Account $account) =>
                    sprintf('%s (%s)', $account->getName(), $account->getCurrency()->getCode()),
                'query_builder' => function(EntityRepository $er) {
                    // Initially load no accounts - they'll be filtered via listener
                    return $er->createQueryBuilder('a')->where('1=0');
                },
                'placeholder' => 'Select an account',
                'required' => true,
            ])
            ->add('amount')
            ->add('date', null, [
                'widget' => 'single_text',
            ])
            ->add('type', EnumType::class, [
                "class" => TransactionTypeEnum::class,
                'choice_filter' => fn($choice) =>
                    !in_array($choice, [TransactionTypeEnum::EXCHANGE])
            ])
            ->add('country')
            ->add('iban')
            ->addEventListener(FormEvents::POST_SUBMIT, function(FormEvent $event) {
                $data = $event->getData();
                $form = $event->getForm();

                // Validate that the selected account belongs to the business partner
                if ($data->getAccount() && $data->getBusinessPartner()) {
                    if ($data->getAccount()->getBusinessPartner()->getId() !== $data->getBusinessPartner()->getId()) {
                        $form->get('account')->addError(
                            new \Symfony\Component\Form\FormError('The selected account must belong to the chosen business partner.')
                        );
                    }
                }
            });

            $formModifier = function ($form, ?BusinessPartner $partner) {
                $accounts = $partner
                    ? $this->em->getRepository(Account::class)->findBy(['businessPartner' => $partner])
                    : [];

                $form->add('account', EntityType::class, [
                    'class' => Account::class,
                    'choices' => $accounts,
                    'placeholder' => 'Select account',
                    'disabled' => !$partner,
                    'choice_label' => fn(Account $a) => sprintf('%s (%s)', $a->getName(), $a->getCurrencyCode()),
                ]);
            };

            // When editing
            $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($formModifier, $options) {
                $tx = $event->getData();
                if (!$tx instanceof Transaction || !$options['is_edit']) return;

                $formModifier($event->getForm(), $tx->getBusinessPartner());
            });

            $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) use ($formModifier) {
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
            'data_class' => Transaction::class,
            'is_edit' => false,
        ]);
    }
}
