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
class Zend_Application_Resource_FrontcontrollerTest extends PHPUnit\Framework\TestCase
{
    protected $loaders;
    protected $autoloader;
    protected $application;
    protected $bootstrap;

    public function setUp(): void
    {
        // Store original autoloaders
        $this->loaders = spl_autoload_functions();
        if (!is_array($this->loaders)) {
            // spl_autoload_functions does not return empty array when no
            // autoloaders registered...
            $this->loaders = array();
        }

        Zend_Loader_Autoloader::resetInstance();
        $this->autoloader = Zend_Loader_Autoloader::getInstance();

        $this->application = new Zend_Application('testing');

        require_once dirname(__FILE__) . '/../_files/ZfAppBootstrap.php';
        $this->bootstrap = new ZfAppBootstrap($this->application);
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

        Zend_Controller_Front::getInstance()->resetInstance();

        // Reset autoloader instance so it doesn't affect other tests
        Zend_Loader_Autoloader::resetInstance();
    }

    public function testInitializationCreatesFrontControllerInstance()
    {
        $resource = new Zend_Application_Resource_Frontcontroller(array());
        $resource->init();
        $this->assertTrue($resource->getFrontController() instanceof Zend_Controller_Front);
    }

    public function testInitializationPushesFrontControllerToBootstrapWhenPresent()
    {
        $resource = new Zend_Application_Resource_Frontcontroller(array());
        $resource->setBootstrap($this->bootstrap);
        $resource->init();
        $this->assertSame($resource->getFrontController(), $this->bootstrap->frontController);
    }

    public function testShouldSetControllerDirectoryWhenStringOptionPresent()
    {
        $resource = new Zend_Application_Resource_Frontcontroller(array(
            'controllerDirectory' => dirname(__FILE__),
        ));
        $resource->init();
        $front = $resource->getFrontController();
        $dir   = $front->getControllerDirectory('default');
        $this->assertEquals(dirname(__FILE__), $dir);
    }

    public function testShouldSetControllerDirectoryWhenArrayOptionPresent()
    {
        $resource = new Zend_Application_Resource_Frontcontroller(array(
            'controllerDirectory' => array(
                'foo' => dirname(__FILE__),
            ),
        ));
        $resource->init();
        $front = $resource->getFrontController();
        $dir   = $front->getControllerDirectory('foo');
        $this->assertEquals(dirname(__FILE__), $dir);
    }

    /**
     * @group ZF-6458
     */
    public function testAllControllerDirectoriesShouldBeSetWhenArrayPassedToControllerDirectoryOption()
    {
        $resource = new Zend_Application_Resource_Frontcontroller(array(
            'controllerDirectory' => array(
                'foo' => dirname(__FILE__),
                'bar' => dirname(__FILE__),
            ),
        ));
        $resource->init();
        $front = $resource->getFrontController();
        $dirs  = $front->getControllerDirectory();
        $this->assertEquals(array(
            'foo' => dirname(__FILE__),
            'bar' => dirname(__FILE__),
        ), $dirs);
    }

    public function testShouldSetModuleControllerDirectoryNameWhenOptionPresent()
    {
        $resource = new Zend_Application_Resource_Frontcontroller(array(
            'moduleControllerDirectoryName' => 'foo',
        ));
        $resource->init();
        $front = $resource->getFrontController();
        $dir   = $front->getModuleControllerDirectoryName();
        $this->assertEquals('foo', $dir);
    }

