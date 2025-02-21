<?php

class INSTALL_Application
{
    private static $classInstance;

    /**
     *
     * @return INSTALL_Application
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    protected function __construct()
    {

    }

    public function init( $dbReady )
    {
        PEEP_Auth::getInstance()->setAuthenticator(new PEEP_SessionAuthenticator());
        
        $router = PEEP::getRouter();
        $router->setBaseUrl(PEEP_URL_HOME);
        $uri = PEEP::getRequest()->getRequestUri();
        $router->setUri($uri);
        
        $router->setDefaultRoute(new INSTALL_DefaultRoute());
        
        include INSTALL_DIR_ROOT . 'init.php';
    }

    public function display( $dbReady )
    {
        $dispatchAttrs = PEEP::getRouter()->route();
        $controllerClass = $dispatchAttrs['controller'];

        /* @var $controller INSTALL_ActionController */
        $controller = new $controllerClass();
        $controller->init($dispatchAttrs, $dbReady);

        $params = array();
        if ( !empty($dispatchAttrs['vars']) )
        {
            $params[] = $dispatchAttrs['vars'];
        }

        call_user_func_array(array($controller, $dispatchAttrs['action']), $params);

        $template = $controller->getTemplate();
        if ( empty($template) )
        {
            $controllerName = PEEP::getAutoloader()->classToFilename($controllerClass, false);
            $template = INSTALL_DIR_VIEW_CTRL . $controllerName
                . '_'
                . UTIL_String::capsToDelimiter($dispatchAttrs['action'], '_') . '.php';

            $controller->setTemplate($template);
        }

        $content = $controller->render();

        $viewRenderer = INSTALL::getViewRenderer();

        $viewRenderer->assignVars(array(
            'pageBody' => $content,
            'pageTitle' => $controller->getPageTitle(),
            'pageHeading' => $controller->getPageHeading(),
            'pageSteps' => INSTALL::getStepIndicator()->render(),
            'pageStylesheetUrl' => INSTALL_URL_VIEW . 'style.css'
        ));

        echo $viewRenderer->render(INSTALL_DIR_VIEW . 'master_page.php');
    }

}

class INSTALL_DefaultRoute extends PEEP_DefaultRoute
{
    public function getDispatchAttrs( $uri )
    {
        return array(
            'controller' => 'INSTALL_CTRL_Error',
            'action' => 'notFound'
        );
    }
}