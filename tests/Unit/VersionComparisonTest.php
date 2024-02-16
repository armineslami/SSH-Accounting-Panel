<?php

namespace Tests\Unit;

use App\Utils\Utils;
use Tests\TestCase;

class VersionComparisonTest extends TestCase
{
    /**
     * Test the version comparison function.
     *
     * @return void
     */
    public function testVersionComparison()
    {
        // Define test cases
        $testCases = [
            ['version1' => '3.0.0', 'version2' => '3.1.0', 'expected' => -1],
            ['version1' => '3.1.0', 'version2' => '3.0.0', 'expected' => 1],
            ['version1' => '3.1.0', 'version2' => '3.1.0', 'expected' => 0],
            ['version1' => '2.0.0', 'version2' => '3.0.0', 'expected' => -1],
            ['version1' => '3.0.0', 'version2' => '2.0.0', 'expected' => 1],
        ];

        // Perform assertions
        foreach ($testCases as $testCase) {
            $actualResult = Utils::compareVersions($testCase['version1'], $testCase['version2']);
            $this->assertEquals($testCase['expected'], $actualResult, "Failed for versions: {$testCase['version1']} and {$testCase['version2']}");
        }
    }
}