    public function testShouldSetModuleDirectoryWhenOptionPresent()
    {
        $resource = new Zend_Application_Resource_Frontcontroller(array(
            'moduleDirectory' => dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR
                               . '_files' . DIRECTORY_SEPARATOR . 'modules',
        ));
        $resource->init();
        $front    = $resource->getFrontController();
        $dir      = $front->getControllerDirectory();
        $expected = array(
            'bar' => dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR
                       . '_files' . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR
                       . 'bar' . DIRECTORY_SEPARATOR . 'controllers',
            'default' => dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR
                       . '_files' . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR
                       . 'default' . DIRECTORY_SEPARATOR . 'controllers',
            'foo-bar' => dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR
                       . '_files' . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR
                       . 'foo-bar' . DIRECTORY_SEPARATOR . 'controllers',
            'foo' => dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR
                       . '_files' . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR
                       . 'foo' . DIRECTORY_SEPARATOR . 'controllers',
            'baz' => dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR
                       . '_files' . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR
                       . 'baz' . DIRECTORY_SEPARATOR . 'controllers',
            'zfappbootstrap' => dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR
                              . '_files' . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR
                              . 'zfappbootstrap' . DIRECTORY_SEPARATOR . 'controllers',
        );
        $this->assertEquals($expected, $dir);
    }

    /**
     * @group ZF-9258
     */
    public function testShouldSetMultipleModuleDirectorysWhenOptionPresent()
    {
        $resource = new Zend_Application_Resource_Frontcontroller(array(
            'moduleDirectory' => array(dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR
                               . '_files' . DIRECTORY_SEPARATOR . 'modules',
                dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR
                               . '_files' . DIRECTORY_SEPARATOR . 'more_modules')
        ));
        $resource->init();
        $front    = $resource->getFrontController();
        $dir      = $front->getControllerDirectory();
        $expected = array(
            'bar' => dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR
                       . '_files' . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR
                       . 'bar' . DIRECTORY_SEPARATOR . 'controllers',
            'default' => dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR
                       . '_files' . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR
                       . 'default' . DIRECTORY_SEPARATOR . 'controllers',
            'foo-bar' => dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR
                       . '_files' . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR
                       . 'foo-bar' . DIRECTORY_SEPARATOR . 'controllers',
            'foo' => dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR
                       . '_files' . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR
                       . 'foo' . DIRECTORY_SEPARATOR . 'controllers',
            'baz' => dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR
                       . '_files' . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR
                       . 'baz' . DIRECTORY_SEPARATOR . 'controllers',
            'zfappbootstrap' => dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR
                              . '_files' . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR
                              . 'zfappbootstrap' . DIRECTORY_SEPARATOR . 'controllers',
            'bat' => dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR
                       . '_files' . DIRECTORY_SEPARATOR . 'more_modules' . DIRECTORY_SEPARATOR
                       . 'bat' . DIRECTORY_SEPARATOR . 'controllers',
            'foobaz' => dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR
                       . '_files' . DIRECTORY_SEPARATOR . 'more_modules' . DIRECTORY_SEPARATOR
                       . 'foobaz' . DIRECTORY_SEPARATOR . 'controllers',
        );
        $this->assertEquals($expected, $dir);
    }


    public function testShouldSetDefaultControllerNameWhenOptionPresent()
    {
        $resource = new Zend_Application_Resource_Frontcontroller(array(
            'defaultControllerName' => 'foo',
        ));
        $resource->init();
        $front = $resource->getFrontController();
        $test  = $front->getDefaultControllerName();
        $this->assertEquals('foo', $test);
    }

    public function testShouldSetDefaultActionWhenOptionPresent()
    {
        $resource = new Zend_Application_Resource_Frontcontroller(array(
            'defaultAction' => 'foo',
        ));
        $resource->init();
        $front = $resource->getFrontController();
        $test  = $front->getDefaultAction();
        $this->assertEquals('foo', $test);
    }

    public function testShouldSetDefaultModuleWhenOptionPresent()
    {
        $resource = new Zend_Application_Resource_Frontcontroller(array(
            'defaultModule' => 'foo',
        ));
        $resource->init();
        $front = $resource->getFrontController();
        $test  = $front->getDefaultModule();
        $this->assertEquals('foo', $test);
    }

