<?php

namespace App\Controller;

use App\Service\PageScraperService;
use App\Service\PageSpeedInsightsService;
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
    public function scrapePage()
    {
        //$url = 'https://bandola.com.pl/pss/';
        //$url = 'https://swiatloscwiekuista.pl/';
        $url = 'https://djygo.pl/';

        $data = $this->pageScraperService->scrapePageContent($url);
        $dataPageSpeed = $this->pageSpeedInsightsService->getPageSpeedInsights($url);
        return $this->render('audytor/audyt.html.twig', [
            'data' => $data,
            'dataPageSpeed' => $dataPageSpeed,
        ]);
        
    }
}
