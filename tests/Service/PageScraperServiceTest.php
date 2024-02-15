<?php
 
namespace App\Tests\Service;
 
use App\Service\PageScraperService;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpClient\Exception\TransportException;
 
 
class PageScraperServiceTest extends KernelTestCase
{
    private PageScraperService $pageScraperService;
 
    public function setUp(): void
    {
        $kernel = self::bootKernel();
        $container = static::getContainer();
 
        $this->pageScraperService = $container->get(PageScraperService::class);
    }
 
    public function testScrapePageContentForCorrectDomain() : void
    {
        $output = $this->pageScraperService->scrapePageContent("bandola.com.pl");
 
        $this->assertEquals("Tytuł - Testowa", $output['title']);
        $this->assertEquals("brak", $output['description']);
        $this->assertEquals("Testowy keyword", $output['keywords']);
        $this->assertEquals("bandola.com.pl", $output['domain']);
        $this->assertEquals("jest", $output['favicon']);
        $this->assertEquals("jest", $output['viewport']);
        $this->assertEquals("jest => index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1", $output['robots']);
        $this->assertEquals("pl-PL", $output['lang']);
        $this->assertEquals("UTF-8", $output['charset']);
        $this->assertEquals("jest", $output['rdfa']);
        $this->assertEquals("jest", $output['microdata']);
        $this->assertEquals("brak", $output['flash']);
        $this->assertEquals("brak", $output['frame']);
        $this->assertEquals("brak", $output['inlineCSS']);
        $this->assertEquals("188.210.221.84", $output['ipAddress']);
        $this->assertEquals("Testowa", $output['headers'][0]['text']);
        $this->assertEquals("Testowy nagłówek h2", $output['headers'][1]['text']);
        $this->assertEquals("Testowy nagłówek h3", $output['headers'][2]['text']);
        $this->assertEquals("Drugi testowy nagłówek h2", $output['headers'][3]['text']);
        $this->assertEquals("1", $output['headerCounts']['h1']);
        $this->assertEquals("2", $output['headerCounts']['h2']);
        $this->assertEquals("1", $output['headerCounts']['h3']);
        $this->assertEquals("true", $output['headersUnique']);
        $this->assertEquals("https://bandola.com.pl/wp-content/uploads/2024/01/Untitled-300x300.png", $output['missingAltImages'][0]);
        $this->assertEquals("true", $output['httpsRedirection']);
        $this->assertEquals("true", $output['wwwRedirection']);
        $this->assertEquals("true", $output['indexesRedirection']['indexPhpStatus']);
        $this->assertFalse($output['indexesRedirection']['indexHtmlStatus']);
        $this->assertEquals("exists", $output['robotsTxt']['exists']);
        $this->assertEquals("hasSitemapDirective", $output['robotsTxt']['hasSitemapDirective']);
        $this->assertEquals("https://bandola.com.pl/", $output['internalLinks'][1]);
        $this->assertEquals("https://bandola.com.pl/sample-page/", $output['internalLinks'][2]);
        $this->assertFalse($output['friendlyLinks']);
        $this->assertEquals("35", $output['maxLenghtInlinks']);
        $this->assertEquals("https://pl.wordpress.org/", $output['outLinks'][3]['href']);
        $this->assertNull($output['outLinks'][3]['rel']);
        $this->assertEquals("http://www.greatfood.com", $output['outLinks'][4]['href']);
        $this->assertNull($output['outLinks'][4]['rel']);
        $this->assertFalse($output['googleAnalytics']);
        $this->assertTrue($output['sslCertificate']);
    }
 
    public function testScrapePageContentForDomainWithDifferentContent() : void
    {
        $output = $this->pageScraperService->scrapePageContent("bandola.com.pl/pss");
 
        $this->assertEquals("", $output['title']);
        $this->assertEquals("brak", $output['description']);
        $this->assertEquals("brak", $output['keywords']);
        $this->assertEquals("bandola.com.pl/pss", $output['domain']);
        $this->assertEquals("brak", $output['favicon']);
        $this->assertEquals("jest", $output['viewport']);
        $this->assertEquals("jest => noindex, nofollow", $output['robots']);
        $this->assertEquals("pl-PL", $output['lang']);
        $this->assertEquals("UTF-8", $output['charset']);
        $this->assertEquals("brak", $output['rdfa']);
        $this->assertEquals("jest", $output['microdata']);
        $this->assertEquals("brak", $output['flash']);
        $this->assertEquals("brak", $output['frame']);
        $this->assertEquals("jest", $output['inlineCSS']);
        $this->assertEquals("Strona na WordPress PSS", $output['headers'][0]['text']);
        $this->assertEquals("Strona na WordPress PSS", $output['headers'][1]['text']);
        $this->assertEquals("1", $output['headerCounts']['h1']);
        $this->assertEquals("1", $output['headerCounts']['h2']);
        $this->assertFalse($output['headersUnique']);
        $this->assertFalse($output['missingAltImages']);
        $this->assertEquals("true", $output['httpsRedirection']);
        $this->assertEquals("true", $output['wwwRedirection']);
        $this->assertEquals("true", $output['indexesRedirection']['indexPhpStatus']);
        $this->assertFalse($output['indexesRedirection']['indexHtmlStatus']);
        $this->assertFalse($output['robotsTxt']['exists']);
        $this->assertFalse($output['robotsTxt']['hasSitemapDirective']);
        $this->assertEquals("http://bandola.com.pl/pss", $output['internalLinks'][0]);
        $this->assertEquals("http://bandola.com.pl/pss/galeria/", $output['internalLinks'][2]);
        $this->assertEquals("http://bandola.com.pl/pss/aktualnosci/", $output['internalLinks'][3]);
        $this->assertFalse($output['friendlyLinks']);
        $this->assertEquals("38", $output['maxLenghtInlinks']);
        $this->assertFalse($output['googleAnalytics']);
        $this->assertFalse($output['sslCertificate']);
    }
 
    public function testGetPageSpeedInsightsForNonExistingDomain() : void
    {
        $this->expectException(TransportException::class);
        $this->expectExceptionCode(0);
 
        $this->pageScraperService->scrapePageContent("bandola1.com.pl");
    }
 
    public function testPageScrapingPerformance(): void
    {
        $domainName = "bandola.com.pl";
        $iterations = 10;

        $startTime = microtime(true);

        for ($i = 0; $i < $iterations; $i++) {
            $this->pageScraperService->scrapePageContent($domainName);
        }

        $endTime = microtime(true);

        $totalTime = $endTime - $startTime;

        $averageTime = $totalTime / $iterations;

        echo "\nIterations: $iterations\n";
        echo "Total time: $totalTime sekund\n";
        echo "Avg time for one iteration $averageTime sekund\n";

        $this->assertLessThan(30, $averageTime);
    }
}
 