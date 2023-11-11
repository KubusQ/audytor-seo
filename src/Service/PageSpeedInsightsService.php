<?php

namespace App\Service;

use Symfony\Component\HttpClient\HttpClient;

class PageSpeedInsightsService
{
    private $apiKey = 'AIzaSyDUY3TixNoya4_WpVTv4KqqRPhVmDeTZTw';

    public function getPageSpeedInsights(string $url): array
    {
        $client = HttpClient::create();

        $mobileResponse = $client->request('GET', "https://www.googleapis.com/pagespeedonline/v5/runPagespeed?url=$url&strategy=mobile&key={$this->apiKey}");
        $desktopResponse = $client->request('GET', "https://www.googleapis.com/pagespeedonline/v5/runPagespeed?url=$url&strategy=desktop&key={$this->apiKey}");

        $mobileData = $mobileResponse->toArray();
        $desktopData = $desktopResponse->toArray();

        return [
            'mobile' => $this->getPerformanceScore($mobileData),
            'desktop' => $this->getPerformanceScore($desktopData),
            'metrics' => [
                'mobile' => $this->getMetrics($mobileData),
                'desktop' => $this->getMetrics($desktopData),
            ],
        ];
    }

    private function getPerformanceScore(array $data)
    {
        return $data['lighthouseResult']['categories']['performance']['score']*100;
    }

    private function getMetrics(array $data)
    {
        return [
            'firstContentfulPaint' => $data['lighthouseResult']['audits']['first-contentful-paint']['displayValue'],
            'speedIndex' => $data['lighthouseResult']['audits']['speed-index']['displayValue'],
            'timeToInteractive' => $data['lighthouseResult']['audits']['interactive']['displayValue'],
            'firstMeaningfulPaint' => $data['lighthouseResult']['audits']['first-meaningful-paint']['displayValue'],
        ];
    }
}