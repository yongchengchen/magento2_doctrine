<?php
namespace Yong\Doctrine\Model\Doctrine;

use Yong\Doctrine\Model\Doctrine\Doctrine;

trait TraitBase
{
    public function __call($method, $argv) {
        $pre = substr($method, 0, 3);
        $extra = \strtolower(substr($method, 3));

        switch($pre) {
            case 'get':
                return $this->{$extra};
                break;
            case 'set':
                return $this->{$extra} = $argv[0];
                break;
            default:
                if (count($argv) > 0) {
                    $this->{$method} = $argv[0];
                    return $this;
                } else {
                    return $this->{$method};
                }
                break;
        }
    }

    /**
    * $id_or_Field, if this field set as field name, then second parameter will be the value
    * id_if_has_field
    **/
    public static function find($id_or_Fieldname, $id_if_has_field=null) {
        if ($id_if_has_field == null) {
            return static::_retrieveConnection()->find(static::class, $id_or_Fieldname);
        }
        $repository = static::_retrieveConnection()->getRepository(static::class);
        return $repository->findOneBy([$id_or_Fieldname => $id_if_has_field]);
    }

    public static function findAll($fieldName, $value) {
        $repository = static::_retrieveConnection()->getRepository(static::class);
        return $repository->findBy([$fieldName => $value]);
    }

    public static function where($field, $cond, $value) {
        $qb = static::from(static::class);
        $qb->where($field, $cond,  $value);
        return $qb;
    }

    public static function from($className) {
        $qb = new QueryBuilder(static::_retrieveConnection()->createQueryBuilder());
        return $qb->from($className);
    }

    public static function select(array $fields) {
        $qb = static::from(static::class)->select($fields);
        return $qb;
    }

    protected function hasMany($extra_classname, $extra_field, $self_field = null) {
        $connection = self::_retrieveConnection($this);
        $extraRepository = $connection->getRepository($extra_classname);
        $params = [];
        $params[$extra_field] = (empty($self_field) ? $this : $this->{$self_field});
        return $extraRepository->findBy(
            $params
        );
    }

    protected function hasOne($extra_classname, $extra_field, $self_field = null) {
        $connection = self::_retrieveConnection($this);
        $extraRepository = $connection->getRepository($extra_classname);
        $params = [];
        $params[$extra_field] = (empty($self_field) ? $this : $this->{$self_field});
        return $extraRepository->findOneBy(
            $params
        );
    }

    protected $_relations = [];
    public function _reloadRelation() {
        $this->_relations = [];
    }

    public function __get($name) {
        if (\method_exists($this, $name)) {
            if (!isset($this->_relations[$name])) {
                $this->_relations[$name] = $this->{$name}();
            }
            return $this->_relations[$name];
        }
        return null;
    }
}
