<?php

/*
 * This file is part of the UxSearch project.
 *
 * (c) Mezcalito (https://www.mezcalito.fr)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Mezcalito\UxSearchBundle\Tests\Twig;

use Mezcalito\UxSearchBundle\Twig\UxSearchExtension;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;
use PHPUnit\Framework\TestCase;

#[RequiresPhpExtension('intl')]
class UxSearchExtensionTest extends TestCase
{
    private string $previousLocale;

    protected function setUp(): void
    {
        $this->previousLocale = \Locale::getDefault();
    }

    protected function tearDown(): void
    {
        \Locale::setDefault($this->previousLocale);
    }

    #[DataProvider('formatNumberProvider')]
    public function testFormatNumber(string $locale, int|float|string $number, string $expected): void
    {
        \Locale::setDefault($locale);

        $this->assertSame($expected, (new UxSearchExtension())->formatNumber($number));
    }

    /**
     * @return iterable<string, array{string, int|float|string, string}>
     */
    public static function formatNumberProvider(): iterable
    {
        yield 'english integer' => ['en', 12345, '12,345'];
        yield 'german integer' => ['de', 12345, '12.345'];
        yield 'french integer' => ['fr', 12345, "12\u{202F}345"];
        yield 'small integer' => ['de', 999, '999'];
        yield 'zero' => ['de', 0, '0'];
        yield 'numeric string' => ['de', '12345', '12.345'];
        yield 'float' => ['de', 1234.5, '1.234,5'];
        yield 'non-numeric string' => ['de', 'n/a', 'n/a'];
    }

    public function testFormatNumberIsRegisteredAsFilter(): void
    {
        $filterNames = array_map(
            static fn ($filter) => $filter->getName(),
            (new UxSearchExtension())->getFilters(),
        );

        $this->assertContains('ux_search_format_number', $filterNames);
    }
}
