<?php
namespace Yong\Doctrine\Model\Doctrine;

use Yong\Doctrine\Model\Doctrine;

class Model
{
    use TraitBase;

    public $connection = 'default';
    public $primaryKey = 'id';
    public $timestamps = false;

    protected static function _retrieveConnection($self = null) {
        if (!$self) {
            $self = new static();
        }
        return Doctrine::getInstance()->getConnection($self->connection);
    }

    public function __construct(array $data=[]) {
        $this->fill($data);
    }

    /**
     * save model to db
     */
    public function save() {
        $this->_saveTimestamps();
        $connection = static::_retrieveConnection($this);
        $connection->persist($this);
        $connection->flush();
        return true;
    }

    private function _saveTimestamps() {
        if (!$this->timestamps) {
            return;
        }
        if (empty($this->{$this->primaryKey})) {
            $this->created_at = gmdate('Y-m-d H:i:s', time());
            $this->updated_at = $this->created_at;
        } else {
            $this->updated_at = gmdate('Y-m-d H:i:s', time());
        }
    }

    public function update() {
        $this->_saveTimestamps();
        $connection = static::_retrieveConnection($this);
        $connection->merge($this);
        $connection->flush();
        return true;
    }

    public function delete() {
        $connection = static::_retrieveConnection($this);
        $connection->remove($this);
        $connection->flush();
        return true;
    }

    public function fill($data) {
        foreach($data as $key => $value) {
            $this->{$key} = $value;
        }
    }

    public function toArray() {
        return get_object_vars($this);
	}
}