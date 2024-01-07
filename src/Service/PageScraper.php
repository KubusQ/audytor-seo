<?php

namespace App\Service;

use Exception;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpClient\HttpClient;

use function PHPUnit\Framework\isEmpty;

class PageScraperService
{
    public function scrapePageContent($domainName)
    {
        $sslCertificate = $this->checkSslCertificate($domainName);
        if ($sslCertificate) {
            $url = "https://" . $domainName;
        } else {
            $url = "http://" . $domainName;
        }
        $url = rtrim($url, "/");
        $client = HttpClient::create();
        $response = $client->request("GET", $url);
        $statusCode = $response->getStatusCode();

        if ($statusCode === 200) {
            $content = $response->getContent();
            $crawler = new Crawler($content);

            $title =
                $crawler->filter("title")->count() > 0
                    ? $crawler->filter("title")->text()
                    : "brak";
            $metaDescription =
                $crawler->filter('meta[name="description"]')->count() > 0
                    ? $crawler
                        ->filter('meta[name="description"]')
                        ->attr("content")
                    : "brak";
            $metaKeywords =
                $crawler->filter('meta[name="keywords"]')->count() > 0
                    ? $crawler->filter('meta[name="keywords"]')->attr("content")
                    : "brak";
            $favicon =
                $crawler->filter('link[rel="icon"]')->count() > 0
                    ? "jest"
                    : "brak";
            $viewport =
                $crawler->filter('meta[name="viewport"]')->count() > 0
                    ? "jest"
                    : "brak";
            $robots =
                $crawler->filter('meta[name="robots"]')->count() > 0
                    ? "jest => " .
                        $crawler->filter('meta[name="robots"]')->attr("content")
                    : "brak";
            $lang = empty($crawler->filter("html")->attr("lang"))
                ? "brak"
                : $crawler->filter("html")->attr("lang");
            $charset =
                $crawler->filter("meta[charset]")->count() > 0
                    ? $crawler->filter("meta[charset]")->attr("charset")
                    : "brak";
            $RDFa =
                $crawler
                    ->filter(
                        "[about], [property], [typeof], [vocab], [datatype]"
                    )
                    ->count() > 0
                    ? "jest"
                    : "brak";
            $microdata =
                $crawler->filter('script[type="application/ld+json"]') ||
                $crawler
                    ->filter("[itemscope], [itemtype], [itemprop]")
                    ->count() > 0
                    ? "jest"
                    : "brak";
            $flash =
                $crawler
                    ->filter(
                        'embed[type="application/x-shockwave-flash"], object[type="application/x-shockwave-flash"]'
                    )
                    ->count() > 0
                    ? "jest"
                    : "brak";
            $frame =
                $crawler->filter("frame, iframe")->count() > 0
                    ? "jest"
                    : "brak";
            $inlineCSS =
                $crawler->filter("*[style]")->count() > 0 ? "jest" : "brak";

            $ipAddress = gethostbyname($domainName);
            $headers = $this->getHeaders($content);
            if (!empty($headers)) {
                $headerCounts = $this->countHeaders($headers);
                $headersUnique = $this->checkHeadersAreUnique($headers);
            } else {
                $headers = false;
                $headerCounts = false;
                $headersUnique = false;
            }
            $missingAltImages = $this->checkAltAttributes($content);
            $httpsRedirection = $this->checkHttpsRedirection($domainName);
            $wwwRedirection = $this->checkWwwRedirection($domainName);
            $indexesRedirections = $this->checkIndexsRedirections($url);
            $robotsTxt = $this->checkRobots($url);
            $ipRedirection = $this->checkIPRedirection($ipAddress);
            $sitemap = $this->checkSitemap($url);
            $internalLinks = $this->getInLinks($url, $content);
            $maxLenghtInlinks = max(array_map("strlen", $internalLinks));
            $friendlyLinks = $this->checkFriendlyLinks($url, $content);
            $outLinks = $this->getOutLinks($url, $content);
            $googleAnalytics = $this->checkGoogleAnalytics($content);

            $auditDateTime = new \DateTime();
            $auditDateTime->setTimezone(new \DateTimeZone("Europe/Warsaw"));
            $auditDateTime = $auditDateTime->format("d-m-Y H:i:s");

            return [
                "title" => $title,
                "description" => $metaDescription,
                "keywords" => $metaKeywords,
                "domain" => $domainName,
                "viewport" => $viewport,
                "favicon" => $favicon,
                "headers" => $headers,
                "headerCounts" => $headerCounts,
                "headersUnique" => $headersUnique,
                "missingAltImages" => $missingAltImages,
                "httpsRedirection" => $httpsRedirection,
                "wwwRedirection" => $wwwRedirection,
                "indexesRedirection" => $indexesRedirections,
                "robotsTxt" => $robotsTxt,
                "robots" => $robots,
                "lang" => $lang,
                "rdfa" => $RDFa,
                "microdata" => $microdata,
                "charset" => $charset,
                "ipAddress" => $ipAddress,
                "ipRedirection" => $ipRedirection,
                "flash" => $flash,
                "frame" => $frame,
                "sitemap" => $sitemap,
                "inlineCSS" => $inlineCSS,
                "internalLinks" => $internalLinks,
                "friendlyLinks" => $friendlyLinks,
                "maxLenghtInlinks" => $maxLenghtInlinks,
                "outLinks" => $outLinks,
                "googleAnalytics" => $googleAnalytics,
                "sslCertificate" => $sslCertificate,
                "auditDateTime" => $auditDateTime,
            ];
        } else {
            throw new Exception();
        }
    }
    private function checkAltAttributes($content)
    {
        $crawler = new Crawler($content);

        $images = $crawler->filter("img");

        $missingAltImages = [];

        foreach ($images as $image) {
            $alt = $image->getAttribute("alt");

            if (empty($alt)) {
                $src = $image->getAttribute("src");
                $missingAltImages[] = $src;
            }
        }
        if (empty($missingAltImages)) {
            $missingAltImages = false;
        }
        return $missingAltImages;
    }
    private function getHeaders($content)
    {
        $crawler = new Crawler($content);

        return $crawler
            ->filter("h1, h2, h3, h4, h5, h6")
            ->each(function (Crawler $node) {
                return [
                    "tag" => $node->getNode(0)->tagName,
                    "text" => $node->text(),
                ];
            });
    }

