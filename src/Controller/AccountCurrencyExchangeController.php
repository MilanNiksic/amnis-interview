<?php

namespace App\Controller;

use App\Form\AccountCurrencyExchangeType;
use App\Service\ExchangeManager;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/account-currency-exchange')]
class AccountCurrencyExchangeController extends AbstractController
{
    #[Route('/new', name: 'app_account_currency_exchange_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
        ExchangeManager $exchangeManager
    ): Response {
        $form = $this->createForm(AccountCurrencyExchangeType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $fromAccount = $form->get('fromAccount')->getData();
                $toAccount = $form->get('toAccount')->getData();
                $amount = $form->get('amount')->getData();

                $exchangeManager->executeExchange($fromAccount, $toAccount, $amount);

                $this->addFlash('success', 'Exchange executed successfully!');
                return $this->redirectToRoute('app_business_partner_list', [], Response::HTTP_SEE_OTHER);
            } catch (\InvalidArgumentException $e) {
                $this->addFlash('danger', 'Validation failed: ' . $e->getMessage());
            } catch (Exception $e) {
                $this->addFlash('danger', 'Exchange failed: ' . $e->getMessage());
            }
        }

        return $this->render('account_currency_exchange/new.html.twig', [
            'form' => $form
        ]);
    }
}
