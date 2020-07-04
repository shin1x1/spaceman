<?php

declare(strict_types=1);

namespace Koriym\Spaceman;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;
use RuntimeException;

class TargetNameCollectVisitor extends NodeVisitorAbstract
{
    /**
     * @var string
     */
    private $namespace;

    /**
     * @var TargetName[]
     */
    public $targetNames;

    /**
     * @param string $namespace
     */
    public function __construct(string $namespace)
    {
        $this->namespace = $namespace;
        $this->targetNames = [];
    }

    public function enterNode(Node $node): void
    {
        if ($node instanceof Node\Stmt\Class_
            || $node instanceof Node\Stmt\Interface_
            || $node instanceof Node\Stmt\Trait_) {
            $this->addTargetName($node, Node\Stmt\Use_::TYPE_NORMAL);
            return;
        }

        if ($node instanceof Node\Stmt\Const_) {
            $this->addTargetName($node, Node\Stmt\Use_::TYPE_CONSTANT);
            return;
        }

        if ($node instanceof Node\Stmt\Function_) {
            $this->addTargetName($node, Node\Stmt\Use_::TYPE_FUNCTION);
            return;
        }
    }

    private function addTargetName(Node $node, int $type): void
    {
        $this->targetNames[] = new TargetName(
            $this->getNamespacedName($node)->getLast(),
            $this->namespace,
            $type
        );
    }

    private function getNamespacedName(Node $node): Node\Name
    {
        if ($node instanceof Node\Stmt\Class_
            || $node instanceof Node\Stmt\Interface_
            || $node instanceof Node\Stmt\Trait_
            || $node instanceof Node\Stmt\Function_) {
            return $node->namespacedName;
        }
        if ($node instanceof Node\Stmt\Const_) {
            return $node->consts[0]->namespacedName;
        }

        throw new RuntimeException();
    }
}
