<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Application
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * @category   Zend
 * @package    Zend_Application
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @group      Zend_Application
 */
class Zend_Application_Module_AutoloaderTest extends PHPUnit\Framework\TestCase
{
    protected $loaders;
    protected $autoloader;
    protected $includePath;
    protected $error;
    protected $loader;

    public function setUp(): void
    {
        // Store original autoloaders
        $this->loaders = spl_autoload_functions();
        if (!is_array($this->loaders)) {
            // spl_autoload_functions does not return empty array when no
            // autoloaders registered...
            $this->loaders = array();
        }

        // Store original include_path
        $this->includePath = get_include_path();

        Zend_Loader_Autoloader::resetInstance();
        $this->autoloader = Zend_Loader_Autoloader::getInstance();

        // initialize 'error' member for tests that utilize error handling
        $this->error = null;

        $this->loader = new Zend_Application_Module_Autoloader(array(
            'namespace' => 'FooBar',
            'basePath'  => realpath(dirname(__FILE__) . '/_files'),
        ));
    }

    public function tearDown(): void
    {
        // Restore original autoloaders
        $loaders = spl_autoload_functions();
        foreach ($loaders as $loader) {
            spl_autoload_unregister($loader);
        }

        foreach ($this->loaders as $loader) {
            spl_autoload_register($loader);
        }

        // Retore original include_path
        set_include_path($this->includePath);

        // Reset autoloader instance so it doesn't affect other tests
        Zend_Loader_Autoloader::resetInstance();
    }

    public function testDbTableResourceTypeShouldBeLoadedByDefault()
    {
        $this->assertTrue($this->loader->hasResourceType('dbtable'));
    }

    public function testDbTableResourceTypeShouldPointToModelsDbTableSubdirectory()
    {
        $resources = $this->loader->getResourceTypes();
        $this->assertStringContainsString('models/DbTable', $resources['dbtable']['path']);
    }

    public function testFormResourceTypeShouldBeLoadedByDefault()
    {
        $this->assertTrue($this->loader->hasResourceType('form'));
    }

    public function testFormResourceTypeShouldPointToFormsSubdirectory()
    {
        $resources = $this->loader->getResourceTypes();
        $this->assertStringContainsString('forms', $resources['form']['path']);
    }

    public function testModelResourceTypeShouldBeLoadedByDefault()
    {
        $this->assertTrue($this->loader->hasResourceType('model'));
    }

    public function testModelResourceTypeShouldPointToModelsSubdirectory()
    {
        $resources = $this->loader->getResourceTypes();
        $this->assertStringContainsString('models', $resources['model']['path']);
    }

    public function testPluginResourceTypeShouldBeLoadedByDefault()
    {
        $this->assertTrue($this->loader->hasResourceType('plugin'));
    }

    public function testPluginResourceTypeShouldPointToPluginsSubdirectory()
    {
        $resources = $this->loader->getResourceTypes();
        $this->assertStringContainsString('plugins', $resources['plugin']['path']);
    }

    public function testServiceResourceTypeShouldBeLoadedByDefault()
    {
        $this->assertTrue($this->loader->hasResourceType('service'));
    }

    public function testServiceResourceTypeShouldPointToServicesSubdirectory()
    {
        $resources = $this->loader->getResourceTypes();
        $this->assertStringContainsString('services', $resources['service']['path']);
    }

    public function testViewHelperResourceTypeShouldBeLoadedByDefault()
    {
        $this->assertTrue($this->loader->hasResourceType('viewhelper'));
    }

    public function testViewHelperResourceTypeShouldPointToViewHelperSubdirectory()
    {
        $resources = $this->loader->getResourceTypes();
        $this->assertStringContainsString('views/helpers', $resources['viewhelper']['path']);
    }

    public function testViewFilterResourceTypeShouldBeLoadedByDefault()
    {
        $this->assertTrue($this->loader->hasResourceType('viewfilter'));
    }

    public function testViewFilterResourceTypeShouldPointToViewFilterSubdirectory()
    {
        $resources = $this->loader->getResourceTypes();
        $this->assertStringContainsString('views/filters', $resources['viewfilter']['path']);
    }

    public function testDefaultResourceShouldBeModel()
    {
        $this->assertEquals('model', $this->loader->getDefaultResourceType());
    }
}
