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
class Zend_Application_Resource_LocaleTest extends PHPUnit\Framework\TestCase
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

        $this->bootstrap = new Zend_Application_Bootstrap_Bootstrap($this->application);

        Zend_Registry::_unsetInstance();
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

        // Reset autoloader instance so it doesn't affect other tests
        Zend_Loader_Autoloader::resetInstance();
    }

    public function testInitializationInitializesLocaleObject()
    {
        $resource = new Zend_Application_Resource_Locale(array());
        $resource->init();
        $this->assertTrue($resource->getLocale() instanceof Zend_Locale);
    }

    public function testInitializationReturnsLocaleObject()
    {
        $resource = new Zend_Application_Resource_Locale(array());
        $resource->setBootstrap($this->bootstrap);
        $test = $resource->init();
        $this->assertInstanceOf(Zend_Locale::class, $test);
    }

    public function testOptionsPassedToResourceAreUsedToSetLocaleState()
    {
        $options = array(
            'default'      => 'kok_IN',
            'registry_key' => 'Foo_Bar',
            'force'        => true
        );

        $resource = new Zend_Application_Resource_Locale($options);
        $resource->setBootstrap($this->bootstrap);
        $resource->init();
        $locale = $resource->getLocale();
        $this->assertEquals('kok_IN', $locale->__toString());
        $this->assertTrue(Zend_Registry::isRegistered('Foo_Bar'));
        $this->assertSame(Zend_Registry::get('Foo_Bar'), $locale);
    }

    public function testOptionsPassedToResourceAreUsedToSetLocaleState1()
    {
        $options = array(
            'default' => 'kok_IN',
            'force'   => true
        );

        $resource = new Zend_Application_Resource_Locale($options);
        $resource->setBootstrap($this->bootstrap);
        $resource->init();
        $locale = $resource->getLocale();

        // This test will fail if your configured locale is kok_IN
        $this->assertEquals('kok_IN', $locale->__toString());
        $this->assertSame(Zend_Registry::get('Zend_Locale'), $locale);
    }

    /**
     * @group ZF-7058
     */
    public function testSetCache()
    {
        $cache = Zend_Cache::factory('Core', 'Black Hole', array(
            'lifetime'                => 120,
            'automatic_serialization' => true
        ));

        $config = array(
            'default' => 'fr_FR',
            'cache'   => $cache,
        );
        $resource = new Zend_Application_Resource_Locale($config);
        $resource->init();
        $backend = Zend_Locale::getCache()->getBackend();
        $this->assertInstanceOf(Zend_Cache_Backend_BlackHole::class, $backend);
        Zend_Locale::removeCache();
    }

    /**
     * @group ZF-7058
     */
    public function testSetCacheFromCacheManager()
    {
        $configCache = array(
            'memory' => array(
                'frontend' => array(
                    'name'    => 'Core',
                    'options' => array(
                        'lifetime'                => 120,
                        'automatic_serialization' => true
                    )
                ),
                'backend' => array(
                    'name' => 'Black Hole'
                )
            )
        );
        $this->bootstrap->registerPluginResource('cachemanager', $configCache);
        $this->assertFalse(Zend_Locale::hasCache());

        $config = array(
            'bootstrap' => $this->bootstrap,
            'cache'     => 'memory',
        );
        $resource = new Zend_Application_Resource_Locale($config);
        $resource->init();

        $this->assertTrue(Zend_Locale::hasCache());
        Zend_Locale::removeCache();
    }
}
