<?php

/**
 *    Model for Multimod
 */
class Ragtek_MM_Model_Multimod extends XenForo_Model
{

    /**
     * Gets the specified Multimod if it exists.
     *
     * @param $id
     * @param array $fetchOptions
     * @return array
     */
    public function getMultimodById($id, array $fetchOptions = array())
    {
        $query = "
            SELECT * FROM  xf_r_multimod
            WHERE  multimod_id = ?
            ";

        return $this->_getDb()->fetchRow($query, $id);
    }

    public function prepareMultiMod(&$multimod)
    {
        $multimod['active_nodes'] = explode(',', $multimod['active_nodes']);
        return $multimod;
    }

    /**
     * gets all Multimods
     *
     * @return array
     */
    public function getAllMultimods()
    {
        return $this->fetchAllKeyed('
			SELECT *
			FROM xf_r_multimod
			ORDER by title
		', 'multimod_id');
    }

    /**
     * returns the available multimods for the specific node
     *
     * @param array $conditions
     * @param array $fetchOptions
     * @return array
     */
    public function getAvailableMultiMods(array $conditions = array(), array $fetchOptions = array())
    {
        $forum = $conditions['node_id'];
        $sqlClauses = $this->prepareMultiModFetchOptions($fetchOptions);

        $query = '
          SELECT multimod.*
          FROM xf_r_multimod AS multimod
         ' . $sqlClauses['orderClause'];

        $multimods = $this->fetchAllKeyed($query, 'multimod_id');

        $multimods = $this->_filterNodes($multimods, $forum);
        return $multimods;
    }


    protected function _filterNodes(array $multimods, array $forum)
    {
        foreach ($multimods AS $id => $mod) {

            $nodes = explode(',', $mod['active_nodes']);
            if (!in_array($forum['node_id'], $nodes) AND !in_array(0, $nodes)) {
                unset($multimods[$id]);
            }
        }
        return $multimods;
    }

    /**
     * @deprecated since 1.0.1
     *
     * @param array $conditions
     * @return string
     */
    public function prepareMultiModCriteria(array $conditions)
    {
        $db = $this->_getDb();
        $sqlConditions = array();
        if (!empty($conditions['node_id'])) {
            $sqlConditions[] = 'multimod.active_nodes IN (0,' . $db->quote($conditions['node_id']) . ')';
        }

        return $this->getConditionsForClause($sqlConditions);
    }


    public function prepareMultiModFetchOptions(array $fetchOptions)
    {
        $selectFields = '';
        $joinTables = '';
        $orderBy = '';


        return array(
            'selectFields' => $selectFields,
            'joinTables' => $joinTables,
            'orderClause' => 'ORDER BY multimod.title'
        );
    }


    /**
     * @param array $thread
     * @param array $forum
     * @param array $multimod
     * @param string $errorPhraseKey
     * @param array $nodePermissions
     * @param array $viewingUser
     * @return bool
     */
    public function canUseMultiModeration(array $thread, array $forum, array $multimod = array(),&$errorPhraseKey = '',
                                          array $nodePermissions = null, array $viewingUser = null)
    {

        $this->standardizeViewingUserReferenceForNode($thread['node_id'], $viewingUser, $nodePermissions);

        if (!XenForo_Permission::hasPermission($viewingUser['permissions'],'general', 'ragtek_mm_canUseMultimod'))
        {
            return false;
        }
        return true;
    }

    /**
     * @param array $thread
     * @param array $multiMod
     */
    public function runMultiMod(array $thread, array $multiMod)
    {

        if ($multiMod['add_reply'] && $multiMod['reply'] != '') {
            if ($multiMod['reply_creator_user_id'] == 0){
                $postCreater = XenForo_Visitor::getInstance()->toArray();
            }
            else {
                $postCreater = $this->getModelFromCache('XenForo_Model_User')->getUserById($multiMod['reply_creator_user_id']);
            }
            Ragtek_MM_Helper::createPost($postCreater, $thread['thread_id'], $multiMod['reply']);
        }

        /** @var $dw    XenForo_DataWriter_Discussion_Thread */
        $dw = XenForo_DataWriter::create('XenForo_DataWriter_Discussion_Thread');
        $dw->setExistingData($thread['thread_id']);

        if ($multiMod['topic_pin'] != 'leave') {
            $dw->set('sticky', ($multiMod['topic_pin'] == 'stick') ? 1 : 0);
        }

        $threadTitle = $dw->get('title');

        if ($multiMod['title_start'] != '') {
            $threadTitle = $multiMod['title_start'] . ' ' . $threadTitle;
        }

        if ($multiMod['title_end'] != '') {
            $threadTitle = $threadTitle . $multiMod['title_end'];
        }
        $dw->set('title', $threadTitle);

        if ($multiMod['move_to_node']) {
            $dw->set('node_id', $multiMod['move_to_node']);
        }

        if ($multiMod['set_thread_prefix']) {
            $dw->set('prefix_id', $multiMod['set_thread_prefix']);
        }
        if ($multiMod['topic_state'] != 'leave') {
            $dw->set('discussion_state', $multiMod['topic_state']);
        }

        if ($multiMod['close_thread']) {
            $dw->set('discussion_open', 0);
        }

        XenForo_CodeEvent::fire('multimod_run', array($thread, $multiMod, &$dw));

        XenForo_Model_Log::logModeratorAction('thread', $thread, 'multimod', array('multimod_title' => $multiMod['title'],
                                                                                    'description' => $multiMod['description'])
        );

        $dw->save();
    }
}