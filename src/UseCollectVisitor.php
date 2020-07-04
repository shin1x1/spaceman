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
        if (!$node instanceof Node\Name) {
            return;
        }

        $resolvedName = $node->getAttribute('resolvedName');
        if (!$resolvedName instanceof Node\Name\FullyQualified) {
            return;
        }
        if (count($resolvedName->parts) !== 1) {
            return;
        }

        $fullQualifiedTarget = $node->toString();
        if (function_exists($fullQualifiedTarget) || defined($fullQualifiedTarget)) {
            return;
        }

        $target = $node->getAttribute('resolvedName')->getLast();
        $targetNames = $this->names->get($target);
        if ($targetNames) {
            foreach ($targetNames as $targetName) {
                $this->uses->add($targetName);
            }
        } else {
            $this->uses->add(new TargetName($target, ''));
        }
    }
}
