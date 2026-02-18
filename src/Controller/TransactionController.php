<?php

namespace App\Controller;

use App\Entity\Transaction;
use App\Form\TransactionType;
use App\Repository\BusinessPartnerRepository;
use App\Repository\TransactionRepository;
use App\Service\PayoutManager;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/transaction')]
class TransactionController extends AbstractController
{
    #[Route('/', name: 'app_transaction_list', methods: ['GET'])]
    public function list(
        Request $request,
        TransactionRepository $transactionRepository,
        BusinessPartnerRepository $businessPartnerRepository
    ): Response {
        $businessPartnerId = $request->query->get('businessPartnerId');

        $businessPartner = $businessPartnerId ? $businessPartnerRepository->find($businessPartnerId) : null;

        return $this->render('transaction/list.html.twig', [
            'businessPartner' => $businessPartner,
            'transactions' => $businessPartner
                ? $transactionRepository->findByBusinessPartner($businessPartner)
                : $transactionRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_transaction_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $transaction = new Transaction();
        $form = $this->createForm(TransactionType::class, $transaction);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($transaction);
            $entityManager->flush();

            return $this->redirectToRoute('app_transaction_list', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('transaction/new.html.twig', [
            'transaction' => $transaction,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_transaction_show', methods: ['GET'])]
    public function show(Transaction $transaction): Response
    {
        return $this->render('transaction/show.html.twig', [
            'transaction' => $transaction,
        ]);
    }

    #[Route('/{id}/execute', name: 'app_transaction_execute', methods: ['GET'])]
    public function execute(Request $request, Transaction $transaction, PayoutManager $payoutManager): Response
    {
        try {
            $payoutManager->execute($transaction);
        } catch (Exception $exception) {
            $request->getSession()->getFlashBag()->add('danger', $exception->getMessage());

            return $this->redirectToRoute('app_transaction_list', [], Response::HTTP_SEE_OTHER);
        }

        $request->getSession()->getFlashBag()->add('success', 'Transaction successfully executed.');

        return $this->redirectToRoute('app_transaction_list', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/edit', name: 'app_transaction_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Transaction $transaction, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(TransactionType::class, $transaction);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_transaction_list', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('transaction/edit.html.twig', [
            'transaction' => $transaction,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_transaction_delete', methods: ['POST'])]
    public function delete(Request $request, Transaction $transaction, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$transaction->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($transaction);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_transaction_list', [], Response::HTTP_SEE_OTHER);
    }
}
