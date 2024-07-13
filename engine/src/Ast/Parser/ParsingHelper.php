<?php

namespace Sapin\Engine\Ast\Parser;

use Sapin\Engine\Ast\Node\AbstractNode;

abstract class ParsingHelper
{
    /**
     * @template T
     *
     * @param class-string<T> $class
     *
     * @return T[]
     */
    public static function findFirstDescendantsNodesOfType(AbstractNode $node, string $class): array
    {
        if ($node instanceof $class) {
            return [$node];
        }

        $descendants = [];
        foreach ($node->getChildren() as $child) {
            foreach (self::findFirstDescendantsNodesOfType($child, $class) as $descendant) {
                $descendants[] = $descendant;
            }
        }

        return $descendants;
    }
}
