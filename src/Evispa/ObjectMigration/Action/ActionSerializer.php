<?php

namespace Evispa\ObjectMigration\Action;

/**
 * Description of ActionSerializer
 *
 * @author nerijus
 */
class ActionSerializer
{
    const ACTION_CREATE = 1;
    const ACTION_CLONE = 2;

    public static function serializeAction(MigrationActionInterface $action) {
        if ($action instanceof CloneAction) {
            return array(self::ACTION_CLONE, $action->getMethod()->class, $action->getMethod()->getName());
        } elseif ($action instanceof CreateAction) {
            return array(self::ACTION_CREATE, $action->getMethod()->class, $action->getMethod()->getName());
        }
        throw new \Exception('Unkown action '.  get_class($action));
    }

    /**
     * @param array $data
     *
     * @return MigrationActionInterface
     */
    public static function deserializeAction($data) {
        if (self::ACTION_CLONE === $data[0]) {
            return new CloneAction(new \ReflectionMethod($data[1], $data[2]));
        } elseif (self::ACTION_CREATE === $data[0]) {
            return new CreateAction(new \ReflectionMethod($data[1], $data[2]));
        }
        throw new \Exception('Can not deserialize "'.  $data[1] . '"" action.');
    }
}