<?php

declare(strict_types=1);

namespace Koriym\Spaceman;

use JsonSerializable;
use PhpParser\Node\Stmt\Use_;

final class TargetName implements JsonSerializable
{
    public const TYPE_NORMAL = 'normal';
    public const TYPE_FUNCTION = 'function';
    public const TYPE_CONST = 'const';

    /**
     * @var string
     */
    private $identifier;

    /**
     * @var string
     */
    private $namespace;

    /**
     * @var int
     */
    private $type;

    /**
     * @param string $identifier
     * @param string $namespace
     * @param int $type
     */
    public function __construct(string $identifier, string $namespace, int $type = Use_::TYPE_NORMAL)
    {
        $this->identifier = $identifier;
        $this->namespace = $namespace;
        $this->type = $type;
    }

    public function getFullQualifiedName(): string
    {
        if ($this->namespace === '') {
            return $this->identifier;
        }

        return $this->namespace . '\\' . $this->identifier;
    }

    public function getName(): string
    {
        return $this->identifier;
    }

    public function getNamespace(): string
    {
        return $this->namespace;
    }

    public function getType(): int
    {
        return $this->type;
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize()
    {
        return [
            'name' => $this->identifier,
            'namespace' => $this->namespace,
            'type' => $this->type,
        ];
    }
}
