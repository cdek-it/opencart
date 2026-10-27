<?php

namespace CDEK\Tests\Unit\Models;

use CDEK\Models\Tariffs;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class TariffsTest extends TestCase
{
    public static function tariffDirectionProvider(): array
    {
        return [
            'door-door'     => [139, true, true],
            'door-office'   => [138, true, false],
            'office-door'   => [137, false, true],
            'office-office' => [136, false, false],
            'door-pickup'   => [366, true, false],
            'office-pickup' => [368, false, false],
        ];
    }

    #[DataProvider('tariffDirectionProvider')]
    public function testTariffDirection(int $code, bool $fromDoor, bool $toDoor): void
    {
        $this->assertSame($fromDoor, Tariffs::isTariffFromDoor($code));
        $this->assertSame($toDoor, Tariffs::isTariffToDoor($code));
    }

    public function testUnknownTariffThrows(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unknown tariff');

        Tariffs::isTariffFromDoor(0);
    }
}
