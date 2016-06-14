<?php
namespace Cilibs\Core\Model;

require_once(dirname(__FILE__) . "/DataBase.php");

class Model
{

    protected $_id;            //identify a instance;usually a key of a table
    static private $database;    //only one instance and connect only once

    /**
     * @var DataBase
     */
    protected $db;            // reference to $database used in every childclass
    public $data;                //data Obj
    public $array;            //store data from corresponding	database
    protected $tableName;        //name of corresponding table
    protected $className = "Model";
    static protected $deepth;
    static public $config;    //db config

    public static function getDB($host, $username, $pwd, $db)
    {
        static $dbs = array();
        $key = $host . "_" . $db;

        if (!isset($dbs[$key])) {
            $dbs[$key] = new DataBase();
            $dbs[$key]->config($host, $username, $pwd);
            $dbs[$key]->connect($db);
        }

        return $dbs[$key];
    }

    function getLink()
    {
        return $this->db->link;
    }

    function __construct($db)
    {
        $this->db = $db;
    }

    function SetTableName($name)
    {
        $this->tableName = $name;
    }

    function SetConfig($config)
    {
        self::$config = $config;
    }

    function __get($name)
    {

        if (isset($this->array[0][$name])) {
            return $this->array[0][$name];
        } else {
            return $this->{$name};
        }
    }

	private function processCondition($condition)
    {
        if (is_array($condition)) {
            $s = array();
            foreach ($condition as $key => $val) {
                if (is_array($val)) {
                    $tmp = array();
                    foreach ($val as $v) {
                        $tmp[] = addslashes($v);
                    }
                    $s[] = "`{$key}` in ('" . join("','", $tmp) . "')";
                } else {
					preg_match("/^(.*)([!><]={0,1})$/i", $key, $out);
					if (isset($out[1])) {
							$key = trim($out[1]);
							$op = ($out[2] == "!") ? "!=" : $out[2];
					} else {
							$op = "=";
					}
                    $val = addslashes($val);
                    $s[] = "`{$key}` {$op} '{$val}'";
                }
            }
            $condition = join(" and ", $s);
        }

        return $condition;
    }

    function __call($funName, $argv)
    {
        global $debugMessage;

        //处理 getBy_****()函数

        if (strtolower(substr($funName, 0, 6)) == "getby_") {
            $fieldName = substr($funName, 6);
            if (isset($argv[1])) {
                $page = $argv[2];
                $num = $argv[3] ? $argv[3] : 5;
                $order = $argv[1];
                $this->array = $this->db->findAllByPage($this->tableName, "`$fieldName`='{$argv[0]}' $order",
                    '*', $page, $num);
            } else {
                $this->array = $this->db->findAll($this->tableName, "`$fieldName`='{$argv[0]}'");
            }

            if (count($this->array) == 1 && isset($this->array[0]['id'])) {
                //				$this->array = $this->array[0];

                $this->_id = $this->array[0]['id'];
            }

            if (DEBUG) {
                //$debugMessage.= "<!--".microtime()." $this->className.$funName()-->\n";
            }

            return $this->array[0];
        }
        //getNUmby_*
        if (substr($funName, 0, 9) == "getNumBy_") {
            $fieldName = substr($funName, 9);
            //                        strtolower($fieldName);                                      //zhoukeli 07-31-2007
            if (isset($argv[1])) {
                $condition = $argv[1];
            } else {
                $condition = '';
            }
            $num = $this->db->getNum($this->tableName, "$fieldName='{$argv[0]}' $condition");

            if (DEBUG) {
                //$debugMessage.= "<!--".microtime()." $this->className.$funName()-->\n";
            }

            return $num;
        }

        if (DEBUG && !method_exists($this, '_' . $funName)) {
            die("call to undefined method : {$funName}");
        }

        $r = $this->{'_' . $funName}($argv);

        return $r;
    }

    function getTableName()
    {
        return $this->tableName;
    }

    function Exist($condition)
    {
        return $this->db->isItemExist($this->tableName, $condition);
    }

    private function _set($arg)
    {
        $id = $arg[0];
        $this->_id = (int)$id;
    }

    function haveData()
    {
        if (count($this->array) > 0) {
            return true;
        }
        return false;
    }

    function getby($condition)
    {
        $data = $this->getAllByPage(1, 1, $condition);
        return $data[0];
    }

    function getAllByPage($page, $limit, $condition, $order = "", $output_index = null)
    {
        $page = (int)$page;            //current page
        $limit = (int)$limit;            //number per page
        $condition = $this->processCondition($condition);
		
        $data = $this->db->findAllByPage($this->tableName,
            $condition . " " . $order, '*', $page, $limit
        );

		if ($output_index) {
			$arr = array();
			foreach($data as $val) {
				$key = $val[$output_index];
				$arr[$key] = $val;
			}
			return $arr;
		}

        return $data;
    }

    private function _query($arg)
    {
        $sql = $arg[0];
        $result = $this->db->findBySql($sql);
        //if(!mysql_affected_rows())
        //	return false;
        //elseif(!$result)
        //	$result = 1;
        return $result;
    }

    private function _insert($arg)
    {
        $arr = $arg[0];
        $this->db->insert($this->tableName, $arr);
        return mysql_insert_id($this->getLink());
    }

    private function _update($arg)
    {
        $condition = $this->processCondition($arg[0]);

        if (preg_match('/^[[:space:]]*$/', $condition)) {
            return false;
        }
        $arr = $arg[1];
        $increment = $arg[2] ? $arg[2] : false;
        $r = $this->db->update($this->tableName, $condition, $arr, $increment);
		if ($r) {
			return mysql_affected_rows($this->getLink());
		}
		return 0;
    }

    /**
     * @param $data
     * @param null $update 为null表示更新其中所有字段
     * @param null $increament
     * @return int
     */
    public function insertupdate($data, $update = null, $increament = null)
    {
        $this->db->insertupdate($this->tableName, $data, $update, $increament);
        return mysql_affected_rows($this->getLink());
    }

    private function _delete($arg)
    {
        $condition = $this->processCondition($arg[0]);

        $flag = $this->db->exeSql("delete from $this->tableName where $condition");
        if ($flag && mysql_affected_rows($this->getLink()) == 0) {
            return false;
        }
        return $flag;
    }

    private function _getNum($arg)
    {
        $condition = $this->processCondition($arg[0]);

        return $this->db->getNum($this->tableName, $condition);
    }

    private function _getMaxId()
    {
        $r = $this->db->findBySql(
            "select id from $this->tableName order by id desc limit 1");
        return $r[0]['id'];

    }

    private function affectRows()
    {
        return mysql_affected_rows($this->getLink());
    }

    static function GetIns($tablename)
    {
        static $mods = array();
        if (isset($mods[$tablename])) {
            return $mods[$tablename];
        }

        $mods[$tablename] = new Model;
        $mods[$tablename]->SetTableName($tablename);
        return $mods[$tablename];
    }

    private function DumpDebugMes()
    {
        global $debugMessage;
        file_put_contents("/tmp/debug.log", $debugMessage, true);
        var_dump($debugMessage);
    }
}

?>