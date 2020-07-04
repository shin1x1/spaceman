<?php

declare(strict_types=1);

namespace Koriym\Spaceman;

use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor;
use PhpParser\ParserFactory;

final class TargetNameCollector
{
    /**
     * @var TargetNameCollection
     */
    private $names;

    public function __construct(TargetNameCollection $names)
    {
        $this->names = $names;
    }

    public function __invoke(string $code, string $namespace): void
    {
        $stmts = $this->parse($code);
        if ($this->hasNamespace($stmts)) {
            return;
        }

        $names = $this->collectTargetNames($stmts, $namespace);
        foreach ($names as $name) {
            $this->names->add($name);
        }
    }

    /**
     * @return Node\Stmt[]
     */
    private function parse(string $code): array
    {
        $parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);
        return $parser->parse($code);
    }

    /**
     * @param Node\Stmt[] $stmts
     * @return bool
     */
    private function hasNamespace(array $stmts): bool
    {
        $traverser = new NodeTraverser();
        $NsCheckerVisitor = new NsCheckerVisitor;
        $traverser->addVisitor($NsCheckerVisitor);
        $traverser->traverse($stmts);

        return $NsCheckerVisitor->hasNamespace;
    }

    /**
     * @param Node\Stmt[] $stmts
     * @param string      $namespace
     * @return TargetName[]
     */
    private function collectTargetNames(array $stmts, string $namespace): array
    {
        $nameResolver = new NodeVisitor\NameResolver();
        $nodeTraverser = new NodeTraverser;
        $nodeTraverser->addVisitor($nameResolver);
        $collectVisitor = new TargetNameCollectVisitor($namespace);
        $nodeTraverser->addVisitor($collectVisitor);
        $nodeTraverser->traverse($stmts);

        return $collectVisitor->targetNames;
    }
}
