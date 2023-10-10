<?php
use PHPUnit\Framework\TestCase;
use App\CommissionCalculator;

class CommissionCalculatorTest extends TestCase
{
    public function testCalculateCommissionsFromFile()
    {
        // Mock the file content for testing
        $testFile = "test_input.txt";
        $fileContent = '{"bin":"45717360","amount":"100.00","currency":"EUR"}
                        {"bin":"516793","amount":"50.00","currency":"USD"}';
        file_put_contents($testFile, $fileContent);
        $calculator = new CommissionCalculator($testFile);

        // Capture the output
        ob_start();
        $calculator->calculateCommissionsFromFile();
        $output = ob_get_clean();

        // Assert the expected output
        $expectedOutput = "1.00\n0.48\n";
        $this->assertEquals($expectedOutput, $output);
        unlink($testFile);
    }

    public function testGetCountryCode()
    {
        // Create an instance of CommissionCalculator with a test file
        $calculator = new CommissionCalculator();

        // Call the method and assert the expected result
        $countryCode = $calculator->getCountryCode('45717360');
        $this->assertEquals('DK', $countryCode);
    }

    public function testCalculateCommissions()
    {
        // Create an instance of CommissionCalculator with a test file
        $calculator = new CommissionCalculator();
        $amount = 100;
        $isEu = true;
        $exchangeRate = 0.01;

        $commission = $calculator->calculateCommission($amount, $isEu, $exchangeRate);
        $this->assertEquals('100', $commission);

        $isEu = false;
        $commission = $calculator->calculateCommission($amount, $isEu, $exchangeRate);
        $this->assertEquals('200', $commission);

    }
}
