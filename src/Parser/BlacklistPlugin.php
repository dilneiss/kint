<?php

namespace Kint\Parser;

use Kint\Object\BasicObject;
use Kint\Object\InstanceObject;

class BlacklistPlugin extends Plugin
{
    /**
     * List of classes and interfaces to blacklist.
     *
     * @var array
     */
    public static $blacklist = array();

    /**
     * List of classes and interfaces to blacklist except when dumped directly.
     *
     * @var array
     */
    public static $shallow_blacklist = array();

    public function getTypes()
    {
        return array('object');
    }

    public function getTriggers()
    {
        return Parser::TRIGGER_BEGIN;
    }

    public function parse(&$var, BasicObject &$o, $trigger)
    {
        foreach (self::$blacklist as $class) {
            if ($var instanceof $class) {
                return $this->blacklist($var, $o);
            }
        }

        if ($o->depth <= 0) {
            return;
        }

        foreach (self::$shallow_blacklist as $class) {
            if ($var instanceof $class) {
                return $this->blacklist($var, $o);
            }
        }
    }

    protected function blacklist(&$var, &$o)
    {
        $object = $o->transplant(new InstanceObject());
        $object->classname = get_class($var);
        $object->hash = spl_object_hash($var);
        $object->clearRepresentations();
        $object->value = null;
        $object->size = null;
        $object->hints[] = 'blacklist';

        $o = $object;

        $this->parser->haltParse();

        return;
    }
}
