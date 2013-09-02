<?php
/*
 * Copyright (c) 2013 Evispa Ltd.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

/**
 * @author Darius Krištapavičius <darius@evispa.lt>
 */

namespace Evispa\ObjectMigration\Tests;


use Doctrine\Common\Annotations\AnnotationReader;
use Evispa\ObjectMigration\Tests\Mock\MockCodeV0;
use Evispa\ObjectMigration\Tests\Mock\MockCodeV1;
use Evispa\ObjectMigration\Tests\Mock\MockCodeV4;
use Evispa\ObjectMigration\VersionConverter;
use Evispa\ObjectMigration\VersionReader;

class VersionConverterTest extends \PHPUnit_Framework_TestCase
{
    public function testVersionConvert()
    {
        $converter = new VersionConverter(
            new VersionReader(new AnnotationReader()),
            'Evispa\ObjectMigration\Tests\Mock\MockCodeV4'
        );

        $codeV1 = new MockCodeV1();
        $codeV1->code = 'sku#kodas';

        $codeV4 = $converter->migrateFrom($codeV1);

        $this->assertTrue($codeV4 instanceof MockCodeV4);
        $this->assertEquals($codeV1->code, $codeV4->code);
    }

    public function testVersionConvertBack()
    {
        $converter = new VersionConverter(
            new VersionReader(new AnnotationReader()),
            'Evispa\ObjectMigration\Tests\Mock\MockCodeV1'
        );

        $codeV4 = new MockCodeV4();
        $codeV4->code = 'sku#kodas';

        $codeV1 = $converter->migrateFrom($codeV4);

        $this->assertTrue($codeV1 instanceof MockCodeV1);
        $this->assertEquals($codeV1->code, $codeV4->code);
    }

    public function testVersionConvertToFrom()
    {
        $converter = new VersionConverter(
            new VersionReader(new AnnotationReader()),
            'Evispa\ObjectMigration\Tests\Mock\MockCodeV0'
        );

        $codeV4 = new MockCodeV4();
        $codeV4->code = 'sku#kodas';

        $codeV0 = $converter->migrateFrom($codeV4);

        $this->assertTrue($codeV0 instanceof MockCodeV0);
        $this->assertEquals($codeV0->code, $codeV4->code);
    }
}
