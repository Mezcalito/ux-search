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

namespace Mezcalito\UxSearchBundle\Tests\Attribute;

use Mezcalito\UxSearchBundle\Attribute\AsSearch;
use Mezcalito\UxSearchBundle\Tests\Fixtures\Attribute\TestClass;
use Mezcalito\UxSearchBundle\Tests\Fixtures\Attribute\TestClassWithoutName;
use PHPUnit\Framework\TestCase;

class AsSearchTest extends TestCase
{
    public function testAsSearchAttributeIsAssociatedWithClass(): void
    {
        $reflectionClass = new \ReflectionClass(TestClass::class);
        $attributes = $reflectionClass->getAttributes(AsSearch::class);

        $this->assertCount(1, $attributes);
    }

    public function testAsSearchAttributeProperties(): void
    {
        $reflectionClass = new \ReflectionClass(TestClass::class);
        $attribute = $reflectionClass->getAttributes(AsSearch::class)[0];

        $asSearchInstance = $attribute->newInstance();

        $this->assertSame('search_index', $asSearchInstance->index);
        $this->assertSame('search_name', $asSearchInstance->name);
        $this->assertSame('search_adapter', $asSearchInstance->adapter);
    }

    public function testAsSearchAttributeWithDefaultName(): void
    {
        $reflectionClass = new \ReflectionClass(TestClassWithoutName::class);
        $attribute = $reflectionClass->getAttributes(AsSearch::class)[0];

        $asSearchInstance = $attribute->newInstance();

        $this->assertSame('search_index', $asSearchInstance->index);
        $this->assertNull($asSearchInstance->name);
        $this->assertNull($asSearchInstance->adapter);
    }
}
