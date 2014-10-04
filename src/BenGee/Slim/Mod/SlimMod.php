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
 * New Slim application startup class for creation of modules based Slim apps.
 * It overrides default Slim's constructor to add new initialization steps.
 * @package BenGee\Slim\Modules
 * @author Benjamin GILLET <bgillet@hotmail.fr>
 * @version 1.0.1
 * @since 1.0.0
 */
class SlimMod extends \Slim\Slim
{
    /**
     * Constructor.
     * @param array $userSettings Associative array of application settings.
     */
    public function __construct(array $userSettings = array())
    {
        parent::__construct($userSettings);
        
        $slimRootDir = $this->config('slim.dir.root');
        $slimModulesDir = $this->config('slim.dir.modules');
        
        // Reference a default folder where to look for templates first.
        if ($app->view instanceof \BenGee\Slim\TwigView)
        {
          $app->view->addTemplatesDirectory($slimRootDir . DIRECTORY_SEPARATOR . 'templates');
        }
        else
        {
          $app->view->setTemplatesDirectory($slimRootDir . DIRECTORY_SEPARATOR . 'templates');
        }

        // Load list of modules composing main application.
        $slimModules = $this->config('slim.modules');

        // Create a default modules list if not defined.
        if (!is_array($slimModules))
        {
            $slimModules = array();
        }

        // Add the default module to the list if not already present.
        if (array_search('default', $slimModules) === false) $slimModules['default'] = 'DefaultModule';
        
        // Update the modules list in the application settings
        $this->config('slim.modules', $slimModules);

        // Inject a single instance of each defined module into the main application.
        foreach($slimModules as $moduleName => $moduleClass)
        {
          // Load the defined module's class file.
          require_once $slimModulesDir . DIRECTORY_SEPARATOR . $moduleName . DIRECTORY_SEPARATOR . 'module.php';
          // Reference module inside the application in the 'module' named array under the module's name as key.
          $module = new $moduleClass($app, $moduleName, (trim($moduleName) == 'default' ? true : false));
          $app->$modules[$moduleName] = $module;
        }
    }
}
