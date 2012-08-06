<?php

class Ragtek_MM_StaticMethods
{

    /**
     *
     * @event load_class_controller
     *
     * @static
     * @param $name
     * @param $extend
     */
    public static function controllerListener($name, &$extend)
    {
        if ($name == 'XenForo_ControllerPublic_Thread') {
            $extend[] = 'Ragtek_MM_Extend_ControllerPublic_Thread';
        }
    }


    /**
     *
     * @event template_create
     *
     * precache the template to save 1 query if there are no other uncached templates
     * @static
     * @param $templateName
     * @param array $params
     * @param XenForo_Template_Abstract $template
     */
    public static function preCacheTemplate($templateName, array &$params, XenForo_Template_Abstract $template)
    {
        if ($templateName == 'thread_view') {
            $template->preloadTemplate('ragtek_mm_multimod');
            $template->preloadTemplate('preview_tooltip');
        }

        ##additonalPreCache##

    }

    /**
     * insert the new template on the hook
     *
     * @event template_hook
     *
     * @static
     * @param $name
     * @param $contents
     * @param array $params
     * @param XenForo_Template_Abstract $template
     */
    public static function templateHooks($name, &$contents, array $params, XenForo_Template_Abstract $template)
    {
        if ($name == 'thread_view_pagenav_before' && $template->getParam('show_multimods')) {
            $contents .= $template->create('ragtek_mm_multimod', $template->getParams());
        }

        ##additionalHook##

    }


    public static function fileHashes(XenForo_ControllerAdmin_Abstract $controller, array &$hashes)
    {
        $fileHashes = Ragtek_MM_Hashes::getHashes();
        $hashes += $fileHashes;
    }

    CONST ADDON_ID = 'ragtekMM';
}