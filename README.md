Overview
--------

Migrate objects to different versions based on version annotations.

For example, let's say we have two class versions, the second one modifies public field name from "slug" to "id":

```php
class V1
{
    public $slug;
}

class V2
{
    public $id;
}
```

We can then decorate these classes with version annotations, and create migration method to the new version:

```php
use Evispa\ObjectMigration\Annotations as Api;

/**
 * @Api\Version("object.v1")
 */
class V1
{
    public $slug;
}

/**
 * @Api\Version("object.v2")
 */
class V2
{
    public $id;

    /**
     * @Api\Migration(from="V1")
     */
    public static function fromV1(V1 $other) {
        $obj = new self();

        $obj->id = $other->slug;

        return $obj;
    }
}
```

Use VersionConverter tool to check object version or migrate object to another available version:

```php

use Doctrine\Common\Annotations\AnnotationReader;
use Evispa\ObjectMigration\VersionReader;
use Evispa\ObjectMigration\VersionConverter;

$converter = new VersionConverter(new VersionReader(new AnnotationReader()));

// create v1 object

$v1 = new V1();
$v1->slug = "TEST";

// migrate to another version

$v2 = $converter->migrate($v1, 'object.v2');

$this->assertTrue($v2 instanceof V2); // true
$this->assertEquals("TEST", $v2->id); // true
```

Requirements
------------

This library will use Doctrine/Common for annotation parsing.

Installation
------------
This library can be easily installed via composer

```bash
composer require evispa/object-migration
```

or just add it to your ``composer.json`` file directly.
