<?php

namespace Bdf\PHPUnit;

use PHPUnit\Framework\TestCase;

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

        $this->assertEquals(['Test' => 1], $deprecation['main']);
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

        $expected = <<<STDOUT

No deprecation notice

STDOUT;

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

        $expected = <<<STDOUT

Deprecation notices (1):

1) PHPUnit\TextUI\Command
  ->main()
      Test: 1

STDOUT;

        $this->assertSame($expected, $stdout);
    }
}