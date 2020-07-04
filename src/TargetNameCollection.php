<?php

declare(strict_types=1);

namespace Koriym\Spaceman;

use JsonSerializable;

final class TargetNameCollection implements JsonSerializable
{
    /**
     * @var TargetName[]
     */
    private $names = [];

    /**
     * @param TargetName[] $names
     */
    public function __construct(array $names = [])
    {
        $this->names = $names;
    }

    public function add(TargetName $name): void
    {
        $this->names[] = $name;
    }

    /**
     * @param string $name
     * @return TargetName|TargetName[]
     */
    public function get(string $name)
    {
        $matched = [];
        foreach ($this->names as $targetName) {
            if ($targetName->getName() === $name) {
                $matched[] = $targetName;
            }
        }

        return $matched;
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize()
    {
        return $this->names;
    }
}
