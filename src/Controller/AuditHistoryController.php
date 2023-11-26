<?php

namespace App\Controller;

use App\Entity\AuditsData;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AuditHistoryController extends AbstractController
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    #[Route('/history/{domain}/{uid}', name: 'app_history', requirements: ['domain' => '[a-zA-Z./-0-9:]+'])]
    public function history($domain, $uid)
    {
        $auditsData = $this->em->getRepository(AuditsData::class)->findOneBy(['domain' => $domain, 'uid' => $uid]);

        if (!$auditsData) {
            throw $this->createNotFoundException('Brak danych dla podanego audytu.');
        }
        return $this->render('history/history.html.twig', [
            'auditsData' => $auditsData,
        ]);
    }
}