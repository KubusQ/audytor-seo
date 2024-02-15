<?php
 
namespace App\Tests\Service;
 
use App\Service\PageSpeedInsightsService;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpClient\Exception\ClientException;
 
class PageSpeedInsightsServiceTest extends KernelTestCase
{
    private PageSpeedInsightsService $pageSpeedInsightsService;
    private const MIN_VALUE = 0.1;
    private const MAX_VALUE = 3;
 
    public function setUp(): void
    {
        $kernel = self::bootKernel();
        $container = static::getContainer();
 
        $this->pageSpeedInsightsService = $container->get(PageSpeedInsightsService::class);
    }
 
    public function testPageSpeedInsightService() : void
    {
        $output = $this->pageSpeedInsightsService->getPageSpeedInsights("https://bandola.com.pl");
 
        // check mobile PSI
        $this->assertThat(
            $output['mobile'],
            $this->logicalAnd(
                $this->greaterThan(80),
                $this->lessThan(101)
            )
        );
 
        // check desktop PSI
        $this->assertThat(
            $output['desktop'],
            $this->logicalAnd(
                $this->greaterThan(80),
                $this->lessThan(101)
            )
        );
 
        // check mobile metrics
        $this->assertThat(
            (float) $output['metrics']['mobile']['firstContentfulPaint'],
            $this->logicalAnd(
                $this->greaterThan(self::MIN_VALUE),
                $this->lessThan(self::MAX_VALUE)
            )
        );
 
        $this->assertThat(
            (float) $output['metrics']['mobile']['speedIndex'],
            $this->logicalAnd(
                $this->greaterThan(self::MIN_VALUE),
                $this->lessThan(self::MAX_VALUE)
            )
        );
 
        $this->assertThat(
            (float) $output['metrics']['mobile']['timeToInteractive'],
            $this->logicalAnd(
                $this->greaterThan(self::MIN_VALUE),
                $this->lessThan(self::MAX_VALUE)
            )
        );
 
        $this->assertThat(
            (float) $output['metrics']['mobile']['firstMeaningfulPaint'],
            $this->logicalAnd(
                $this->greaterThan(self::MIN_VALUE),
                $this->lessThan(self::MAX_VALUE)
            )
        );
 
        // check desktop metrics
        $this->assertThat(
            (float) $output['metrics']['desktop']['firstContentfulPaint'],
            $this->logicalAnd(
                $this->greaterThan(self::MIN_VALUE),
                $this->lessThan(self::MAX_VALUE)
            )
        );
 
        $this->assertThat(
            (float) $output['metrics']['desktop']['speedIndex'],
            $this->logicalAnd(
                $this->greaterThan(self::MIN_VALUE),
                $this->lessThan(self::MAX_VALUE)
            )
        );
 
        $this->assertThat(
            (float) $output['metrics']['desktop']['timeToInteractive'],
            $this->logicalAnd(
                $this->greaterThan(self::MIN_VALUE),
                $this->lessThan(self::MAX_VALUE)
            )
        );
 
        $this->assertThat(
            (float) $output['metrics']['desktop']['firstMeaningfulPaint'],
            $this->logicalAnd(
                $this->greaterThan(self::MIN_VALUE),
                $this->lessThan(self::MAX_VALUE)
            )
        );
    }
 
    public function testGetPageSpeedInsightsForNonExistingDomain() : void
    {
        $this->expectException(ClientException::class);
        $this->expectExceptionCode(400);
 
        $this->pageSpeedInsightsService->getPageSpeedInsights("https://bandola1.com.pl");
    }

    public function testPageSpeedInsightsPerformance(): void
    {
        $domainName = "https://bandola.com.pl";
        $iterations = 10;

        $startTime = microtime(true);

        for ($i = 0; $i < $iterations; $i++) {
            $this->pageSpeedInsightsService->getPageSpeedInsights($domainName);
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