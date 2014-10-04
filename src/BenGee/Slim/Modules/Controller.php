<?php

/**
 * SlimMod - Library for modules based Slim apps development.
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
 * Abstract Slim module's controller.
 * Any controller in modules based Slim apps must inherit this class.
 * @package BenGee\Slim\Modules
 * @author Benjamin GILLET <bgillet@hotmail.fr>
 * @version 1.0.1
 * @since 1.0.0
 */
abstract class Controller 
{
    /**
     * Reference to the parent module.
     * @var \BenGee\Slim\Modules\Module
     */
    private $_module = null;

    /**
     * Constructor.
     * @param \BenGee\Slim\Module $module Reference to the parent module.
     * @throws ErrorException if controller's parent module reference is invalid (null).
     */
    public function __construct(\BenGee\Slim\Modules\Module $module)
    {
        if ($module == null) throw new \ErrorException("Controller's parent module is invalid (null) !");
        $this->_module = $module;
    }

    /**
     * Return a reference to the parent Slim application of the controller's parent module.
     * @return \BenGee\Slim\Modules\SlimMod A reference to the parent Slim application.
     */
    public function app() 
    {
        return $this->_module->app();
    }

    /**
     * Return controller's parent module.
     * @return \BenGee\Slim\Modules\Module A reference to the controller's parent module.
     */
    public function module() 
    {
        return $this->_module;
    }

    /**
     * Render a view (see \BenGee\Slim\Modules\Module class render() method).
     * @param string $name Name of the view to render.
     * @param string $namespace Namespace where to look for the view's template.
     * @return string Content of the rendered view or (===) 'false' if an error occured.
     * @throws \ErrorException If view name is null or empty.
     */
    public function render($name, $namespace = false) 
    {
        return $this->module()->render($name, $namespace);
    }

    /**
     * Display a view. Simply echo the result of the render() method.
     * @param string $name Name of the view to display.
     * @param string $namespace Namespace where to look for the view's template.
     * @throw \ErrorException if view name is null or empty.
     */
    public function display($name, $namespace = false) 
    {
        echo $this->render($name, $namespace);
    }
}
