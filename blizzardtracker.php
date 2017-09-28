<?php

namespace Grav\Plugin;

use Grav\Common\Plugin;
use Grav\Common\Utils;

/**
 * Class BlizzardTrackerPlugin
 * @package Grav\Plugin
 */
class BlizzardTrackerPlugin extends Plugin
{
    /**
     * @return array
     *
     * The getSubscribedEvents() gives the core a list of events
     *     that the plugin wants to listen to. The key of each
     *     array section is the event that the plugin listens to
     *     and the value (in the form of an array) contains the
     *     callable (or function) as well as the priority. The
     *     higher the number the higher the priority.
     */
    public static function getSubscribedEvents()
    {
        return [
            'onPluginsInitialized' => [['setup', 100000]],
        ];
    }

    /**
     * If the admin path matches, initialize the Login plugin configuration and set the admin
     * as active.
     */
    public function setup()
    {
        // Autoloader
        spl_autoload_register(function ($class) {
            if (Utils::startsWith($class, 'Grav\Plugin\BlizzardTracker')) {
                require_once __DIR__ . '/classes/' . strtolower(basename(str_replace("\\", "/", $class))) . '.php';
            }
        });
    }
}