<?php

namespace App\Controller;

use App\Service\PageScraperService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AudytorController extends AbstractController
{
    private $pageScraperService;

    public function __construct(PageScraperService $pageScraperService)
    {
        $this->pageScraperService = $pageScraperService;
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
        $url = 'https://bandola.com.pl/pss/';
        //$url = 'https://swiatloscwiekuista.pl/';

        $data = $this->pageScraperService->scrapePageContent($url);

        return $this->render('audytor/audyt.html.twig', ['data' => $data]);
    }
}
