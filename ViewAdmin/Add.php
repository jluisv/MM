<?php

/**
 *  create the editor
 */
class Ragtek_MM_ViewAdmin_Add extends XenForo_ViewAdmin_Base{


    public function renderHtml()
    {
        $this->_params['editorTemplate'] = XenForo_ViewPublic_Helper_Editor::getEditorTemplate(
            $this, 'reply'
        );
    }
}