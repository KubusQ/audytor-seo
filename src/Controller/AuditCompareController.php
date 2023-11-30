<?php

namespace App\Controller;

use App\Entity\AuditsData;
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
}
