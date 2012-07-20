<?php

/**
 *   overwrite original XenForo_ControllerPublic_Thread class
 */
class Ragtek_MM_Extend_ControllerPublic_Thread extends
    #XenForo_ControllerPublic_Thread
    XFCP_Ragtek_MM_Extend_ControllerPublic_Thread
{

    /**
     * check if the user is allowed to use the multimoderation tool and if there are available multimoderations for this forum
     *
     * @return XenForo_ControllerResponse_View
     */
    public function actionIndex()
    {
        /** @var $parentReturn  XenForo_ControllerResponse_View */
        $parentReturn = parent::actionIndex();

        if ($parentReturn instanceof XenForo_ControllerResponse_View
            && $this->_getMultimodModel()->canUseMultiModeration($parentReturn->params['forum'])
            ) {
            $parentReturn->params += array(
                'multimods' => $this->_getMultimodModel()->getAvailableMultiMods($parentReturn->params['forum'])
            );
        }
        return $parentReturn;
    }

    /**
     * run multi moderation
     *
     * @return XenForo_ControllerResponse_Redirect|XenForo_ControllerResponse_View
     * @throws XenForo_Exception
     */
    public function actionRunMultiMod()
    {
        $multiModId = $this->_input->filterSingle('mod_id', XenForo_Input::UINT);
        $threadId = $this->_input->filterSingle('thread_id', XenForo_Input::UINT);

        $ftpHelper = $this->getHelper('ForumThreadPost');
        list($thread, $forum) = $ftpHelper->assertThreadValidAndViewable($threadId);

        if (!$this->_getMultimodModel()->canUseMultiModeration($forum)) {
            throw new XenForo_Exception('nothing to do here');
        }

        $multiMod = $this->_getMultimodOrError($multiModId);

        if ($this->isConfirmedPost()) {
            $this->_getMultimodModel()->runMultiMod($thread, $multiMod);
            return $this->responseRedirect(
                XenForo_ControllerResponse_Redirect::SUCCESS,
                XenForo_Link::buildPublicLink('threads', $thread)
            );
        } else {

            $viewParams = array(
                'thread' => $thread,
                'forum' => $forum,
                'nodeBreadCrumbs' => $ftpHelper->getNodeBreadCrumbs($forum),
                'multimod' => $multiMod
            );
            return $this->responseView('Ragtek_MM_Thread_MultiMod', 'ragtek_mm_confirm', $viewParams);

        }
    }

    /**
     * Gets the specified multimod or throws an error.
     *
     * @param $id
     * @param array $fetchOptions
     * @return array
     * @throws XenForo_ControllerResponse_Exception
     */
    protected function _getMultimodOrError($id, array $fetchOptions = array())
    {
        $info = $this->_getMultimodModel()->getMultimodById($id, $fetchOptions);

        if (empty($info)) {
            throw $this->responseException($this->responseError("Multimod not found", 404)); // TODO phrase
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
}