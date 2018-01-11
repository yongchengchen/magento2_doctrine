<!--
Categories = ["Magento"]
Description = "Doctrine ORM support for Magneto 2"
Tags = ["Development", "Magento 2"]
date = "2018-01-04T21:47:31-08:00"
title = "Use Doctrine in Magneto2"
-->

### Doctrine ORM support for Magneto2
[![Total Downloads](https://poser.pugx.org/yong/magento2_doctrine/d/total.svg)](https://packagist.org/packages/yong/magento2_doctrine)
[![Latest Stable Version](https://poser.pugx.org/yong/magento2_doctrine/v/stable.svg)](https://packagist.org/packages/yong/magento2_doctrine)
[![Latest Unstable Version](https://poser.pugx.org/yong/magento2_doctrine/v/unstable.svg)](https://packagist.org/packages/yong/magento2_doctrine)
[![License](https://poser.pugx.org/yong/magento2_doctrine/license.svg)](https://packagist.org/packages/yong/elasticsuit)
Github: (https://packagist.org/packages/yong/magento2_doctrine)

Our system has some tables for exist system which developed by Laravel, 

And we using Magento2 CE which not support multiple databases.

On the other side, Magento 2 encapsulates database model a little bit too complicated.

So we need a rapid solution to retrieve these data model from 3rd database, and we decided to use Doctrine ORM.


#### * It's packaged as Magento 2 Module, but you don't have to enable this module to use it.


### Installation

#### 1.Via Composer
run command:
```shell
composer require yong/magento2_doctrine
```

#### 2. Download by git
Download this repo to the path app/code/Yong/Doctrine
```shell
    cd root_path_of_magento2
    git clone https://github.com/yongchengchen/magento2_doctrine.git app/code/Yong/Doctrine
```

### Usage

#### 1. Config Database Connection

Edit root_path_of_magento2/app/etc/env.php

Add your database connection configuration to 'db.connection' node
```php
      'my_connection' =>
        array (
            'host' => 'mysql',
            'dbname' => 'mydb',
            'username' => 'root',
            'password' => 'root',
            'active' => '1',
        ),
```

#### 2. Define Model
Define a model for your table.

```php
<?php
namespace TestNameSpace;

class TestDoctrineModel extends \Yong\Doctrine\Model\Doctrine\Model
{
    public $connection = 'my_connection';   //define your connection
    public $primaryKey = 'id';              //define your primary Key
    public $timestamps = true;              //define if your table has timestamps(created_at and updated_at)

    /** @Id @Column(type="integer") **/
    protected $id;
    /** @Column(type="string") **/
    protected $name;
    /** @Column(type="integer") **/
    protected $foreign_key_id;              //For has many or has one
    /** @Column(type="string") **/
    protected $created_at;
    /** @Column(type="string") **/
    protected $updated_at;

    /**
     * relationship support
     */
    public function foreignitem() {
        // return $this->hasMany(OtherTestDoctrineModel::class, 'id', 'foreign_key_id');
        return $this->hasOne(OtherTestDoctrineModel::class, 'id', 'foreign_key_id');
    }
}
```

#### 3. Supported Model Features

##### 1. get/set property
Once you've defined fields, getter and setter is ready.

```php
$test = new TestDoctrineModel();
$test->setcreated_at('2018-01-01 00:00:00')
echo $test->getcreated_at()
```

##### 2. Relation ship support

So far it support hasOne and hasMany, you can define a relationship with hasOne and HasMany.

```php
function hasOne($extra_classname, $extra_field, $self_field = null) 
function hasMany($extra_classname, $extra_field, $self_field = null)
```

##### 2. Query builder combination

If you want to query your Doctrine Model, you can use your Doctrine directly. Here's an example.

```php
$collection = TestDoctrineModel::select(['id', 'name', 'foreign_key_id', 'created_at', 'updated_at'])
    ->where('foreign_key_id', 'in', [1])
    ->whereLike('name', '%test%')
    ->where('id', 'notIn', [1])
    ->orWhere('id', 'in', [2])
    ->get();

print_r($collection);       //collection is an array or row array

$item = TestDoctrineModel::find(1); //if found, it will return TestDoctrineModel instance which id =1

$collection = TestDoctrineModel::findAll('created_at', '2018-01-01 00:00:00');
//if found, it will return an anrray of TestDoctrineModel instances which is created at '2018-01-01 00:00:00'
```

##### 3. Update/Save/Fill data

1) If you enable timestamps, it will auto update timestamps.
```php
    public $timestamps = true;              //define if your table has timestamps(created_at and updated_at)
```

2) You can call 'function fill' to fill data.

```php
$test = new TestDoctrineModel();
$test->fill(['name'=>'test', 'foreign_key_id'=>2]);
```

3) Use update/delete/save
```php
$test = new TestDoctrineModel();
$test->fill(['name'=>'test', 'foreign_key_id'=>2]);
$test->save();

$test = TestDoctrineModel::find(1);
$test->fill(['name'=>'test', 'foreign_key_id'=>2]);
$test->update();
$test->delete();
```
