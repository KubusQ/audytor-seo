<?php

namespace App\Service;

use Symfony\Component\HttpClient\HttpClient;

class PageSpeedInsightsService
{
    private $apiKey = 'AIzaSyDUY3TixNoya4_WpVTv4KqqRPhVmDeTZTw';

    public function getPageSpeedInsights($url)
    {
        $apiKey = $this->apiKey;

        $mobileUrl = "https://www.googleapis.com/pagespeedonline/v5/runPagespeed?url=$url&strategy=mobile&key=$apiKey";
        $desktopUrl = "https://www.googleapis.com/pagespeedonline/v5/runPagespeed?url=$url&strategy=desktop&key=$apiKey";

        $client = HttpClient::create();

        $mobileResponse = $client->request('GET', $mobileUrl);
        $desktopResponse = $client->request('GET', $desktopUrl);

        $mobileData = $mobileResponse->toArray();
        $desktopData = $desktopResponse->toArray();

        $mobilePerformance = $mobileData['lighthouseResult']['categories']['performance']['score'];
        $desktopPerformance = $desktopData['lighthouseResult']['categories']['performance']['score'];

        $mobilePerformancePercentage = $mobilePerformance*100;
        $desktopPerformancePercentage = $desktopPerformance*100;

        $mobileMetrics = $mobileData['lighthouseResult']['audits'];
        $desktopMetrics = $desktopData['lighthouseResult']['audits'];

        $metrics = [
            'mobile' => [
                'firstContentfulPaint' => $mobileMetrics['first-contentful-paint']['displayValue'],
                'speedIndex' => $mobileMetrics['speed-index']['displayValue'],
                'timeToInteractive' => $mobileMetrics['interactive']['displayValue'],
                'firstMeaningfulPaint' => $mobileMetrics['first-meaningful-paint']['displayValue'],
            ],
            'desktop' => [
                'firstContentfulPaint' => $desktopMetrics['first-contentful-paint']['displayValue'],
                'speedIndex' => $desktopMetrics['speed-index']['displayValue'],
                'timeToInteractive' => $desktopMetrics['interactive']['displayValue'],
                'firstMeaningfulPaint' => $desktopMetrics['first-meaningful-paint']['displayValue'],
            ],
        ];

        return [
            'mobile' => $mobilePerformancePercentage,
            'desktop' => $desktopPerformancePercentage,
            'metrics' => $metrics,
        ];
    }
}