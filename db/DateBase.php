<?php
namespace Cilibs\Core\Model;

if (!defined("DEBUG")) {
    define("DEBUG", false);
}

class Result
{
    public $resultArray = array();  //for output
    public $resultData;   //for input

    function addData($data)
    {
    } //add input

    function fetchRow()
    {
    } //collate the input
}

class PearResult extends Result
{

    function addData($data)
    {
        $this->resultData = $data;
    }

    function fetchRow()
    {
        $this->resultArray = array();
        if (is_object($this->resultData)) {
            while ($row = $this->resultData->fetchRow(DB_FETCHMODE_ASSOC)) {
                $this->resultArray[] = $row;
            }
        }
    }
}

class AnotherResult extends Result
{

}

class DataBase
{
    public $db = null;
    public $result = null;
    public $errMsg = array();
    public $errNum = 0;
    public $userID = null;
    public $host = '';
    public $userName = '';
    public $passWord = '';
    public $DBName = '';
    public $link;

    function __construct()
    {
        $this->result = new PearResult();

    }

    function __destruct()
    {
        if ($this->db) {
            $this->db->disconnect();
        }
    }

    function config($host, $username, $password)
    {
        $this->host = $host;
        $this->userName = $username;
        $this->passWord = $password;
    }

    function connect($DBname)
    {
        if ($this->DBName == $DBname) {
            return true;
        }

        $this->link = mysql_connect($this->host, $this->userName, $this->passWord, true);

        if (!$this->link) {
            die("连接数据库({$this->host}) 出错, username({$this->userName})");
        }

        if (!mysql_select_db($DBname, $this->link)) {
            die("当前配置没有权限访问数据库{$DBname}, 或者数据库{$DBname}不存在");
        }

        mysql_query("set names utf8", $this->link);
    }


    function getLastError()
    {
        return mysql_error($this->link);
    }

    function WriteLog($str)
    {
        $r = date("Y-m-d H:i:s");
        $r .= "\t" . $str . "\n";
        file_put_contents("/tmp/v_dblog", $r, FILE_APPEND);
    }

    function exeSql($sql)
    { //return pear result
        global $debugMessage;

        if (DEBUG) {
            $debugMessage .= "<!--$sql-->" . "\n";
        }

        $resultData = mysql_query($sql, $this->link);

        if (!$resultData) {
            $debugMessage .= mysql_error($this->link) . "\n";
            return null;
        }

        if (is_bool($resultData)) {
            return $resultData;
        }

        $arr = array();
        while ($row = mysql_fetch_assoc($resultData)) {
            $arr[] = $row;
        }
        mysql_free_result($resultData);

        return $arr;
    }

    function getResultArray()
    {
        $this->result->fetchRow();
        return $this->result->resultArray;
    }

    function findAll($tableName, $condition = '', $field = '*')
    {
        if (!$tableName) {
            echo "empty tablename";
            return null;
        }
        $sql = "select $field from `$tableName` where $condition";

        return $this->exeSql($sql);
    }

    function findBySql($sql)
    {
        return $this->exeSql($sql);
    }

    function addFieldVal($tableName, $condition, $field, $val)
    {
        $sql = "update `$tableName` set `$field` = `$field` +'$val'
			where $condition";

        if ($this->exeSql($sql)) {

            return true;
        }

        return false;
    }

    function minusFieldVal($tableName, $condition, $field, $val)
    {
        $sql = "update `$tableName` set `$field` = `$field`-'$val'
			where $condition";
        if ($this->exeSql($sql)) {
            return true;
        }

        return false;

    }

    function setFieldVal($tableName, $condition, $field, $val)
    {
        $mval = addslashes($val);
        $sql = "update `$tableName` set `$field` ='$mval'
			where $condition";

        if ($this->exeSql($sql)) {
            return true;
        }

        return false;

    }

    function delete($tableName, $condition)
    {
        $sql = "delete from `$tableName` where $condition";
        if ($this->exeSql($sql)) {
            return true;
        }

        return false;

    }

    function insert($tableName, $data)
    {
        if (is_array($data) && $tableName) {
            $sql = "insert into `$tableName` (";
            foreach ($data as $key => $val) {
                $sql .= "`$key`,";
            }
            $sql = substr($sql, 0, -1) . ')';
            $sql .= " values ('";
            foreach ($data as $val) {
                $val = addslashes($val);
                $sql .= $val . "','";
            }
            $sql = substr($sql, 0, -2) . ")";
            if ($this->exeSql($sql)) {
                return true;
            }
        }
        return false;
    }

    function update($tableName, $condition, $data, $increament)
    {
        //echo $condition.'<br/>';
        //print_r($data);
        if (is_array($data) && $tableName) {
            $sql = "update `$tableName` set ";
            foreach ($data as $key => $val) {
                $val = addslashes($val);
                if ($increament && in_array($key, $increament)) {
                    $sql .= "`$key` = `$key` + '$val',";
                } else {
                    $sql .= "`$key` = '$val',";
                }
            }
            $sql = substr($sql, 0, -1) . ' ';
            $sql .= "where $condition";
            //var_dump($sql);
            //die();
            if ($this->exeSql($sql)) {
                return true;
            }
        }
        return false;
    }

    function insertupdate($tableName, $data, $update, $increament)
    {
        if (is_array($data) && $tableName) {
            $keys = array();
            foreach ($data as $key => $val) {
                $keys[] = "`$key`";
            }
            $key_str = join(',', $keys);
            $values = array();
            foreach ($data as $val) {
                $val = addslashes($val);
                $values[] = "'$val'";
            }
            $value_str = join(',', $values);
            $sql = "insert into `$tableName` ($key_str) value ($value_str)";
            $sql .= " on duplicate key update ";
            $sets = array();
            foreach ($data as $key => $val) {
                $val = addslashes($val);
                if ($increament && in_array($key, $increament)) {
                    $sets[] = "`$key`=`$key` + '$val'";
                } else {
                    if (!$update || ($update && in_array($key, $update))) {
                        $sets[] = "`$key`= '$val'";
                    }
                }
            }
            $sql .= join(',', $sets);

            if ($this->exeSql($sql)) {
                return true;
            }
        }
        return false;
    }

    function isItemExist($tableName, $condition)
    {
        //$sql = "select * from $tableName where $codition";
        $result = $this->findAll($tableName, $condition);
        if (empty($result)) {
            return false;
        }

        return true;
    }

    function getNum($tableName, $condition = ' 1 ')
    {
        if (!$condition) {
            $mycondition = '1';
        } else {
            $mycondition = $condition;
        }

        $data = $this->exeSql("select count(*) from `$tableName` where $mycondition");

        return $data[0]['count(*)'];
    }

    //read by page
    function findAllByPage($table, $condition, $field = '*', $page = 1, $numberPerPage = 10)
    {
        $elements = array();
        $page = ($page < 1) ? 1 : $page;
        $from = ($page - 1) * $numberPerPage;
        $perPage = $numberPerPage;
        $limit = " limit $from,$perPage";
        $condition = $condition . $limit;

        $elements = $this->findAll($table, $condition, $field);//---
        return $elements;
    }

    function getPageNum($table, $condition, $numberPerPage)
    {
        $field = "count(*)";
        //$elements=array();
        $pageNum = $this->findAll($table, $condition, $field);
        return ceil(intval($pageNum[0]['count(*)']) / $numberPerPage);
    }
}