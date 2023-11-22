<?php

namespace App\Controller;

use App\Form\AuditFormType;
use App\Service\PageScraperService;
use App\Service\PageSpeedInsightsService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AudytorController extends AbstractController
{
    private $pageScraperService;
    private $pageSpeedInsightsService;

    public function __construct(PageScraperService $pageScraperService, PageSpeedInsightsService $pageSpeedInsightsService)
    {
        $this->pageScraperService = $pageScraperService;
        $this->pageSpeedInsightsService = $pageSpeedInsightsService;
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
            
            return $this->redirectToRoute('app_audyt', ['id' => 10, 'domain' => $url]);
        }

        return $this->render('audytor/audytor.html.twig', [
            'form' => $form->createView(),
        ]);
    }


    #[Route('/audyt/{domain}/{id}', name: 'app_audyt', requirements: ['id' => '\d+', 'domain' => '[a-zA-Z./-0-9:]+'])]
    public function audyt(int $id, string $domain)
    {
        $data = $this->pageScraperService->scrapePageContent($domain);
        $dataPageSpeed = $this->pageSpeedInsightsService->getPageSpeedInsights($domain);
        
        return $this->render('audytor/audyt.html.twig', [
            'data' => $data,
            'dataPageSpeed' => $dataPageSpeed,
        ]);
        
    }
}