    private function countHeaders(array $headers)
    {
        $headerCounts = [];
        foreach ($headers as $header) {
            $tag = $header["tag"];
            if (isset($headerCounts[$tag])) {
                $headerCounts[$tag]++;
            } else {
                $headerCounts[$tag] = 1;
            }
        }
        // if(empty($headerCount)){
        //     return false;
        // }
        return $headerCounts;
    }
    private function checkHeadersAreUnique($headers)
    {
        $headerTexts = array_column($headers, "text");
        $uniqueTexts = array_unique($headerTexts);

        return count($headerTexts) === count($uniqueTexts);
    }
    private function checkHttpsRedirection($domain)
    {
        $client = HttpClient::create();
        $response = $client->request("GET", "http://" . $domain, [
            "max_redirects" => 0,
        ]);
        $statusCode = $response->getStatusCode();

        return $statusCode === 301 || $statusCode === 302;
    }
    private function checkWwwRedirection($domain)
    {
        $client = HttpClient::create();
        $response = $client->request("GET", "https://www." . $domain, [
            "max_redirects" => 0,
        ]);
        $statusCode = $response->getStatusCode();

        return $statusCode === 301 || $statusCode === 302;
    }
    private function checkIndexsRedirections($url)
    {
        $client = HttpClient::create();
        $responseIndexPhp = $client->request("GET", $url . "/index.php", [
            "max_redirects" => 0,
        ]);
        $statusCodeIndexPhp = $responseIndexPhp->getStatusCode();
        $responseIndexHtml = $client->request("GET", $url . "/index.html", [
            "max_redirects" => 0,
        ]);
        $statusCodeIndexHtml = $responseIndexHtml->getStatusCode();

        return [
            "indexPhpStatus" =>
                $statusCodeIndexPhp === 301 || $statusCodeIndexPhp === 302,
            "indexHtmlStatus" =>
                $statusCodeIndexHtml === 301 || $statusCodeIndexHtml === 302,
        ];
    }
    private function checkRobots($url)
    {
        $client = HttpClient::create();
        $responseRobotsTxt = $client->request("GET", $url . "/robots.txt");
        $statusCodeRobotsTxt = $responseRobotsTxt->getStatusCode();

        $exists = false;
        $hasSitemapDirective = false;

        if ($statusCodeRobotsTxt === 200) {
            $exists = true;
            $robotsContent = $responseRobotsTxt->getContent();

            if (strpos($robotsContent, "Sitemap:") !== false) {
                $hasSitemapDirective = true;
            }
        }

        return [
            "exists" => $exists,
            "hasSitemapDirective" => $hasSitemapDirective,
        ];
    }
    private function checkIPRedirection($ipAddress)
    {
        $client = HttpClient::create();
        $respnseIPAddress = $client->request("GET", "http://" . $ipAddress, [
            "max_redirects" => 0,
        ]);
        $statusCodeIPAddress = $respnseIPAddress->getStatusCode();

        return $statusCodeIPAddress === 301 || $statusCodeIPAddress === 302;
    }
    private function checkSitemap($url)
    {
        $client = HttpClient::create();
        $responseSitemap = $client->request("GET", $url . "/sitemap.xml");
        $statusCodeSitemap = $responseSitemap->getStatusCode();

        return $statusCodeSitemap === 200;
    }
    private function getInLinks($url, $content)
    {
        $crawler = new Crawler($content);

        $links = $crawler->filter("a[href]")->extract(["href"]);

        $filteredLinks = array_filter($links, function ($link) use ($url) {
            return (substr($link, 0, 1) === "/" ||
                substr($link, 0, strlen($url)) ===
                    substr($url, 0, strlen($url))) &&
                !preg_match('/\.(jpg|jpeg|png|gif|webp)$/i', $link);
        });

        $addLinksPrefix = array_map(function ($link) use ($url) {
            return strpos($link, $url) === 0 ? $link : $url . $link;
        }, $filteredLinks);

        $inlinks = array_unique($addLinksPrefix);
        if(empty($inlinks)){
            return false;
        }
        return $inlinks;
    }
    private function checkFriendlyLinks($url, $content)
    {
        $inlinks = $this->getInLinks($url, $content);
        $rule = '/[a-zA-Z0-9\/-?#]+$/';

        $nonFriendlyLinks = array_filter($inlinks, function ($link) use (
            $url,
            $rule
        ) {
            $path = parse_url($link, PHP_URL_PATH);
            $pathWithoutTrailingSlash = rtrim($path, "/");

            return strpos($link, $url) === 0 &&
                !preg_match($rule, $pathWithoutTrailingSlash) &&
                $pathWithoutTrailingSlash !== "";
        });
        if (empty($nonFriendlyLinks)) {
            $nonFriendlyLinks = false;
        }
        return $nonFriendlyLinks;
    }
    private function getOutLinks($url, $content)
    {
        $crawler = new Crawler($content);

        $outlinks = $crawler
            ->filter("a[href]")
            ->each(function (Crawler $node) use ($url) {
                $href = $node->attr("href");
                $rel = $node->attr("rel");

                if (
                    substr($href, 0, 1) !== "/" &&
                    substr($href, 0, strlen($url)) !==
                        substr($url, 0, strlen($url)) &&
                    strpos($href, "mailto:") !== 0 &&
                    strpos($href, "tel:") !== 0 &&
                    strpos($href, "#") !== 0
                ) {
                    return [
                        "href" => $href,
                        "rel" => $rel ?? null,
                    ];
                }
            });
        if (empty(array_filter($outlinks))) {
            return $outlinks = false;
        }
        return array_filter($outlinks);
    }

    private function checkGoogleAnalytics($content)
    {
        $codes = [];

        preg_match_all("/G-[A-Z0-9]{10}|UA-\d{8,}-\d{1,}/", $content, $codes);

        $codes = array_values(array_unique($codes[0]));
        if (empty($codes)) {
            $codes = false;
        }
        return $codes;
    }
    private function checkSslCertificate($domain)
    {
        $client = HttpClient::create();

        $response = $client->request("GET", "https://" . $domain, [
            "max_redirects" => 0,
        ]);
        $statusCode = $response->getStatusCode();

        return $statusCode === 200;
    }
}
