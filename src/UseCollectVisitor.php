<?php

declare(strict_types=1);

namespace Koriym\Spaceman;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

class UseCollectVisitor extends NodeVisitorAbstract
{
    /**
     * @var TargetNameCollection
     */
    private $names;

    /**
     * @var UseCollection
     */
    public $uses;

    public function __construct(TargetNameCollection $names)
    {
        $this->names = $names;
        $this->uses = new UseCollection();
    }

    public function enterNode(Node $node): void
    {
        if ($node instanceof Node\Expr\New_) {
            $this->addForClass($node->class);
            return;
        }

        if ($node instanceof Node\Stmt\Class_) {
            $this->addForClass($node->extends);

            if (count($node->implements) > 0) {
                foreach ($node->implements as $implement) {
                    $this->addForClass($implement);
                }
            }
            return;
        }

        if ($node instanceof Node\Stmt\Interface_) {
            if (count($node->extends) > 0) {
                foreach ($node->extends as $extent) {
                    $this->addForClass($extent);
                }
            }
            return;
        }

        if ($node instanceof Node\Stmt\TraitUse) {
            foreach ($node->traits as $trait) {
                $this->addForClass($trait);
            }
            return;
        }

        if ($node instanceof Node\Stmt\Property) {
            $this->addForClass($node->type);
            return;
        }

        if ($node instanceof Node\Stmt\ClassMethod) {
            foreach ($node->params as $param) {
                $this->addForClass($param->type);
            }

            $this->addForClass($node->returnType);
            return;
        }

        if ($node instanceof Node\Expr\StaticPropertyFetch) {
            $this->addForClass($node->class);
            return;
        }

        if ($node instanceof Node\Expr\ClassConstFetch) {
            $this->addForClass($node->class);
            return;
        }

        if ($node instanceof Node\Expr\StaticCall) {
            $this->addForClass($node->class);
            return;
        }

        if ($node instanceof Node\Expr\ClassConstFetch) {
            $this->addForClass($node->class);
            return;
        }

        if ($node instanceof Node\Expr\ConstFetch) {
            $this->addForConst($node->name);
            return;
        }

        if ($node instanceof Node\Expr\FuncCall) {
            $this->addForFunction($node->name);
        }
    }

    private function getGlobalName(Node\Name $node): string
    {
        $resolvedName = $node->getAttribute('resolvedName');
        if (!$resolvedName instanceof Node\Name\FullyQualified) {
            return '';
        }

        if (count($resolvedName->parts) !== 1) {
            return '';
        }

        return $node->getAttribute('resolvedName')->getLast();
    }

    private function addForClass(Node $node = null): void
    {
        if ($node instanceof Node\Name) {
            $name = $this->getGlobalName($node);
            if ($name === '') {
                return;
            }

            $targetNames = $this->names->get($name);
            if ($targetNames) {
                foreach ($targetNames as $targetName) {
                    $this->uses->add($targetName);
                }
            } else {
                $this->uses->add(new TargetName($name, ''));
            }
        }
    }

    private function addForConst(Node $node): void
    {
        if ($node instanceof Node\Name) {
            $name = $this->getGlobalName($node);
            if ($name === '') {
                return;
            }

            if (defined($name)) {
                return;
            }

            $targetNames = $this->names->get($name);
            if ($targetNames) {
                foreach ($targetNames as $targetName) {
                    $this->uses->add($targetName);
                }
            }
        }
    }

    private function addForFunction(Node $node): void
    {
        if ($node instanceof Node\Name) {
            $name = $this->getGlobalName($node);
            if ($name === '') {
                return;
            }

            if (file_exists($name)) {
                return;
            }

            $targetNames = $this->names->get($name);
            if ($targetNames) {
                foreach ($targetNames as $targetName) {
                    $this->uses->add($targetName);
                }
            }
        }
    }
}
