<?php

/**
 * SlimMod - Library for modules based Slim apps development.
 *
 * @author Benjamin GILLET <bgillet@hotmail.fr>
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

use \BenGee\Slim\Utils\StringUtils;

/**
 * New Slim application startup class for creation of modules based Slim apps.
 * It overrides default Slim's constructor to add new initialization steps.
 * Application handles new settings retrieved from application configuration.
 * These settings are :
 *      'slim.dir.root'      : application root directory
 *      'slim.dir.modules'   : modules storage directory
 *      'slim.dir.templates' : default templates storage directory
 *      'slim.modules'       : fully qualified classnames of module to load
 * @author Benjamin GILLET <bgillet@hotmail.fr>
 */
class SlimMod extends \Slim\Slim
{
    /**
     * Constructor.
     * First, get application root directory.
     * Second, get user defined modules storage directory or use a default
     * 'modules' one inside application root directory.
     * Third, get user defined default templates directory or use a default
     * 'templates' one inside application root directory.
     * Fourth, get array of modules fully qualified classnames to load.
     * @param array $userSettings Associative array of application settings.
     * @throw \ErrorException If module class cannot be loaded or an instance is already loaded.
     */
    public function __construct(array $userSettings = array())
    {
        parent::__construct($userSettings);
        
        // Get application root directory path
        $slimRootDir = $this->config('slim.dir.root');
        if (!StringUtils::emptyOrSpaces($slimRootDir) && !StringUtils::endsWith($slimRootDir, '/') && !StringUtils::endsWith($slimRootDir, '\\')) $slimRootDir .= DIRECTORY_SEPARATOR;
        
        // Get application modules directory path
        $slimModulesDir = $this->config('slim.dir.modules');
        if (!StringUtils::emptyOrSpaces($slimModulesDir))
        {
            if (!StringUtils::endsWith($slimModulesDir, '/') && !StringUtils::endsWith($slimModulesDir, '\\')) $slimModulesDir .= DIRECTORY_SEPARATOR;
        }
        else
        {
            $slimModulesDir = $slimRootDir . 'modules' . DIRECTORY_SEPARATOR;
        }
        
        // Set application default templates directory where to look first
        $slimTemplatesDir = $this->config('slim.dir.templates');
        if (!StringUtils::emptyOrSpaces($slimTemplatesDir))
        {
            if (!StringUtils::endsWith($slimTemplatesDir, '/') && !StringUtils::endsWith($slimTemplatesDir, '\\')) $slimTemplatesDir .= DIRECTORY_SEPARATOR;
        }
        else
        {
            $slimTemplatesDir = $slimRootDir . 'templates' . DIRECTORY_SEPARATOR;
        }
        if ($this->view instanceof \BenGee\Slim\TwigView)
        {
            $this->view->addTemplatesDirectory($slimTemplatesDir);
        }
        else
        {
            $this->view->setTemplatesDirectory($slimTemplatesDir);
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

        // Inject a single instance of each defined module into the main application under $app->modules->module_name
        $this->modules = new \Slim\Helper\Set();
        foreach($slimModules as $moduleName => $moduleClass)
        {
            // If the class is not already loaded because not available as a package downloaded via Composer ...
            if (!class_exists($moduleClass))
            {
                // ... then try loading from the local modules folder
                $moduleFile = $slimModulesDir . $moduleName . DIRECTORY_SEPARATOR . $moduleClass . '.php';
                if (file_exists($moduleFile))
                {
                    require_once $moduleFile;
                }
                // ... else throw an exception
                else
                {
                    throw new \ErrorException("Cannot load module definition file : " . $moduleFile);
                }
            }
            // Reference module inside the application in the 'modules' named array under the module's name as key.
            $module = new $moduleClass($this, $moduleName, (trim($moduleName) == 'default' ? true : false));
            $oldModule = $this->modules[$moduleName];
            if (!empty($oldModule) || $oldModule instanceof $moduleClass) throw new \ErrorException("An instance of the module is already loaded !");
            $this->modules->singleton($moduleName, function () use ($module)
            {
                return $module;
            });
        }
    }
}