    public function testShouldSetBaseUrlWhenOptionPresent()
    {
        $resource = new Zend_Application_Resource_Frontcontroller(array(
            'baseUrl' => '/foo',
        ));
        $resource->init();
        $front = $resource->getFrontController();
        $test  = $front->getBaseUrl();
        $this->assertEquals('/foo', $test);
    }

    public function testShouldSetParamsWhenOptionPresent()
    {
        $params = array(
            'foo' => 'bar',
            'bar' => 'baz',
        );
        $resource = new Zend_Application_Resource_Frontcontroller(array(
            'params' => $params,
        ));
        $resource->init();
        $front = $resource->getFrontController();
        $test  = $front->getParams();
        $this->assertEquals($params, $test);
    }

    public function testShouldInstantiateAndRegisterPluginsWhenOptionPassed()
    {
        $plugins = array(
            'Zend_Controller_Plugin_ActionStack',
        );
        $resource = new Zend_Application_Resource_Frontcontroller(array(
            'plugins' => $plugins,
        ));
        $resource->init();
        $front = $resource->getFrontController();
        foreach ($plugins as $class) {
            $this->assertTrue($front->hasPlugin($class));
        }
    }

    public function testShouldReturnFrontControllerWhenComplete()
    {
        $resource = new Zend_Application_Resource_Frontcontroller(array(
            'controllerDirectory' => dirname(__FILE__),
        ));
        $front = $resource->init();
        $this->assertInstanceOf(Zend_Controller_Front::class, $front);
    }

    public function testNoBaseUrlShouldBeSetIfEmptyBaseUrlProvidedInOptions()
    {
        $resource = new Zend_Application_Resource_Frontcontroller(array(
            'baseurl' => '',
        ));
        $resource->init();
        $front = $resource->getFrontController();
        $this->assertNull($front->getBaseUrl());
    }

    /**
     * @group ZF-9044
     */
    public function testSettingOfRegisterPluginIndexActuallyWorks()
    {
        $plugins = array(
            array('class'      => 'Zend_Controller_Plugin_ErrorHandler',
                  'stackindex' => 10),
            'Zend_Controller_Plugin_ActionStack',
            array('class'      => 'Zend_Controller_Plugin_PutHandler',
                  'stackIndex' => 5),
        );

        $expected = array(
            1  => 'Zend_Controller_Plugin_ActionStack',
            5  => 'Zend_Controller_Plugin_PutHandler',
            10 => 'Zend_Controller_Plugin_ErrorHandler',
        );

        $resource = new Zend_Application_Resource_Frontcontroller(array(
            'plugins' => $plugins
        ));

        $resource->init();
        $front   = $resource->getFrontController();
        $plugins = $front->getPlugins();

        $this->assertEquals(count($expected), count($plugins));
        foreach ($expected as $index => $class) {
            $this->assertEquals($class, get_class($plugins[$index]));
        }
    }

    /**
     * @group ZF-7367
     */
    public function testPassingReturnResponseFlagShouldAlterFrontControllerStatus()
    {
        $resource = new Zend_Application_Resource_Frontcontroller(array(
            'returnresponse' => true,
        ));
        $resource->init();
        $front = $resource->getFrontController();
        $this->assertTrue($front->returnResponse());
    }

    /**
     * @group ZF-9724
     */
    public function testShouldSetDispatcherFromConfiguration()
    {
        $resource = new Zend_Application_Resource_Frontcontroller(array(
            'dispatcher' => array(
                'class'  => 'ZF9724_Dispatcher',
                'params' => array(
                    'bar' => 'baz'
                )
            )
        ));
        $resource->init();
        $front = $resource->getFrontController();
        $this->assertEquals('ZF9724_Dispatcher', get_class($front->getDispatcher()));
        $this->assertEquals('baz', $front->getDispatcher()->getParam('bar'));
    }
}

class ZF9724_Dispatcher extends Zend_Controller_Dispatcher_Standard
{
}
