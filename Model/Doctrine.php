<?php

namespace Yong\Doctrine\Model;

use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;

class Doctrine {
    private $loader;
    private $dbconfigs;
    private $connections = [];

    private $modelPaths = [];
    private static $doctrine;
    public static function getInstance() {
        if (!self::$doctrine) {
            self::$doctrine = new static();
        }
        return self::$doctrine;
    }

    /**
     * prepare model path, and get connection definition from
     */
    public function __construct() {
        $dir = \Magento\Framework\App\ObjectManager::getInstance()->get(
            '\Magento\Framework\Filesystem\DirectoryList'
        );
        $this->loader = new \Doctrine\Common\ClassLoader("Doctrine");
        $this->loader->register();
        $env = require($dir->getPath('etc') . '/env.php');
        $this->dbconfigs = $env['db']['connection'];
        $this->addModelPath(__FILE__, true);
    }

    public function addModelPath($path, $isFile = false) {
        if ($isFile) {
            $path = dirname($path);
        }
        if (!in_array($path, $this->modelPaths)) {
            $this->modelPaths[] = $path;
            foreach($this->connections as &$connection) {
                $connection[1]->setMetadataDriverImpl($connection[1]->newDefaultAnnotationDriver($this->modelPaths, true));
            }
        }
    }

    public function getLoader() {
        return $this->loader;
    }
  
    // $dbParams = array(
    //     'driver' => 'pdo_mysql',
    //     'user' => 'root',
    //     'password' => '',
    //     'dbname' => 'tests'
    // );
    public function getConnection($connection='default') {
        if (empty($connection)) {
            $connection = 'default';
        }
        if (!isset($this->connections[$connection])) {
            if (!isset($this->dbconfigs[$connection])) {
                throw new \Exception(sprintf('Database connection[%s] is not defined in app/etc/doctrine.php', $connection));
            }
            $config = Setup::createAnnotationMetadataConfiguration($this->modelPaths, true);
            $db_conf = $this->dbconfigs[$connection];
            $db_conf['driver'] = 'pdo_mysql';
            $db_conf['user'] = (isset($db_conf['user']) ? $db_conf['user'] : $db_conf['username']);
            $this->connections[$connection] = [EntityManager::create($db_conf, $config), $config];
        }
        return $this->connections[$connection][0];
    }
}