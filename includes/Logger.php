<?php

namespace LicenseManagerForWooCommerce;

use DateTime;
use Exception;

defined('ABSPATH') || exit;

class Logger
{
    /**
     * @var string
     */
    const LOG_DIR = 'logs';

    /**
     * @var string
     */
    const DEBUG_FILE = 'debug.log';

    /**
     * @var string
     */
    const ERROR_FILE = 'error.log';

    /**
     * Helper function for converting any PHP value into a string.
     *
     * @param mixed $object
     * @param bool  $jsCode
     *
     * @return mixed
     */
    protected static function objectToString($object, $jsCode = false)
    {
        static $objectToStringMap = null;

        if (is_null($objectToStringMap)) {
            // https://secure.php.net/manual/en/function.gettype.php#refsect1-function.gettype-returnvalues
            $objectToStringMap = array(
                'boolean' => function (&$object, &$jsCode) {
                    return $object ? 'true' : 'false';
                },
                'integer' => function (&$object, &$jsCode) {
                    return strval($object);
                },
                'double' => function (&$object, &$jsCode) {
                    return strval($object);
                },
                'string' => function (&$object, &$jsCode) {
                    return $jsCode ? json_encode($object) : $object;
                },
                'array' => function (&$object, &$jsCode) {
                    return $jsCode ?
                        'JSON.parse(' . json_encode(json_encode(
                            $object,
                            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
                        )) . ')' :
                        json_encode(
                            $object,
                            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
                        );
                },
                'object' => function (&$object, &$jsCode) {
                    return $jsCode ?
                        json_encode(print_r($object, true)) :
                        print_r($object, true);
                },
                'resource' => function (&$object, &$jsCode) {
                    return $jsCode ?
                        json_encode('Resource of type "' . get_resource_type($object) . '"') :
                        'Resource of type "' . get_resource_type($object) . '"';
                },
                'NULL' => function (&$object, &$jsCode) {
                    return 'null';
                },
                'unknown type' => function (&$object, &$jsCode) {
                    return $jsCode ? json_encode('unknown type') : 'unknown type';
                }
            );
        }

        return $objectToStringMap[gettype($object)]($object, $jsCode);
    }

    /**
     * Helper function for getting a log label from the backtrace.
     *
     * @param array $backtrace
     *
     * @return string
     */
    protected static function labelFromBacktrace($backtrace)
    {
        return substr($backtrace[0]['file'], ABSPATH_LENGTH) . ':' . $backtrace[0]['line'];
    }

    /**
     * Log a value to the browser console.
     *
     * @param mixed $object
     * @param null  $label
     */
    public static function console($object, $label = null)
    {
        if (gettype($label) !== 'string') {
            $label = self::labelFromBacktrace(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS));
        }

        if (!empty($label)) {
            $label = wp_json_encode($label . "\n") . ', ';
        }

        $object_str = self::objectToString($object, true);

        add_action(is_admin() ? 'admin_footer' : 'wp_footer', function () use (&$object_str, &$label) {
            ?><script type="text/javascript">console.log(<?= wp_json_encode($label . $object_str); ?>);</script><?php
        }, 65535);
    }

    /**
     * Log the backtrace to the browser console.
     *
     * @param bool $chronological
     * @param null $label
     */
    public static function consoleBacktrace($chronological = true, $label = null)
    {
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);

        if (gettype($label) !== 'string') {
            $label = self::labelFromBacktrace($backtrace);
        }

        foreach ($backtrace as &$entry) {
            if (isset($entry['file'])) {
                $entry['file'] = substr($entry['file'], ABSPATH_LENGTH);
            }
        }

        if ($chronological) {
            $backtrace = array_reverse($backtrace);
            array_pop($backtrace);
        } else {
            array_shift($backtrace);
        }

        self::console($backtrace, $label);
    }

    /**
     * Log a value to a file.
     *
     * @param mixed  $object
     * @param string $filename
     * @param null   $label
     */
    public static function file($object, $filename = self::DEBUG_FILE, $label = null)
    {
        static $log_files = array();

        if (gettype($filename) !== 'string') {
            $filename = self::DEBUG_FILE;
        }

        if (!file_exists(LMFWC_LOG_DIR)) {
            mkdir(LMFWC_LOG_DIR);
        }

        if (!isset($log_files[$filename])) {
            $log_files[$filename] = fopen(LMFWC_LOG_DIR . $filename, 'ab');

            register_shutdown_function(function () use (&$log_files, &$filename) {
                fclose($log_files[$filename]);
            });
        }

        if (gettype($label) !== 'string') {
            $label = self::labelFromBacktrace(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS));
        }

        if (!empty($label)) {
            $label .= "\n";
        }

        fwrite($log_files[$filename], $label . self::objectToString($object) . "\n\n\n\n");
    }

    /**
     * Log the backtrace to a file.
     *
     * @param bool   $chronological
     * @param string $filename
     * @param null   $label
     */
    public static function fileBacktrace($chronological = true, $filename = self::DEBUG_FILE, $label = null)
    {
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);

        if (gettype($label) !== 'string') {
            $label = self::labelFromBacktrace($backtrace);
        }

        foreach ($backtrace as &$entry) {
            if (isset($entry['file'])) {
                $entry['file'] = substr($entry['file'], ABSPATH_LENGTH);
            }
        }

        if ($chronological) {
            $backtrace = array_reverse($backtrace);
            array_pop($backtrace);
        } else {
            array_shift($backtrace);
        }

        self::file($backtrace, $filename, $label);
    }

    /**
     * Log error messages or exception to the exception log.
     *
     * @param $error
     */
    public static function exception($error)
    {
        if (!$error instanceof Exception) {
            return;
        }

        try {
            $date = new DateTime();
            $time = $date->format('Y-m-d H:i');
        } catch (Exception $e) {
            $time = 'UNKNOWN';
        }

        $message = sprintf("Exception thrown at: %s\n", $time);
        $message .= 'Message: ' . $error->getMessage() . "\n";
        $message .= 'Code: ' . $error->getCode() . "\n";
        $message .= 'Thrown at: ' . $error->getFile() . ':' . $error->getLine() . "\n";
        $message .= "Trace:\n";
        $message .= $error->getTraceAsString();

        self::file($message, self::ERROR_FILE);
    }

    /**
     * Perform a preformatted var_dump.
     *
     * @param mixed $variable
     */
    public static function var_dump_pre($variable)
    {
        echo '<pre>';
        var_dump($variable);
        echo '</pre>';
    }

}