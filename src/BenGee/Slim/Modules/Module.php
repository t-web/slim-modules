<?php

/**
 * SlimMod - Library to code modules based Slim apps development.
 *
 * @author Benjamin GILLET <bgillet@hotmail.fr>
 * @package \BenGee\Slim\Modules
 * @version 1.0.1
 *
 * MIT LICENSE
 *
 * Permission is hereby granted, free of charge, to any person obtaining
 * a copy of this software and associated documentation files (the
 * "Software"), to deal in the Software without restriction, including
 * without limitation the rights to use, copy, modify, merge, publish,
 * distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to
 * the following conditions:
 *
 * The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
 * LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
 * OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
 * WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

namespace BenGee\Slim\Modules;

/**
 * Abstract implementation of a Slim module.
 * Any module in modules base Slim apps must inherit this class.
 * @package BenGee\Slim\Modules
 * @author Benjamin GILLET <bgillet@hotmail.fr>
 * @version 1.0.1
 * @since 1.0.0
 */
abstract class Module
{
    /**
     * Flag to set module as default manager for '/' request.
     * @var bool
     */
    private $_default = false;

    /**
     * Reference to module's parent Slim application.
     * @var \BenGee\Slim\Modules\SlimMod
     */
    private $_app = null;

    /**
     * Module's name used as folder name for module's file storage and as root path for module's routes.
     * @var string
     */
    private $_name = null;

    /**
     * Test if the module manages '/' request.
     * @return bool 'true' if the module is the default manager for the '/' request else 'false'.
     */
    public function isDefault()
    {
        return $this->_default;
    }

    /**
     * Return module's parent Slim application.
     * @return \Slim\Slim Reference to the module's parent Slim application instance.
     */
    public function app() 
    {
        return $this->_app;
    }

    /**
     * Return module's name.
     * @return string Module's name.
     */
    public function name() 
    {
        return $this->_name;
    }

    /**
     * Constructor.
     * First, it sets internal variables.
     * Second, it references module's templates.
     * Third, it registers module's hooks.
     * Fourth, it registers module's routes.
     * @param \BenGee\Slim\Modules\SlimMod $app Reference to the module's parent Slim application.
     * @param string $name Module's name used as folder name for module's files location, module's templates namespace and module's routes prefix.
     * @param bool $default If 'true' then the module will handle the default '/' request.
     * @throws \ErrorException If $app is null or $name is null or empty.
     */
    public function __construct(\BenGee\Slim\Modules\SlimMod $app, $name, $default = false) 
    {
        $this->_default = !empty($default);
        if ($app == null) throw new \ErrorException("Module's parent Slim application cannot be null !");
        $this->_app = $app;
        if (empty($name) || !is_string($name)) throw new \ErrorException("Module's name cannot be null or empty !");
        $this->_name = $name;
        $slimModulesDir = $this->config('slim.dir.modules');
        // Append module's templates path to the renderer if it is Twig
        if ($app->view instanceof \BenGee\Slim\Twig\TwigView) 
        {
            $app->view->addTemplatesDirectory($slimModulesDir . DIRECTORY_SEPARATOR . $name . DIRECTORY_SEPARATOR . 'views', $name);
        }
        // Register module's hooks
        $this->registerHooks();
        // Register module's routes
        $this->registerRoutes();
    }

    /**
     * Method to override to register routes handled by the module.
     * By default, handled routes follow the given pattern 'GET /module_name[/module_ctrl[/action[/param1/param2/...]]]'.
     */
    protected function registerRoutes() 
    {
        $app = $this->app();
        $module = $this;
        $route = $this->_name.'(/:ctrl(/:action(/:params+)))';
        $route = '/' . ($this->isDefault() ? '(' . $route . ')' : $route);
        $app->get($route, function ($ctrl = 'index', $action = 'index', $params = null) use($app, $module)
        {
            $slimModulesDir = $this->config('slim.dir.modules');
            // Set templates directory to the module's one if current view renderer is not Twig.
            if (!($app->view instanceof \BenGee\Slim\Twig\TwigView))
            {
                $app->view->setTemplatesDirectory($slimModulesDir . DIRECTORY_SEPARATOR . $name . DIRECTORY_SEPARATOR . 'views', $name);
            }
            // Load controller class file.
            $ctrl_name = (!empty($ctrl) && trim($ctrl) != '' ? \BenGee\Slim\Utils\StringUtils::camelize($ctrl, true) : 'Index') . 'Controller';
            require_once($slimModulesDir . DIRECTORY_SEPARATOR .$module->name() . DIRECTORY_SEPARATOR . 'controllers' . DIRECTORY_SEPARATOR . $ctrl_name . '.php');
            // Create an instance of the controller.
            $ctrl_instance = new $ctrl_name($module);
            // Call required action.
            $action_name = ($action != null && trim($action) != '' ? \BenGee\Slim\Utils\StringUtils::camelize($action) : 'index') . 'Action';
            echo ($params != null ? $ctrl_instance->$action_name($params) : $ctrl_instance->$action_name());
        });
    }

    /**
     * Register module's hooks into the module's parent Slim application.
     */
    protected function registerHooks()
    {
    }

    /**
     * Render a view.
     * @param string $name Name of the view to render.
     * @param array $data Data to give to the view for rendering.
     * @param string $namespace Namespace where to look for view's template. If 'false' then it will search in default.
     * @return string Content of the rendered view or (===) 'false' if an error occured.
     * @throws \ErrorException If view name is null or empty.
     */
    public function render($name, $data = array(), $namespace = false)
    {
        if (!is_string($name) || empty(trim($name))) throw new \ErrorException("View's name is invalid (null, empty or not a string) !");
        if (!strstr($name, '@')) 
        {
            if ($namespace !== false && !is_string($namespace)) throw new \ErrorException("Cannot render [" . $name . "] view because of invalid namespace (not a string) !");
            $name = '@' . ($namespace === false ? $this->name() : trim($namespace)) . '/' . $name;
        }
        return $this->app()->view->render($name, $data);
    }
}
