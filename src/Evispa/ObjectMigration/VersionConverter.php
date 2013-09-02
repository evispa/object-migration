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
 * @author Nerijus Arlauskas <nercury@gmail.com>
 */

namespace Evispa\ObjectMigration;

use Evispa\ObjectMigration\Exception\VersionPathNotFoundException;
use Evispa\ObjectMigration\VersionPath\VersionPathSearch;

/**
 *
 *
 */
class VersionConverter
{
    /**
     * Version reader.
     *
     * @var VersionReader
     */
    private $reader;

    /**
     * @var string
     */
    private $className;

    /**
     * @var array
     */
    private $options;

    public function __construct(VersionReader $reader, $className, $options = array())
    {
        $this->reader = $reader;
        $this->className = $className;

        $requiredOptions = $reader->getRequiredClassOptions($className);
        foreach ($requiredOptions as $option => $classes) {
            if (!isset($options[$option])) {
                throw new \LogicException('Migration from "' . $classes['from'] . '" to "' . $classes['to'] . '" may require "' . $option . '" option which was not set.');
            }
        }

        $this->options = $options;
    }

    /**
     * Migrate object to specified version.
     *
     * @param mixed  $object       Object instance.
     * @param string $otherVersion Version name.
     *
     * @throws \LogicException
     * @throws Exception\VersionPathNotFoundException
     *
     * @return mixed Migrated object.
     */
    public function migrateTo($object, $otherVersion)
    {
        $className = get_class($object);

        if ($className !== $this->className) {
            throw new \LogicException('Converter for class "' . $className . '" can not migrate "' . $className . '" objects.');
        }

        $versionPath = new VersionPathSearch($this->reader);
        $otherVersionClassName = $this->reader->getClassNameByVersion($className, $otherVersion);
        if ($otherVersionClassName) {
            $migrations = $versionPath->find($className, $otherVersionClassName);

            if (count($migrations) === 0) {
                throw new VersionPathNotFoundException($className, $otherVersionClassName);
            }

            foreach ($migrations as $migration) {
                $object = $migration->action->run($object, $this->options);
            }
        }

        return $object;
    }

    /**
     * Get object from other version.
     *
     * @param mixed $otherObject  Object instance.
     *
     * @throws Exception\VersionPathNotFoundException
     *
     * @return mixed Migrated object.
     */
    public function migrateFrom($otherObject)
    {
        $otherObjectClass = get_class($otherObject);
        $versionPath = new VersionPathSearch($this->reader);
        $migrations = $versionPath->find($otherObjectClass, $this->className);

        if (count($migrations) === 0) {
            throw new VersionPathNotFoundException($otherObjectClass, $this->className);
        }

        foreach ($migrations as $migration) {
            $otherObject = $migration->action->run($otherObject, $this->options);
        }

        return $otherObject;
    }
}