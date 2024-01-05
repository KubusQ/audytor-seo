<?php

namespace App\Controller;

use App\Entity\AuditsData;
use Mpdf\Mpdf;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
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

    #[Route('/history/{domain}/{uid}', name: 'app_history_audit', requirements: ['domain' => '[a-zA-Z./-0-9:]+'])]
    public function historyAudit($domain, $uid)
    {
        $auditsData = $this->em->getRepository(AuditsData::class)->findOneBy(['domain' => $domain, 'uid' => $uid]);

        if (!$auditsData) {
            throw $this->createNotFoundException('Brak danych dla podanego audytu.');
        }
        return $this->render('history/audithistory.html.twig', [
            'auditsData' => $auditsData,
        ]);
    }

    #[Route('/history/{domain}/{uid}/pdf', name: 'app_history_audit_pdf', requirements: ['domain' => '[a-zA-Z./-0-9:]+'])]
    public function historyAuditPdf($domain, $uid)
    {
        $auditsData = $this->em->getRepository(AuditsData::class)->findOneBy(['domain' => $domain, 'uid' => $uid]);

        $filename = $uid . '.pdf';
        $pdf = new mPDF();

        $pdf->AddPage('A4', 'portrait');

        $pdf->SetTitle('Audit Report');

        $pdf->WriteHTML(
            $this->render('history/history-pdf.html.twig', [
                'auditsData' => $auditsData,
            ])
        );

        $pdf->Output($filename, 'D');

        $response = new Response($pdf->Output($filename, 'D'), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);

        return $response;
    }   
    
    #[Route('/history', name: 'app_history')]
    public function history(Security $security)
    {
        $user = $security->getUser();
        
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $audits = $this->em->getRepository(AuditsData::class)->findBy(['user' => $user]);
        
        return $this->render('history/history.html.twig', [
            'audits' => $audits
        ]);
    }
}