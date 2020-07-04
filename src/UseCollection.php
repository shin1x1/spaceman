<?php

declare(strict_types=1);

namespace Koriym\Spaceman;

use PhpParser\Node\Name;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\Stmt\UseUse;

final class UseCollection
{
    /**
     * @var TargetName[]
     */
    private $uses;

    /**
     * @param TargetName[] $uses
     */
    public function __construct(array $uses = [])
    {
        $this->uses = $uses;
    }

    /**
     * @param TargetName $targetName
     */
    public function add(TargetName $targetName): void
    {
        foreach ($this->uses as $e) {
            if ($e->getName() === $targetName->getName() && $e->getType() === $targetName->getType()) {
                return;
            }
        }

        $this->uses[] = $targetName;
    }

    /**
     * @param string $namespace
     * @return Stmt\Use_[]
     */
    public function getUseStmts(string $namespace): iterable
    {
        // exclude target namespace
        $filtered = array_filter($this->uses, function (TargetName $targetName) use ($namespace) {
            return $namespace !== $targetName->getNamespace();
        });

        // sort
        usort($filtered, function (TargetName $a, TargetName $b) {
            if ($a->getType() !== $b->getType()) {
                return $a->getType() > $b->getType();
            }

            return $a->getFullQualifiedName() > $b->getFullQualifiedName();
        });

        $uses = [];
        foreach ($filtered as $name) {
            /** @var TargetName $name */
            $uses[] = new Use_(
                [new UseUse(new Name($name->getFullQualifiedName()))],
                $name->getType()
            );
        }


        return $uses;
    }
}
