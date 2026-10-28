<?php

namespace Tests\Unit;

use App\Enums\Frequency;
use App\Support\Money;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class MoneyTest extends TestCase
{
    public static function monthlyAmounts(): array
    {
        return [
            'weekly' => ['12.00', Frequency::Weekly, 5200],
            'monthly' => ['2200.00', Frequency::Monthly, 220000],
            'quarterly' => ['100.00', Frequency::Quarterly, 3333],
            'yearly' => ['120.00', Frequency::Yearly, 1000],
            'half up' => ['0.06', Frequency::Yearly, 1],
            'small weekly' => ['0.01', Frequency::Weekly, 4],
            'large exact value' => ['99999999.99', Frequency::Monthly, 9999999999],
        ];
    }

    #[DataProvider('monthlyAmounts')]
    public function test_monthly_averages_are_rounded_in_integer_cents(string $amount, Frequency $frequency, int $expected): void
    {
        $this->assertSame($expected, Money::monthly($amount, $frequency));
    }

    public function test_decimal_conversion_and_dutch_formatting(): void
    {
        $this->assertSame(101, Money::cents('1.01'));
        $this->assertSame(110, Money::cents('1.1'));
        $this->assertSame('€ 1.234,56', Money::format(123456));
        $this->assertSame('− € 0,01', Money::format(-1));
    }

    public function test_invalid_precision_is_rejected(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Money::cents('0.001');
    }
}
