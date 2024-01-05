<?php

namespace App\Controller;

use App\Entity\AuditsData;
use App\Form\AuditFormType;
use Symfony\Component\Uid\Uuid;
use App\Service\PageScraperService;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\PageSpeedInsightsService;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AudytorController extends AbstractController
{
    private $pageScraperService;
    private $pageSpeedInsightsService;
    private $em;

    public function __construct(PageScraperService $pageScraperService, PageSpeedInsightsService $pageSpeedInsightsService, EntityManagerInterface $em)
    {
        $this->pageScraperService = $pageScraperService;
        $this->pageSpeedInsightsService = $pageSpeedInsightsService;
        $this->em = $em;
    }
    

    #[Route('/', name: 'app_main')]
    public function index(): Response
    {
        return $this->render('audytor/index.html.twig', [
            'controller_name' => 'AudytorController',
        ]);
    }
    
    
    #[Route('/audytor', name: 'app_audytor')]
    public function audytor(Request $request, AudytorController $audytorController): Response
    {
        $form = $this->createForm(AuditFormType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $dataForm = $form->getData();
            $url = $dataForm['url'];

            $parsedUrl = parse_url($url);
            $domain = $parsedUrl['host'] ?? '';

            $uid = Uuid::v4()->toRfc4122();
            
            return $this->redirectToRoute('app_audyt', ['uid' => $uid, 'domain' => $domain]);
        }

        return $this->render('audytor/audytor.html.twig', [
            'form' => $form->createView(),
        ]);
    }


    #[Route('/audyt/{domain}/{uid}', name: 'app_audyt', requirements: ['domain' => '[a-zA-Z./-0-9:]+'])]
public function audyt($domain, $uid)
{
    try {
        $data = $this->pageScraperService->scrapePageContent($domain);
        $dataPageSpeed = $this->pageSpeedInsightsService->getPageSpeedInsights('http://'.$domain);
        
        return $this->render('audytor/audyt.html.twig', [
            'data' => $data,
            'dataPageSpeed' => $dataPageSpeed,
            'uid' => $uid,
        ]);
    } catch (\Exception $e) {
        $this->addFlash('error', $e->getMessage());
        return $this->redirectToRoute('app_audytor');
    }
}
    
    #[Route('/save/{domain}/{uid}', name: 'app_save', requirements: ['domain' => '[a-zA-Z./-0-9:]+'])]
    public function auditSave(Request $request, Security $security, $domain, $uid): Response
    {
        $user = $security->getUser();
        $data = $request->get('data');
        $dataPageSpeed = $request->get('dataPageSpeed');

        $auditsData = new AuditsData();
        $auditsData->setUID(Uuid::fromString($uid));
        $auditsData->setDomain($domain);
        $auditsData->setData(['data' => $data, 'dataPageSpeed' => $dataPageSpeed]);
        $auditsData->setUser($user);

        $this->em->persist($auditsData);
        $this->em->flush();

        return new JsonResponse(['status' => 'success']);
    }
}
