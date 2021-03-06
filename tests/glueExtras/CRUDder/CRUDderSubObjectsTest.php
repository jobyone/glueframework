<?php
/**
  * Glue Framework
  * Copyright (C) 2015 Joby Elliott
  *
  * This program is free software; you can redistribute it and/or modify
  * it under the terms of the GNU General Public License as published by
  * the Free Software Foundation; either version 2 of the License, or
  * (at your option) any later version.
  *
  * This program is distributed in the hope that it will be useful,
  * but WITHOUT ANY WARRANTY; without even the implied warranty of
  * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
  * GNU General Public License for more details.
  *
  * You should have received a copy of the GNU General Public License along
  * with this program; if not, write to the Free Software Foundation, Inc.,
  * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */
namespace glueExtras\CRUDder\SubObjectsTest;

use \glueExtras\CRUDder\CRUDder;
use \glueExtras\CRUDder\DB;

class CRUDderSubObjectsTest extends \PHPUnit_Extensions_Database_TestCase
{
    public static $conn = null;
    protected static $boArray1 = array(
        'string' => 'Basic Object 1'
    );
    protected static $boArray2 = array(
        'string' => 'Basic Object 2'
    );

    public function testCreateSubObjects()
    {
        $bo1 = BasicObject::create(static::$boArray1);
        $bo2 = BasicObject::create(static::$boArray2);
        $ma1 = MasterObject::create(array(
            'string'=>'Some string',
            'child1'=>$bo1,
            'child2'=>$bo2
        ));
        $this->assertEquals($bo1->string,$ma1->child1->string);
        $this->assertEquals($bo2->string,$ma1->child2->string);
        //test creation with null and nonexistent subobjects
        $ma2 = MasterObject::create(array(
            'string'=>'Some other string',
            'child1'=>null
        ));
        $this->assertNull($ma2->child1);
        $this->assertNull($ma2->child2);
    }

    public function testRemoveSubObjects()
    {
        $bo1 = BasicObject::create(static::$boArray1);
        $bo2 = BasicObject::create(static::$boArray2);
        $ma1 = MasterObject::create(array(
            'string'=>'Some string',
            'child1'=>$bo1,
            'child2'=>$bo2
        ));
        //remove first child
        $ma1->child1 = null;
        $ma1->update();
        $ma1 = MasterObject::read(1);
        $this->assertNull($ma1->child1);
        //remove second child
        $ma1->child2 = null;
        $ma1->update();
        $ma1 = MasterObject::read(1);
        $this->assertNull($ma1->child2);
    }

    public function testUpdateSubObjects()
    {
        $bo1 = BasicObject::create(static::$boArray1);
        $ma1 = MasterObject::create(array(
            'string'=>'Some other string',
            'child1'=>$bo1
        ));
        $bo1 = $ma1->child1;
        $bo1->string = 'Updated basic object';
        $bo1->update();
        //check that it was updated
        $bo1 = BasicObject::read(1);
        $this->assertEquals('Updated basic object', $bo1->string);
    }

    /**
    * @return PHPUnit_Extensions_Database_DB_IDatabaseConnection
    */
    public function getConnection() {
        $pdo = DB::getConnection('sqlite::memory:', null, null);
        //BasicObject schema
        $pdo->exec('DROP TABLE IF EXISTS BasicObject');
        $pdo->exec('CREATE TABLE BasicObject (
            bo_id INTEGER PRIMARY KEY AUTOINCREMENT,
            bo_string VARCHAR(30) NOT NULL
        )');
        static::$conn = &$pdo;
        //MasterObject schema
        $pdo->exec('DROP TABLE IF EXISTS MasterObject');
        $pdo->exec('CREATE TABLE MasterObject (
            mo_id INTEGER PRIMARY KEY AUTOINCREMENT,
            mo_childA INTEGER,
            mo_childB INTEGER,
            mo_string VARCHAR(30)
        )');
        static::$conn = &$pdo;
        //return
        return $this->createDefaultDBConnection(static::$conn);
    }
    /**
    * @return PHPUnit_Extensions_Database_DataSet_IDataSet
    */
    public function getDataSet() {
        return new \PHPUnit_Extensions_Database_DataSet_DefaultDataSet();
    }
}

Class TestCRUDder extends CRUDder {
    protected static $conn;
}
TestCRUDder::configureDB('sqlite::memory:', null, null);

class MasterObject extends TestCRUDder
{
    protected static $config = array();
    protected static $formatter;
}
MasterObject::configureClass(file_get_contents(__DIR__ . '/MasterObject.yaml'));

class BasicObject extends TestCRUDder
{
    protected static $config = array();
    protected static $formatter;
}
BasicObject::configureClass(file_get_contents(__DIR__ . '/BasicObject.yaml'));
