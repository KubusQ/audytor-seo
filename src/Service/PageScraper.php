<?php

namespace App\Service;

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\DomCrawler\Crawler;

class PageScraperService
{
    public function scrapePageContent($url)
    {
        $client = HttpClient::create();
        $response = $client->request('GET', $url);
        $statusCode = $response->getStatusCode();

        if ($statusCode === 200) {
            $content = $response->getContent();
            $crawler = new Crawler($content);

            // get title, description, keywords
            $title = $crawler->filter('title')->count() > 0 
                ? $crawler->filter('title')->text() 
                : 'brak';
            $metaDescription = $crawler->filter('meta[name="description"]')->count() > 0
                ? $crawler->filter('meta[name="description"]')->attr('content')
                : 'brak';
            $metaKeywords = $crawler->filter('meta[name="keywords"]')->count() > 0
                ? $crawler->filter('meta[name="keywords"]')->attr('content')
                : 'brak';
            $favicon = $crawler->filter('link[rel="icon"]')->count() > 0
                ? 'jest'
                : 'brak';

            // get domain name
            $parsedUrl = parse_url($url);
            $domainName = isset($parsedUrl['host']) ? $parsedUrl['host'] : '';
            
            // get headers and counts it
            $headers = $crawler->filter('h1, h2, h3, h4, h5, h6')->each(function (Crawler $node) {
                return [
                    'tag' => $node->getNode(0)->tagName,
                    'text' => $node->text(),
                ];
            });

            $headerCounts = [];
            foreach ($headers as $header) {
                $tag = $header['tag'];
                if (isset($headerCounts[$tag])) {
                    $headerCounts[$tag]++;
                } else {
                    $headerCounts[$tag] = 1;
                }
            }
            // check that headers are unique
            $headersUnique = $this->checkHeadersAreUnique($headers);
            // check alt for images
            $missingAltImages = $this->checkAltAttributes($content);
            return [
                'title' => $title,
                'description' => $metaDescription,
                'keywords' => $metaKeywords,
                'domain' => $domainName,
                'favicon' => $favicon,
                'headers' => $headers,
                'headerCounts' => $headerCounts,
                'headersUnique' => $headersUnique,
                'missingAltImages' => $missingAltImages,
            ];
        } else {
            return [
                'title' => 'Błąd: Nie można pobrać zawartości strony',
                'description' => '',
                'keywords' => '',
                'domain' => '',
                'favicon' => '',
                'headers' => [],
                'headerCounts' => [],
                'headersUnique' => '',
                'missingAltImages' => [],
            ];
        }
    }
private function checkAltAttributes($content)
{
    $crawler = new Crawler($content);

    $images = $crawler->filter('img');

    $missingAltImages = [];

    foreach ($images as $image) {
        $alt = $image->getAttribute('alt');

        if (empty($alt)) {
            $src = $image->getAttribute('src');
            $missingAltImages[] = $src;
        }
    }

    return $missingAltImages;
}
private function checkHeadersAreUnique($headers)
{
    $headerTexts = array_column($headers, 'text');
    $uniqueTexts = array_unique($headerTexts);

    return count($headerTexts) === count($uniqueTexts);
}
}