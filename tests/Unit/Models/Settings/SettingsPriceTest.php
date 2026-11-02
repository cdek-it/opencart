<?php

namespace CDEK\Tests\Unit\Models\Settings;

use CDEK\Models\Settings\SettingsPrice;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class SettingsPriceTest extends TestCase
{
    public function testValidWhenEmpty(): void
    {
        (new SettingsPrice())->validate();

        $this->expectNotToPerformAssertions();
    }

    public function testFieldsAreReadFromPostData(): void
    {
        $settings = new SettingsPrice([
            'cdek_official__priceFix'  => '100.5',
            'cdek_official__priceFree' => '',
        ]);

        $this->assertSame(100.5, $settings->priceFix);
        $this->assertNull($settings->priceFree);
    }

    public static function negativeValueProvider(): array
    {
        return [
            'extra price'         => ['priceExtraPrice', 'cdek_error_price_extra_price_invalid'],
            'percentage increase' => ['pricePercentageIncrease', 'cdek_error_price_percentage_increase_invalid'],
            'fix'                 => ['priceFix', 'cdek_error_price_fix_invalid'],
            'free'                => ['priceFree', 'cdek_error_price_free_invalid'],
        ];
    }

    #[DataProvider('negativeValueProvider')]
    public function testNegativeValueIsRejected(string $field, string $message): void
    {
        $settings = new SettingsPrice(['cdek_official__' . $field => '-1']);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage($message);

        $settings->validate();
    }
}
