<?php

namespace App\Controller;

use App\Entity\BusinessPartner;
use App\Form\BusinessPartnerType;
use App\Repository\BusinessPartnerRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/business_partner')]
class BusinessPartnerController extends AbstractController
{
    #[Route('/', name: 'app_business_partner_list', methods: ['GET'])]
    public function list(BusinessPartnerRepository $businessPartnerRepository): Response
    {
        return $this->render('business_partner/list.html.twig', [
            'businessPartners' => $businessPartnerRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_business_partner_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $businessPartner = new BusinessPartner();
        $form = $this->createForm(BusinessPartnerType::class, $businessPartner);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($businessPartner);
            $entityManager->flush();

            return $this->redirectToRoute('app_business_partner_list', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('business_partner/new.html.twig', [
            'businessPartner' => $businessPartner,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_business_partner_show', methods: ['GET'])]
    public function show(BusinessPartner $businessPartner): Response
    {
        return $this->render('business_partner/show.html.twig', [
            'businessPartner' => $businessPartner,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_business_partner_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        BusinessPartner $businessPartner,
        EntityManagerInterface $entityManager
    ): Response {
        $form = $this->createForm(BusinessPartnerType::class, $businessPartner);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_business_partner_list', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('business_partner/edit.html.twig', [
            'businessPartner' => $businessPartner,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_business_partner_delete', methods: ['POST'])]
    public function delete(
        Request $request,
        BusinessPartner $businessPartner,
        EntityManagerInterface $entityManager
    ): Response {
        if ($this->isCsrfTokenValid('delete'.$businessPartner->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($businessPartner);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_business_partner_list', [], Response::HTTP_SEE_OTHER);
    }
}
