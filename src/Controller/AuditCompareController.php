<?php

namespace App\Controller;

use App\Entity\AuditsData;
use Mpdf\Mpdf;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AuditCompareController extends AbstractController
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }
    
    #[Route('/compare/{uid1}/{uid2}', name: 'app_compare')]
    public function compare($uid1, $uid2): Response
    {
        $audit1 = $this->em->getRepository(AuditsData::class)->findOneBy(['uid' => $uid1]);
        $audit2 = $this->em->getRepository(AuditsData::class)->findOneBy(['uid' => $uid2]);
        
        return $this->render('audit_compare/compare.html.twig', [
            'audit1Data' => $audit1,
            'audit2Data' => $audit2,
        ]);
    }

    #[Route('/compare/{uid1}/{uid2}/pdf', name: 'app_compare_audit_pdf')]
    public function compareAuditPdf($uid1, $uid2)
    {
        $audit1 = $this->em->getRepository(AuditsData::class)->findOneBy(['uid' => $uid1]);
        $audit2 = $this->em->getRepository(AuditsData::class)->findOneBy(['uid' => $uid2]);

        $filename = $uid1 . "+" . $uid2 . '.pdf';
        $pdf = new mPDF();

        $pdf->AddPage('A4', 'portrait');

        $pdf->SetTitle('Porównanie audytów');

        $pdf->WriteHTML(
            $this->render('audit_compare/compare-pdf.html.twig', [
                'audit1Data' => $audit1,
                'audit2Data' => $audit2,
            ])
        );

        $pdf->Output($filename, 'D');

        $response = new Response($pdf->Output($filename, 'D'), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);

        return $response;
    }   
}
