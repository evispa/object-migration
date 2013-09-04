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
use Evispa\ObjectMigration\Tests\Mock\MockCodeV2;
use Evispa\ObjectMigration\Tests\Mock\MockCodeV3;
use Evispa\ObjectMigration\Tests\Mock\MockCodeV4;
use Evispa\ObjectMigration\VersionConverter;
use Evispa\ObjectMigration\VersionReader;

class AnnotationTesterTest extends \PHPUnit_Framework_TestCase
{
    public function testTesterCodeV4()
    {
        $versionReader = new VersionReader(new AnnotationReader());
        $versionConverter = new VersionConverter($versionReader, 'Evispa\ObjectMigration\Tests\Mock\MockCodeV4', array('locale' => 'lt'));

        $tester = new AnnotationTester($versionConverter, $versionReader);
        $tester->testAllVariations();

        $this->assertTrue(
            in_array(
                array('from' => get_class(new MockCodeV4()), 'to' => get_class(new MockCodeV3())),
                $tester->testedMigrations['to']
            )
        );

        $this->assertTrue(
            in_array(
                array('from' => get_class(new MockCodeV3()), 'to' => get_class(new MockCodeV2())),
                $tester->testedMigrations['to']
            )
        );

        $this->assertTrue(
            in_array(
                array('from' => get_class(new MockCodeV3()), 'to' => get_class(new MockCodeV1())),
                $tester->testedMigrations['to']
            )
        );

        $this->assertTrue(
            in_array(
                array('from' => get_class(new MockCodeV2()), 'to' => get_class(new MockCodeV1())),
                $tester->testedMigrations['to']
            )
        );

        $this->assertTrue(
            in_array(
                array('from' => get_class(new MockCodeV1()), 'to' => get_class(new MockCodeV4())),
                $tester->testedMigrations['to']
            )
        );

        $this->assertEquals(5, count($tester->testedMigrations['to']));


        $this->assertTrue(
            in_array(
                array('from' => get_class(new MockCodeV2()), 'to' => get_class(new MockCodeV4())),
                $tester->testedMigrations['from']
            )
        );

        $this->assertTrue(
            in_array(
                array('from' => get_class(new MockCodeV1()), 'to' => get_class(new MockCodeV2())),
                $tester->testedMigrations['from']
            )
        );

        $this->assertTrue(
            in_array(
                array('from' => get_class(new MockCodeV3()), 'to' => get_class(new MockCodeV2())),
                $tester->testedMigrations['from']
            )
        );

        $this->assertTrue(
            in_array(
                array('from' => get_class(new MockCodeV3()), 'to' => get_class(new MockCodeV1())),
                $tester->testedMigrations['from']
            )
        );

        $this->assertTrue(
            in_array(
                array('from' => get_class(new MockCodeV4()), 'to' => get_class(new MockCodeV1())),
                $tester->testedMigrations['from']
            )
        );

        $this->assertEquals(5, count($tester->testedMigrations['from']));
    }

    public function testTesterCodeV0()
    {
        $versionReader = new VersionReader(new AnnotationReader());
        $versionConverter = new VersionConverter($versionReader, 'Evispa\ObjectMigration\Tests\Mock\MockCodeV0', array('locale' => 'lt'));

        $tester = new AnnotationTester($versionConverter, $versionReader);
        $tester->testAllVariations();

        $this->assertEquals(0, count($tester->testedMigrations['to']));

        $this->assertTrue(
            in_array(
                array('from' => get_class(new MockCodeV1()), 'to' => get_class(new MockCodeV0())),
                $tester->testedMigrations['from']
            )
        );

        $this->assertTrue(
            in_array(
                array('from' => get_class(new MockCodeV3()), 'to' => get_class(new MockCodeV1())),
                $tester->testedMigrations['from']
            )
        );

        $this->assertTrue(
            in_array(
                array('from' => get_class(new MockCodeV4()), 'to' => get_class(new MockCodeV1())),
                $tester->testedMigrations['from']
            )
        );

        $this->assertTrue(
            in_array(
                array('from' => get_class(new MockCodeV2()), 'to' => get_class(new MockCodeV4())),
                $tester->testedMigrations['from']
            )
        );

        $this->assertTrue(
            in_array(
                array('from' => get_class(new MockCodeV1()), 'to' => get_class(new MockCodeV2())),
                $tester->testedMigrations['from']
            )
        );

        $this->assertTrue(
            in_array(
                array('from' => get_class(new MockCodeV3()), 'to' => get_class(new MockCodeV2())),
                $tester->testedMigrations['from']
            )
        );

        $this->assertEquals(6, count($tester->testedMigrations['from']));
    }

    public function testTesterCodeV3()
    {
        $versionReader = new VersionReader(new AnnotationReader());
        $versionConverter = new VersionConverter($versionReader, 'Evispa\ObjectMigration\Tests\Mock\MockCodeV3', array('locale' => 'lt'));

        $tester = new AnnotationTester($versionConverter, $versionReader);
        $tester->testAllVariations();

        $this->assertEquals(0, count($tester->testedMigrations['from']));

        $this->assertTrue(
            in_array(
                array('from' => get_class(new MockCodeV3()), 'to' => get_class(new MockCodeV2())),
                $tester->testedMigrations['to']
            )
        );

        $this->assertTrue(
            in_array(
                array('from' => get_class(new MockCodeV3()), 'to' => get_class(new MockCodeV1())),
                $tester->testedMigrations['to']
            )
        );

        $this->assertTrue(
            in_array(
                array('from' => get_class(new MockCodeV2()), 'to' => get_class(new MockCodeV1())),
                $tester->testedMigrations['to']
            )
        );

        $this->assertTrue(
            in_array(
                array('from' => get_class(new MockCodeV1()), 'to' => get_class(new MockCodeV4())),
                $tester->testedMigrations['to']
            )
        );

        $this->assertTrue(
            in_array(
                array('from' => get_class(new MockCodeV4()), 'to' => get_class(new MockCodeV3())),
                $tester->testedMigrations['to']
            )
        );

        $this->assertEquals(5, count($tester->testedMigrations['to']));
    }


    /**
     * @expectedException \LogicException
     */
    public function testOptionsException()
    {
        $versionReader = new VersionReader(new AnnotationReader());
        $versionConverter = new VersionConverter($versionReader, 'Evispa\ObjectMigration\Tests\Mock\MockCodeV4');

        $tester = new AnnotationTester($versionConverter, $versionReader);
        $tester->testAllVariations();
    }
}
