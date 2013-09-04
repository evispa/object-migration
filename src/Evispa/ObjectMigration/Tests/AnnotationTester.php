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
        $className = $this->versionConverter->getClassName();
        $class = new \ReflectionClass($className);
        $object = $class->newInstance();

        $this->migrateTo($object);
        $this->migrateFrom($className);
    }

    /**
     * Walks through all migrateTo functions
     * With empty object
     *
     * @param $fromObject
     * @param $visited
     */
    private function migrateTo($fromObject, &$visited = array())
    {
        $className = get_class($fromObject);
        $migrations = $this->versionReader->getClassMigrationMethodInfo($className);

        /** @var MethodInfo $methodInfo */
        foreach ($migrations as $methodInfo) {
            $methodId = $className . '->' . $methodInfo->name;

            if ($methodInfo->annotation->to && !in_array($methodId, $visited)) {
                array_push($visited, $methodId);
                $newObject = $methodInfo->action->run(clone $fromObject, $this->versionConverter->getOptions());
                $this->migrateTo($newObject, $visited);
            }
        }
    }

    /**
     * Walks through all migrateFrom functions
     * With empty object
     *
     * @param $fromClassName
     * @param $path
     * @param $visited
     */
    private function migrateFrom($fromClassName, $path = array(), &$visited = array())
    {
        $migrations = $this->versionReader->getClassMigrationMethodInfo($fromClassName);

        $found = false;

        /** @var MethodInfo $methodInfo */
        foreach ($migrations as $methodInfo) {

            $methodId = $fromClassName . '::' . $methodInfo->name;

            if ($methodInfo->annotation->from && !in_array($methodId, $visited)) {
                array_push($visited, $methodId);

                $migrationsPath = $path;
                $migrationsPath[] = $methodInfo;

                $this->migrateFrom($methodInfo->annotation->from, $migrationsPath, $visited);
                $found = true;
            }
        }

        if (false === $found) {
            $path = array_reverse($path);

            $class = new \ReflectionClass($fromClassName);
            $object = $class->newInstance();

            /** @var MethodInfo $methodInfo */
            foreach ($path as $methodInfo) {
                $object = $methodInfo->action->run($object, $this->versionConverter->getOptions());
            }
        }

    }
}
