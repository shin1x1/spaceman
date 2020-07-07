<?php

declare(strict_types=1);

namespace Koriym\Spaceman;

use PhpParser\BuilderFactory;
use PhpParser\Lexer\Emulative;
use PhpParser\Node;
use PhpParser\NodeFinder;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\Parser\Php7;
use PhpParser\PrettyPrinter\Standard;

final class Spaceman
{
    /**
     * @var TargetNameCollection
     */
    private $names;

    /**
     * @param TargetNameCollection $names
     */
    public function __construct(TargetNameCollection $names)
    {
        $this->names = $names;
    }

    /**
     * return namespaced code
     */
    public function __invoke(string $code, string $namespace): string
    {
        [$oldStmts, $oldTokens, $newStmts] = $this->getAstToken($code);
        if ($this->hasNamespace($oldStmts)) {
            return '';
        }

        [$newStmts, $declareStmts, $useStmts] = $this->resolveName($newStmts, $namespace);
        $newStmts = $this->addNamespace($newStmts, $declareStmts, $useStmts, $namespace);
        assert_options(ASSERT_ACTIVE, 0);
        $code = (new Standard)->printFormatPreserving($newStmts, $oldStmts, $oldTokens);
        assert_options(ASSERT_ACTIVE, 1);

        return $this->addPhpEol($code);
    }

    private function getAstToken(string $code): array
    {
        $lexer = new Emulative([
            'usedAttributes' => [
                'comments',
                'startLine', 'endLine',
                'startTokenPos', 'endTokenPos',
            ],
        ]);
        $parser = new Php7($lexer);
        $traverser = new NodeTraverser;
        $traverser->addVisitor(new NodeVisitor\CloningVisitor());
        $oldStmts = $parser->parse($code);
        $oldTokens = $lexer->getTokens();
        $newStmts = $traverser->traverse($oldStmts);

        return [$oldStmts, $oldTokens, $newStmts];
    }

    /**
     * @return Node[]
     */
    private function addNamespace(array $ast, array $declareStmts, array $useStmts, string $namespace): array
    {
        $stmts = count($declareStmts) > 0 ? $declareStmts : [];

        $stmts[] = (new BuilderFactory())->namespace($namespace)->getNode();
        $stmts = array_merge($stmts, $useStmts, $ast);

        return $stmts;
    }

    private function hasNamespace(array $ast): bool
    {
        $finder = new NodeFinder();
        return $finder->findFirstInstanceOf($ast, Node\Stmt\Namespace_::class) !== null;
    }

    /**
     * @return array{Node[], Node\Stmt\Declare_[], Node\Stmt\Use_[]}
     */
    private function resolveName($ast, string $namespace): array
    {
        $nameResolver = new NameResolver(null, [
            'preserveOriginalNames' => true,
            'replaceNodes' => false,
        ]);
        $nodeTraverser = new NodeTraverser;
        $nodeTraverser->addVisitor($nameResolver);
        $useVisitor = new UseCollectVisitor($this->names);
        $nodeTraverser->addVisitor($useVisitor);
        $declareVisitor = new DeclareCollectVisitor();
        $nodeTraverser->addVisitor($declareVisitor);
        $travesedAst = $nodeTraverser->traverse($ast);

        return [$travesedAst, $declareVisitor->declares, $useVisitor->uses->getUseStmts($namespace)];
    }

    private function addPhpEol(string $code): string
    {
        if (substr($code, -1) !== "\n") {
            $code .= PHP_EOL;
        }

        return $code;
    }
}
