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

use Doctrine\Common\Annotations\Reader;
use Evispa\ObjectMigration\Action\CloneAction;
use Evispa\ObjectMigration\Action\CreateAction;
use Evispa\ObjectMigration\Migration\MethodInfo;
use Evispa\ObjectMigration\Migration\MigrationMethods;

class VersionReader
{
    private $classReflections = array();
    private $classVersions = array();
    private $classMigrationMethods = array();

    private $reader;

    public function __construct(Reader $reader)
    {
        $this->reader = $reader;
    }

    /**
     * Get the reflection class instance for a class name.
     *
     * @param string $className Full class name.
     *
     * @return \ReflectionClass
     */
    public function getReflectionClass($className)
    {
        if (isset($this->classReflections[$className])) {
            return $this->classReflections[$className];
        }

        $class = new \ReflectionClass($className);
        $this->classReflections[$className] = $class;

        return $class;
    }

    /**
     * Get class version annotation for a class name.
     *
     * @param string $className Full class name.
     *
     * @return string
     *
     * @throws Exception\NotVersionedException If object is not versioned.
     */
    public function getClassVersion($className)
    {
        if (isset($this->classVersions[$className])) {
            return $this->classVersions[$className];
        }

        $class = $this->getReflectionClass($className);

        $versionAnnotation = $this->reader->getClassAnnotation(
            $class,
            'Evispa\ObjectMigration\Annotations\Version'
        );

        if (null === $versionAnnotation) {
            throw new Exception\NotVersionedException($className);
        }

        $version = $versionAnnotation->version;

        $this->classVersions[$className] = $version;

        return $version;
    }

    /**
     *
     * @param string $className
     *
     * @return MethodInfo[]
     */
    public function getClassMigrationMethodInfo($className)
    {
        $class = $this->getReflectionClass($className);
        $methods = $class->getMethods(\ReflectionMethod::IS_PUBLIC);

        $annotations = array();

        foreach ($methods as $method) {
            $migrationAnnotation = $this->reader->getMethodAnnotation(
                $method,
                'Evispa\ObjectMigration\Annotations\Migration'
            );

            if (null === $migrationAnnotation) {
                continue;
            }

            $action = null;

            if (null !== $migrationAnnotation->from) {
                if (false === $method->isStatic() || 2 !== $method->getNumberOfParameters()) {
                    throw new \LogicException(
                        'Method "' . $method->getName(
                        ) . '" in "' . $className . '" should be static and require 2 parameters.'
                    );
                }

                if ($migrationAnnotation->from === $className) {
                    throw new \LogicException(
                        'Method "' . $method->getName(
                        ) . '" in "' . $className . '" should have a migration from a different class.'
                    );
                }

                $action = new CreateAction($method);

            } elseif (null !== $migrationAnnotation->to) {
                if (true === $method->isStatic() || 1 !== $method->getNumberOfParameters()) {
                    throw new \LogicException(
                        'Method "' . $method->getName(
                        ) . '" in "' . $className . '" should not be static and require 1 parameter.'
                    );
                }

                if ($migrationAnnotation->to === $className) {
                    throw new \LogicException(
                        'Method "' . $method->getName(
                        ) . '" in "' . $className . '" should have a migration to a different class.'
                    );
                }

                $action = new CloneAction($method);

            }

            $annotations[] = new MethodInfo($action, $migrationAnnotation);
        }

        return $annotations;
    }

    /**
     * Search for class name by version id
     *
     * @param $startFromClassName
     * @param $version
     *
     * @return null|string
     */
    public function getClassNameByVersion($startFromClassName, $version)
    {
        $visited = array();

        return $this->findClassNameByVersion($startFromClassName, $version, $visited);
    }

    /**
     * Search for class name by version id
     *
     * @param string $startFromClassName
     * @param string $version
     * @param array  $visited
     *
     * @return null|string
     */
    private function findClassNameByVersion($startFromClassName, $version, &$visited = array())
    {
        $versionAnnotation = $this->reader->getClassAnnotation(
            new \ReflectionClass($startFromClassName),
            'Evispa\ObjectMigration\Annotations\Version'
        );

        if ($versionAnnotation->version === $version) {
            return $startFromClassName;
        }

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

        return null;
    }

    /**
     * Get migrations for a class.
     *
     * @param string $className
     *
     * @return MigrationMethods
     *
     * @throws \LogicException
     */
    public function getClassMigrationMethods($className)
    {
        if (isset($this->classMigrationMethods[$className])) {
            return $this->classMigrationMethods[$className];
        }

        $migrationAnnotations = $this->getClassMigrationMethodInfo($className);

        $migrationMethods = new MigrationMethods();

        foreach ($migrationAnnotations as $migrationAnnotation) {
            $method = $migrationAnnotation->method;
            $migrationAnnotation = $migrationAnnotation->annotation;

            if (null !== $migrationAnnotation->from) {
                if (false === $method->isStatic() || 2 !== $method->getNumberOfParameters()) {
                    throw new \LogicException(
                        'Method "' . $method->getName(
                        ) . '" in "' . $className . '" should be static and require 2 parameters.'
                    );
                }

                if ($migrationAnnotation->from === $className) {
                    throw new \LogicException(
                        'Method "' . $method->getName(
                        ) . '" in "' . $className . '" should have a migration from a different class.'
                    );
                }

                $otherClass = $migrationAnnotation->from;
                $otherClassVersion = $this->getClassVersion($otherClass);
                $migrationMethods->from[$otherClassVersion] = new CreateAction($method);

            } elseif (null !== $migrationAnnotation->to) {
                if (true === $method->isStatic() || 1 !== $method->getNumberOfParameters()) {
                    throw new \LogicException(
                        'Method "' . $method->getName(
                        ) . '" in "' . $className . '" should not be static and require 1 parameter.'
                    );
                }

                if ($migrationAnnotation->to === $className) {
                    throw new \LogicException(
                        'Method "' . $method->getName(
                        ) . '" in "' . $className . '" should have a migration to a different class.'
                    );
                }

                $otherClass = $migrationAnnotation->to;
                $otherClassVersion = $this->getClassVersion($otherClass);
                $migrationMethods->to[$otherClassVersion] = new CloneAction($method);

            }
        }

        $this->classMigrationMethods[$className] = $migrationMethods;

        return $migrationMethods;
    }

    public function getRequiredClassOptions($className)
    {
        $requiredOptions = array();

        $scanList = array($className => true);

        $scannedSourceNames = array();

        while (true) {
            if (0 === count($scanList)) {
                break;
            }

            // find new class names
            foreach ($scanList as $scanName => $_) {
                $annotations = $this->getClassMigrationMethodInfo($scanName);
                foreach ($annotations as $annotationInfo) {
                    if (null !== $annotationInfo->annotation->from) {
                        $newClass = $annotationInfo->annotation->from;
                        foreach ($annotationInfo->annotation->require as $requiredOption) {
                            $requiredOptions[$requiredOption] = array('from' => $newClass, 'to' => $scanName);
                        }
                    } elseif (null !== $annotationInfo->annotation->to) {
                        $newClass = $annotationInfo->annotation->to;
                        foreach ($annotationInfo->annotation->require as $requiredOption) {
                            $requiredOptions[$requiredOption] = array('from' => $scanName, 'to' => $newClass);
                        }
                    }

                    if (!isset($scannedSourceNames[$newClass])) {
                        $scanList[$newClass] = true;
                    }
                }

                unset($scanList[$scanName]);

                if (!isset($scannedSourceNames[$scanName])) {
                    $scannedSourceNames[$scanName] = true;
                }
            }
        }

        return $requiredOptions;
    }

}