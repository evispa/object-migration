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

use Evispa\ObjectMigration\Migration\MethodInfo;
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

    protected $visited = array();

    public function __construct($versionConverter, $versionReader)
    {
        $this->versionConverter = $versionConverter;
        $this->versionReader = $versionReader;
    }

    /**
     * Walks through all migrations functions
     * With empty objects
     */
    public function testAllVariations()
    {
        $class = new \ReflectionClass($this->versionConverter->getClassName());
        $object = $class->newInstance();

        $visited = array();
        $this->migrateTo($object, $visited);
    }

    /**
     * Walks through all migrateTo functions
     * With empty object
     *
     * @param $fromObject
     * @param $visited
     */
    private function migrateTo($fromObject, &$visited)
    {
        $className = get_class($fromObject);
        $migrations = $this->versionReader->getClassMigrationMethodInfo($className);

        /** @var MethodInfo $methodInfo */
        foreach ($migrations as $methodInfo) {
            if ($methodInfo->annotation->to && !in_array($methodInfo->annotation->to, $this->visited)) {
                array_push($this->visited, $methodInfo->annotation->to);
                $newObject = $methodInfo->action->run(clone $fromObject, $this->versionConverter->getOptions());
                $this->migrateTo($newObject, $visited);
            }
        }
    }
}