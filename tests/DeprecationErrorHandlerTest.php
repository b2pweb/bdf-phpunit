<?php

namespace Bdf\PHPUnit;

use PHPUnit\Framework\TestCase;
use PHPUnit\TextUI\Command;

/**
 *
 */
class DeprecationErrorHandlerTest extends TestCase
{
    /**
     * 
     */
    public function test_call_previous_handler()
    {
        $isCalled = false;
        $handler = new DeprecationErrorHandler(function() use(&$isCalled) {
            $isCalled = true;
        });

        $handler->handleError(E_USER_ERROR, 'Test');

        $this->assertTrue($isCalled);
    }

    /**
     *
     */
    public function test_catch_deprecation()
    {
        $handler = new DeprecationErrorHandler();
        $handler->handleError(E_USER_DEPRECATED, 'Test');
        $deprecation = current($handler->getDeprecations());

        $this->assertEquals(['Test' => 1], $deprecation['main'] ?? $deprecation['run']);
    }

    /**
     *
     */
    public function test_empty_display()
    {
        $handler = new DeprecationErrorHandler();

        ob_start();
        $handler->display();
        $stdout = ob_get_contents();
        ob_end_clean();

        if (function_exists('posix_isatty') && @posix_isatty(STDOUT)) {
            $expected = "\n\x1B[42;30mNo deprecation notice\x1B[0m\n";
        } else {
            $expected = "\nNo deprecation notice\n";
        }

        $this->assertSame($expected, $stdout);
    }

    /**
     *
     */
    public function test_display()
    {
        $handler = new DeprecationErrorHandler();
        $handler->handleError(E_USER_DEPRECATED, 'Test');

        ob_start();
        $handler->display();
        $stdout = ob_get_contents();
        ob_end_clean();

        if (function_exists('posix_isatty') && @posix_isatty(STDOUT)) {
            $expected = "\n\x1B[43;30mDeprecation notices (1):\x1B[0m";
        } else {
            $expected = "\nDeprecation notices (1):\n";
        }

        $this->assertStringContainsString($expected, $stdout);

        if (class_exists(Command::class)) {
            $expected = <<<STDOUT
1) PHPUnit\TextUI\Command
  ->main()
      Test: 1

STDOUT;
        } else {
            $expected = <<<STDOUT
1) PHPUnit\TextUI\Application
  ->run()
      Test: 1

STDOUT;
        }

        $this->assertStringContainsString($expected, $stdout);
    }
}