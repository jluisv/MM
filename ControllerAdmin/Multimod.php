<?php

/**
 *  admin controller for Multimod
 */
class Ragtek_MM_ControllerAdmin_Multimod extends XenForo_ControllerAdmin_Abstract
{

    protected function _preDispatch($action)
    {
        parent::_preDispatch($action);
        $this->assertAdminPermission('ragtek_manage_multimod');
    }


    public function actionIndex()
    {
        $model = $this->_getMultimodModel();
        $multimods = $model->getAllMultimods();

        $viewParams = array(
            'multimods' => $multimods
        );

        return $this->responseView('Ragtek_MM_View_List', 'ragtek_multimod_list', $viewParams);
    }

    public function actionAdd()
    {
        $viewParams = array(
            'multimod' => array('active_nodes' => array()),
            'nodes' => $this->getModelFromCache('XenForo_Model_Node')->getViewableNodeList()
        );

        return $this->responseView('Ragtek_MM_ViewAdmin_Add', 'ragtek_multimod_edit', $viewParams);
    }


    public function actionEdit()
    {
        $id = $this->_input->filterSingle('multimod_id', XenForo_Input::UINT);

        $multimod = $this->_getMultimodOrError($id);

        $viewParams = array(
            'multimod' => $multimod,
            'nodes' => $this->getModelFromCache('XenForo_Model_Node')->getViewableNodeList()

        );

        return $this->responseView('Ragtek_MM_ViewAdmin_Edit', 'ragtek_multimod_edit', $viewParams);
    }

    public function actionSave()
    {
        $this->_assertPostOnly();

        $id = $this->_input->filterSingle('multimod_id', XenForo_Input::UINT);

        $dwInput = $this->_input->filter(array(
            'title' => XenForo_Input::STRING,
            'topic_pin' => XenForo_Input::STRING,
            'title_start' => XenForo_Input::STRING,
            'title_end' => XenForo_Input::STRING,
            'add_reply' => XenForo_Input::UINT,
            'reply' => XenForo_Input::STRING,
            'active_nodes' => array(XenForo_Input::UINT, 'array' => true),
            'move_to_node' => XenForo_Input::UINT,
            'set_thread_prefix' => XenForo_Input::UINT,
            'topic_state' => XenForo_Input::STRING,
            'close_thread' => XenForo_Input::UINT
        ));

        $input['reply'] = $this->getHelper('Editor')->getMessageText('reply', $this->_input);
        $dwInput['reply'] = XenForo_Helper_String::autoLinkBbCode($input['reply']);



        $dw = $this->_getMultimodDataWriter();
        if ($id) {
            $dw->setExistingData($id);
        }
        $dw->bulkSet($dwInput);
        $dw->save();

        return $this->responseRedirect(
            XenForo_ControllerResponse_Redirect::SUCCESS,
            XenForo_Link::buildAdminLink('multimods') . $this->getLastHash($dw->get('multimod_id'))
        );
    }


    public function actionDelete()
    {
        $id = $this->_input->filterSingle('multimod_id', XenForo_Input::UINT);
        $multimod = $this->_getMultimodOrError($id);

        if ($this->isConfirmedPost()) {
            return $this->_deleteData('Ragtek_MM_DataWriter_Multimod', $multimod, XenForo_Link::buildAdminLink('multimods'));
        } else {
            $viewParams = array(
                'multimod' => $multimod
            );

            return $this->responseView('Ragtek_MM_View_Delete', 'ragtek_multimod_delete', $viewParams);
        }
    }

    /**
     * returns multimod or throws an error
     *
     * @param $id
     * @param array $fetchOptions
     * @return array
     * @throws XenForo_ControllerResponse_Exception
     */
    protected function _getMultimodOrError($id, array $fetchOptions = array())
    {
        $info = $this->_getMultimodModel()->getMultimodById($id, $fetchOptions);
        $info = $this->_getMultimodModel()->prepareMultiMod($info);

        if (empty($info)) {
            throw $this->responseException($this->responseError(new XenForo_Phrase('ragtek_mm_not_found'), 404));
        }
        return $info;
    }


    /**
     * @return Ragtek_MM_Model_Multimod
     */
    protected function _getMultimodModel()
    {
        return $this->getModelFromCache('Ragtek_MM_Model_Multimod');
    }

    /**
     * @return Ragtek_MM_DataWriter_Multimod
     */
    protected function _getMultimodDataWriter()
    {
        return XenForo_DataWriter::create('Ragtek_MM_DataWriter_Multimod');
    }


    ##additionalContent##
}