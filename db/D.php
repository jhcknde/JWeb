<?php
namespace Cilibs\Core\Model;

class D
{

    protected static $config;

    public static function SetConfig($config)
    {
        self::$config = $config;
    }

    /**
     * @param $tblname 表名
     * @param null $dbname 数据库,默认取第一个
     * @return Model
     */
    public static function B($tblname, $dbname = null)
    {
        if (!isset(static::$config)) {
            die("Error:[数据库尚未配置]\n");
        }
        static $mods = array();

        $cfgs = array();

        foreach (static::$config as $cfg) {
            $cfgs[$cfg[0]] = $cfg;
        }
        if ($dbname) {
            $dbcfg = $cfgs[$dbname];
        } else {
            $dbcfg = static::$config[0];
        }

        $key = $dbcfg[1] . "_" . $dbcfg[4] . "_" . $tblname;

        if (isset($mods[$key])) {
            return $mods[$key];
        }

        $mods[$key] = new \Cilibs\Core\Model\Model(\Cilibs\Core\Model\Model::getDB(
            $dbcfg[1], $dbcfg[2], $dbcfg[3], $dbcfg[4]));

        $mods[$key]->SetTableName($tblname);

        return $mods[$key];
    }
}

