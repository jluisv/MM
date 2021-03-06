<?php

class Ragtek_MM_Route_PrefixAdmin_Multimods implements XenForo_Route_Interface {

    /**
     * @param $routePath
     * @param Zend_Controller_Request_Http $request
     * @param XenForo_Router $router
     * @return false|XenForo_RouteMatch
     */
	public function match($routePath, Zend_Controller_Request_Http $request, XenForo_Router $router) {
        $action = $router->resolveActionWithStringParam($routePath, $request, 'multimod_id');
		return $router->getRouteMatch('Ragtek_MM_ControllerAdmin_Multimod', $action, 'applications');
	}

    public function buildLink($originalPrefix, $outputPrefix, $action, $extension, $data, array &$extraParams)
    {
        return XenForo_Link::buildBasicLinkWithStringParam($outputPrefix, $action, $extension, $data, 'multimod_id');
    }
}