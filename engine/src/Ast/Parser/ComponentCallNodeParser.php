<?php

namespace Sapin\Engine\Ast\Parser;

use DOMAttr;
use DOMNode;
use ReflectionClass;
use ReflectionParameter;
use Sapin\Engine\Ast\Node\Template\ComponentCallNode;
use Sapin\Engine\Ast\Node\Template\TemplateNode;

final class ComponentCallNodeParser
{
    /**
     * @throws \ReflectionException
     */
    public function tryParse(DOMNode $domNode, TemplateNode $templateNode): ?ComponentCallNode
    {
        $componentFqn = $templateNode->getUse($domNode->nodeName);
        if ($componentFqn === null) {
            return null;
        }

        // Usage of ReflectionClass may cause a sub compilation of the `$componentFqn` component
        $componentConstructorParameters = array_reduce(
            (new ReflectionClass($componentFqn))->getConstructor()?->getParameters() ?? [],
            function (array $props, ReflectionParameter $parameter) {
                $props[$parameter->getName()] = (string)$parameter->getType() ?: null;

                return $props;
            },
            [],
        );

        /** @var array<string, string> $props */
        $props = [];

        /** @var DOMAttr $attribute */
        foreach ($domNode->attributes ?? [] as $attribute) {
            if (!str_starts_with($attribute->name, ':')) {
                continue;
            }

            $attributeName = substr($attribute->name, 1);

            if (!array_key_exists($attributeName, $componentConstructorParameters)
                || in_array($attributeName, TemplateNodeParser::RESERVED_DYNAMIC_ATTRIBUTES)
            ) {
                continue;
            }

            $props[$attributeName] = match($componentConstructorParameters[$attributeName]) {
                'string' => sprintf("'%s'", str_replace("'", "\'", $attribute->value)),
                default => $attribute->value,
            };
        }

        return new ComponentCallNode($componentFqn, $props);
    }
}
