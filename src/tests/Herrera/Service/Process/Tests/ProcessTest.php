<?php

namespace Herrera\Service\Process\Tests;

use Herrera\Service\Process\Process;
use Herrera\PHPUnit\TestCase;

class ProcessTest extends TestCase
{
    public function testBuilder()
    {
        $process = new Process('dir');

        $this->assertInstanceOf(
            'Symfony\\Component\\Process\\ProcessBuilder',
            $process->builder()
        );
    }

    public function testGlob()
    {
        $process = new Process('dir');

        touch('a');
        touch('a.php');
        touch('b');
        touch('b.php');

        $process->glob('*.php');

        $builder = $this->getPropertyValue($process, 'builder');
        $args = $this->getPropertyValue($builder, 'arguments');

        $this->assertNotContains('a', $args);
        $this->assertContains('a.php', $args);
        $this->assertNotContains('b', $args);
        $this->assertContains('b.php', $args);
    }

    public function testRun()
    {
        $output = '';
        $process = new Process('composer');

        $process->arg('--version')->output(
            function ($buffer) use (&$output) {
                $output .= $buffer;
            }
        );

        $this->assertSame(0, $process->run());
        $this->assertRegExp('/Composer version/', $output);
    }

    public function testRunError()
    {
        $output = '';
        $process = new Process('composer');

        $process->arg('test')->error(
            function ($buffer) use (&$output) {
                $output .= $buffer;
            }
        );

        $this->assertSame(1, $process->run());
        $this->assertRegExp('/Command "test" is not defined/', $output);
    }

    public function testRunStream()
    {
        $stream = fopen('php://memory', 'w+');
        $process = new Process('composer');

        $process->arg('--version')->output($process->stream($stream))->run();

        fseek($stream, 0, 0);

        $this->assertRegExp('/Composer version/', fgets($stream));

        fclose($stream);
    }
}
