<?php

/*
Copyright (c) 2004-2017 Fabien Potencier
Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is furnished
to do so, subject to the following conditions:
The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.
THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.
 */

namespace Bdf\PHPUnit;

use PHPUnit\Util\ErrorHandler;

/**
 * DeprecationErrorHandler
 *
 * Catch deprecation notices and print a summary report at the end of the test suite
 *
 * @link https://github.com/symfony/symfony/pull/13032
 * @author Nicolas Grekas <p@tchwork.com>
 */
class DeprecationErrorHandler
{
    /**
     * Flag for registration
     *
     * @var bool
     */
    private static $isRegistered = false;

    /**
     * @var array
     */
    private $deprecations = [0];

    /**
     * @var callable
     */
    private $previous;

    /**
     * DeprecationErrorHandler constructor.
     *
     * @param callable $previous  The previous registered handler
     */
    public function __construct(?callable $previous = null)
    {
        $this->previous = $previous ?: $this->getPhpunitHandler();
    }

    /**
     * Find callable from phpunit version
     *
     * @return callable
     */
    private function getPhpunitHandler()
    {
        if (method_exists(ErrorHandler::class, 'handleError')) {
            return [ErrorHandler::class, 'handleError'];
        }

        // PHPUnit >= 8
        return new ErrorHandler(false, false, false, false);
    }

    /**
     * Register deprecated error handler
     *
     * @codeCoverageIgnore
     */
    public static function register()
    {
        if (self::$isRegistered) {
            return;
        }

        $previousHandler = set_error_handler('var_dump');
        restore_error_handler();

        if ($previousHandler !== null) {
            restore_error_handler();
        }

        $handler = new self($previousHandler);
        set_error_handler([$handler, 'handleError']);
        register_shutdown_function([$handler, 'display']);

        self::$isRegistered = true;
    }

    /**
     * Handle a deprecation. Send other level to the old handler.
     *
     * @param  int     $level
     * @param  string  $message
     * @param  string  $file
     * @param  int     $line
     * @param  array   $context
     */
    public function handleError($level, $message, $file = '', $line = 0, $context = array())
    {
        if (E_USER_DEPRECATED !== $level) {
            if (null !== ($previous = $this->previous)) {
                $previous($level, $message, $file, $line);
            }

            return;
        }

        ++$this->deprecations[0];
        $trace = debug_backtrace(PHP_VERSION_ID >= 50400 ? DEBUG_BACKTRACE_IGNORE_ARGS : false);

        $i = count($trace);
        while (isset($trace[--$i]['class']) && ('ReflectionMethod' === $trace[$i]['class'] || 0 === strpos($trace[$i]['class'], 'PHPUnit_'))) {
            // No-op
        }

        if (isset($trace[$i]['class'])) {
            if (isset($this->deprecations[$trace[$i]['class']][$trace[$i]['function']][$message])) {
                ++$this->deprecations[$trace[$i]['class']][$trace[$i]['function']][$message];
            } else {
                $this->deprecations[$trace[$i]['class']][$trace[$i]['function']][$message] = 1;
            }
        }
    }

    /**
     * Display deprecation found
     */
    public function display()
    {
        if (!$this->deprecations[0]) {
            $this->stdout("No deprecation notice");
            return;
        }

        $this->stdout("Deprecation notices ({$this->deprecations[0]}):", 'warn');

        $i = 1;
        foreach ($this->getDeprecations() as $class => $noticesByMethod) {
            echo "\n{$i}) {$class}\n";

            foreach ($noticesByMethod as $method => $notices) {
                echo "  ->{$method}()\n";
                foreach ($notices as $msg => $freq) {
                    echo "      {$msg}: $freq\n";
                }
            }

            $i++;
        }
    }

    /**
     * Print on stdout
     *
     * @param string $message
     */
    private function stdout($message, $level = 'info')
    {
        if ($level === 'info') {
            $level = '42';
        } else {
            $level = '43';
        }

        if (function_exists('posix_isatty') && @posix_isatty(STDOUT)) {
            echo "\n\x1B[{$level};30m{$message}\x1B[0m\n";
        } else {
            echo "\n{$message}\n";
        }
    }

    /**
     * Gets the catched deprecations
     *
     * @return array
     */
    public function getDeprecations()
    {
        $deprecations = $this->deprecations;
        unset($deprecations[0]);

        return $deprecations;
    }
}
