<?php
/**
 * @file
 * Lightning\Tools\Configuration
 */

namespace Lightning\Tools;

/**
 * A helper to load variables from the configuration.
 *
 * @package Lightning\Tools
 */
class Configuration {
    /**
     * The cached configuration data.
     *
     * @var array
     */
    protected static $configuration = array();

    /**
     * Get a config variable's value.
     *
     * @param string $variable
     *   The path to the variable within the config.
     *
     * @return mixed
     *   The value of the variable.
     */
    public static function get($variable, $default = null) {
        if (empty(self::$configuration)) {
            self::loadConfiguration();
        }

        return Data::getFromPath($variable, self::$configuration, $default);
    }

    /**
     * Set a configuration variable's value.
     *
     * @param string $variable
     *   The name of the variable.
     *
     * @param mixed $value
     *   The new value.
     */
    public static function set($variable, $value) {
        if (empty(self::$configuration)) {
            self::loadConfiguration();
        }

        Data::setInPath($variable, $value, self::$configuration);
    }

    /**
     * Merge new data into the configuration.
     *
     * @param array $new_data
     *   An array of new data to merge.
     */
    public static function merge($new_data) {
        self::$configuration = array_replace_recursive(self::$configuration, $new_data);
    }

    /**
     * Override the entire configuration with a new one.
     *
     * @param array $new_configuration
     *   An array with the new configuration.
     */
    public static function override($new_configuration) {
        self::$configuration = $new_configuration;
    }

    /**
     * Load the configuration from the configuration.inc.php file.
     */
    protected static function loadConfiguration() {
        if (empty(self::$configuration)) {
            foreach (self::getConfigurations() as $config_file) {
                if (file_exists($config_file)) {
                    self::$configuration = array_replace_recursive(
                        self::getConfigurationData($config_file),
                        self::$configuration
                    );
                } else {
                    echo "not found $config_file";
                }
            }
        }
    }

    /**
     * Get the full configuration.
     *
     * @return array
     *   The full configuration.
     */
    public static function getConfiguration() {
        return self::$configuration;
    }

    /**
     * Get a list of configuration files.
     *
     * @return array
     *   A list of files.
     */
    public static function getConfigurations() {
        return array(
            'source' => CONFIG_PATH . '/config.inc.php',
            'internal' => HOME_PATH . '/Lightning/Config.php'
        );
    }

    /**
     * Load a configuration form a file.
     *
     * @param string $file
     *   The absolute file path.
     *
     * @return array
     *   The config data.
     */
    public static function getConfigurationData($file) {
        include $file;
        return $conf;
    }
}
