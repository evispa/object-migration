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
use Evispa\ObjectMigration\VersionPath\VersionPathSearch;
use Evispa\ObjectMigration\VersionReader;

class VersionPathSearchTest extends \PHPUnit_Framework_TestCase
{
    public function testFind()
    {
        $search = new VersionPathSearch(new VersionReader(new AnnotationReader()));

        $result = $search->find(
            'Evispa\ObjectMigration\Tests\Mock\MockCodeV3',
            'Evispa\ObjectMigration\Tests\Mock\MockCodeV4'
        );

        $this->assertEquals('Evispa\ObjectMigration\Tests\Mock\MockCodeV3', $result[0]->action->method->class);
        $this->assertEquals('toCodeV2', $result[0]->action->method->name);

        $this->assertEquals('Evispa\ObjectMigration\Tests\Mock\MockCodeV4', $result[1]->action->method->class);
        $this->assertEquals('fromCodeV2', $result[1]->action->method->name);
    }

    public function testNotFound()
    {
        $search = new VersionPathSearch(new VersionReader(new AnnotationReader()));

        $result = $search->find(
            'Evispa\ObjectMigration\Tests\Mock\MockCodeV0',
            'Evispa\ObjectMigration\Tests\Mock\MockCodeV3'
        );

        $this->assertEquals(0, count($result));
    }

    public function testFind2()
    {
        $search = new VersionPathSearch(new VersionReader(new AnnotationReader()));

        $result = $search->find(
            'Evispa\ObjectMigration\Tests\Mock\MockCodeV1',
            'Evispa\ObjectMigration\Tests\Mock\MockCodeV4'
        );

        $this->assertEquals('Evispa\ObjectMigration\Tests\Mock\MockCodeV1', $result[0]->action->method->class);
        $this->assertEquals('toCodeV4', $result[0]->action->method->name);
    }
}
