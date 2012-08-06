<?php

/**
 *  data writer for Multimods
 */
class Ragtek_MM_DataWriter_Multimod extends XenForo_DataWriter
{

    protected function _getFields()
    {
        return array(
            'xf_r_multimod' => array(
                'multimod_id' => array('type' => self::TYPE_UINT, 'autoIncrement' => true),
                'title' => array('type' => self::TYPE_STRING, 'required' => true, 'maxLength' => 50),
                'topic_pin' => array('type' => self::TYPE_STRING, 'allowedValues' => array('leave', 'stick', 'unstick'), 'default' => 'leave'),
                'title_start' => array('type' => self::TYPE_STRING, 'default' => ''),
                'description' => array('type' => self::TYPE_STRING,'default' => ''),
                'title_end' => array('type' => self::TYPE_STRING, 'default' => ''),
                'move_to_node' => array('type' => self::TYPE_UINT, 'default' => 0),
                'topic_state' => array('type' => self::TYPE_STRING, 'allowedValues' => array('leave', 'visible', 'moderated', 'deleted'), 'default' => 'leave'),
                'set_thread_prefix' => array('type' => self::TYPE_UINT, 'default' => 0),
                'add_reply' => array('type' => self::TYPE_BOOLEAN, 'default' => 0),
                'active_nodes' => array('type' => self::TYPE_UNKNOWN),
                'reply' => array('type' => self::TYPE_STRING, 'default' => ''),
                'close_thread' => array('type' => self::TYPE_BOOLEAN, 'default' => 0)
            )
        );
    }

    protected function _getExistingData($data)
    {
        if (!$id = $this->_getExistingPrimaryKey($data, 'multimod_id')) {
            return false;
        }

        return array('xf_r_multimod' => $this->_getRagtek_MM_Model_Multimod()->getMultimodById($id));
    }

    protected function _getUpdateCondition($tableName)
    {
        return 'multimod_id = ' . $this->_db->quote($this->getExisting('multimod_id'));
    }

    public function set($field, $value, $tableName = '', array $options = null)
    {
        if ($field == 'active_nodes') {
            $value = implode(',', $value);
        }
        parent::set($field, $value, $tableName, $options);
    }


    /**
     * @return Ragtek_MM_Model_Multimod
     */
    protected function _getRagtek_MM_Model_Multimod()
    {
        return $this->getModelFromCache('Ragtek_MM_Model_Multimod');
    }
}