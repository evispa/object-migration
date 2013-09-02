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

use Evispa\ObjectMigration\VersionConverter;
use Evispa\ObjectMigration\VersionReader;

class AnnotationTester
{
    /**
     * @var VersionConverter
     */
    protected $versionConverter;

    /**
     * @var VersionReader
     */
    protected $versionReader;

    public function __construct($versionConverter, $versionReader)
    {
        $this->versionConverter = $versionConverter;
        $this->versionReader = $versionReader;
    }

    public function testAllVariations()
    {
        $migrationAnnotations = $this->getClassMigrationMethodInfo($startFromClassName);
        /** @var MethodInfo $methodInfo */
        foreach ($migrationAnnotations as $methodInfo) {
            if ($methodInfo->annotation->from && !in_array($methodInfo->annotation->from, $visited)) {
                $visited[] = $methodInfo->annotation->from;
                $className = $this->getClassNameByVersion($methodInfo->annotation->from, $version, $visited);
                if ($className) {
                    return $className;
                }
            }

            if ($methodInfo->annotation->to && !in_array($methodInfo->annotation->to, $visited)) {
                $visited[] = $methodInfo->annotation->to;
                $className = $this->getClassNameByVersion($methodInfo->annotation->to, $version, $visited);
                if ($className) {
                    return $className;
                }
            }
        }
    }
}