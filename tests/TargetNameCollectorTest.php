<?php

declare(strict_types=1);

namespace Koriym\Spaceman;

use PhpParser\Node\Stmt\Use_;
use PHPUnit\Framework\TestCase;

class TargetNameCollectorTest extends TestCase
{
    public function test__invoke_with_Base() : void
    {
        $targetNames = new TargetNameCollection();
        $sut = new TargetNameCollector($targetNames);

        $code = file_get_contents(__DIR__ . '/TargetNameCollectorTest/Base.php');
        if (! is_string($code)) {
            throw new \RuntimeException;
        }
        $namespace = 'Newname\Space';
        ($sut)($code, $namespace);

        $expected = new TargetNameCollection();
        $expected->add(new TargetName('BaseClass', $namespace, Use_::TYPE_NORMAL));
        $expected->add(new TargetName('BaseInterface', $namespace, Use_::TYPE_NORMAL));
        $expected->add(new TargetName('BaseTrait', $namespace, Use_::TYPE_NORMAL));
        $expected->add(new TargetName('BASE_CONST', $namespace, Use_::TYPE_CONSTANT));
        $expected->add(new TargetName('base_function', $namespace, Use_::TYPE_FUNCTION));
        $expected->add(new TargetName('BaseClass', $namespace, Use_::TYPE_FUNCTION));

        $this->assertEquals($expected, $targetNames);
    }

    public function test__invoke_with_Foo() : void
    {
        $targetNames = new TargetNameCollection();
        $sut = new TargetNameCollector($targetNames);

        $code = file_get_contents(__DIR__ . '/TargetNameCollectorTest/Sub1/Foo.php');
        if (! is_string($code)) {
            throw new \RuntimeException;
        }
        $namespace = 'Newname\Space\Sub1';
        ($sut)($code, $namespace);

        $expected = new TargetNameCollection();
        $expected->add(new TargetName('Foo', $namespace, Use_::TYPE_NORMAL));

        $this->assertEquals($expected, $targetNames);
    }
}
