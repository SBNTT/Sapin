<?php

declare(strict_types=1);

namespace Sapin\Engine\Parser\Template\Stage3;

use Sapin\Engine\Parser\Template\Stage3\Node as Stage3;
use Sapin\Engine\SapinException;
use function in_array;
use function sprintf;

abstract class AttributesListChecker
{
    /** @var SpecialAttribute[][] */
    private const CONFLICT_GROUPS = [
        [SpecialAttribute::IF, SpecialAttribute::ELSE_IF, SpecialAttribute::ELSE],
        [SpecialAttribute::FOR, SpecialAttribute::FOREACH],
    ];

    /**
     * @param array<Stage3\StaticAttributeNode|Stage3\DynamicAttributeNode> $attributes
     * @param Stage3\SpecialAttributeNode[] $specialAttributes
     * @throws SapinException
     */
    public static function checkAttributes(array $attributes, array $specialAttributes): void
    {
        self::checkSpecialAttributes($specialAttributes);
    }

    /**
     * @param Stage3\SpecialAttributeNode[] $specialAttributes
     * @throws SapinException
     */
    private static function checkSpecialAttributes(array $specialAttributes): void
    {
        /** @var SpecialAttribute[] $seen */
        $seen = [];

        foreach ($specialAttributes as $attr) {
            if (in_array($attr->kind, $seen, true)) {
                throw new SapinException(sprintf(
                    'You cannot use the special attribute "%s" twice',
                    $attr->kind->value,
                ));
            }

            foreach (self::CONFLICT_GROUPS as $group) {
                if (in_array($attr->kind, $group, true)) {
                    foreach ($group as $other) {
                        if ($other !== $attr->kind && in_array($other, $seen, true)) {
                            throw new SapinException(sprintf(
                                'You cannot use the special attributes "%s" and "%s" together',
                                $other->value,
                                $attr->kind->value,
                            ));
                        }
                    }
                }
            }

            $seen[] = $attr->kind;
        }
    }
}
