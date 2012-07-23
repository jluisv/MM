<?php

class Ragtek_MM_Install
{

    CONST ADDON_PRIMARY_TABLE = 'xf_r_multimod';


    public function _installVersion10(){
        $query['add_index'] = "ALTER TABLE  " . self::ADDON_PRIMARY_TABLE . " ADD INDEX  `title` (  `title` )";
        $this->_runQuery($query);
    }

    public function _installVersion4()
    {
        self::addColumn(self::ADDON_PRIMARY_TABLE, 'topic_state', "VARCHAR( 25 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT  'leave'");
        self::addColumn(self::ADDON_PRIMARY_TABLE, 'close_thread', "TINYINT( 3 ) UNSIGNED NOT NULL DEFAULT  '0'");
    }

    public function _installVersion1()
    {
        $query['xf_r_multimod'] = sprintf("
           CREATE TABLE IF NOT EXISTS %s (
              `multimod_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `title` varchar(50) NOT NULL,
              `topic_pin` varchar(25) NOT NULL,
              `title_start` varchar(25) NOT NULL,
              `title_end` varchar(25) NOT NULL,
              `move_to_node` int(10) unsigned NOT NULL DEFAULT '0',
              `set_thread_prefix` int(10) unsigned NOT NULL DEFAULT '0',
              `add_reply` tinyint(3) NOT NULL DEFAULT '0',
              `active_nodes` text NOT NULL,
              `reply` mediumtext NOT NULL,
              PRIMARY KEY (`multimod_id`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;
           ",self::ADDON_PRIMARY_TABLE);
        $this->_runQuery($query);
    }

    public static function uninstall()
    {
        $query['drop'] = sprintf("
            DROP TABLE %s
            ", self::ADDON_PRIMARY_TABLE);
        self::_runQuery($query);
    }


    public static function install($existingAddOn, $addOnData)
    {
        $startVersionId = 1;
        $endVersionId = $addOnData['version_id'];

        if ($existingAddOn) {
            $startVersionId = $existingAddOn['version_id'] + 1;
        }
        $install = self::getInstance();

        for ($i = $startVersionId; $i <= $endVersionId; $i++) {
            $method = '_installVersion' . $i;
            if (method_exists($install, $method) === false) {
                continue;
            }
            $install->$method();
        }
    }

    public static function _runQuery(array $queries)
    {
        $db = XenForo_Application::getDb();
        foreach ($queries AS $id => $query) {
            $db->query($query);
        }
    }

    static private $instance = null;

    public static function getInstance()
    {

        if (self::$instance == null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public static function addColumn($table, $field, $attr)
    {
        if (!self::checkIfExist($table, $field)) {
            $db = XenForo_Application::get('db');
            return $db->query("ALTER TABLE `" . $table . "` ADD `" . $field . "` " . $attr);
        }
    }

    public static function checkIfExist($table, $field)
    {
        $db = XenForo_Application::get('db');
        if ($db->fetchRow('SHOW columns FROM `' . $table . '` WHERE Field = ?', $field)) {
            return true;
        } else {
            return false;
        }
    }


    protected static function checkIfAddonExists($addonId)
    {
        /** @var $addonModel    XenForo_Model_AddOn */
        $addonModel = XenForo_Model::create('XenForo_Model_AddOn');
        if (!$addonModel->getAddOnById($addonId)) {
            throw new XenForo_Exception('This addon requires ' . $addonId);
        }
        return true;
    }

    ##additional_install_methods##
}