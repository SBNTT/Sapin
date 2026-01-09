<?php

declare(strict_types=1);

namespace Sapin\Engine\Renderer;

use Closure;
use Generator;
use Sapin\Engine\AsyncComponentLoaderInterface;
use Sapin\Engine\Component;
use Sapin\Engine\Renderable;
use Sapin\Engine\SapinException;
use Stringable;
use function count;
use function is_int;
use function sprintf;

final class Renderer
{
    /** @var array<string|int|float|bool|Stringable|ComponentRenderNode|ComponentLoaderRenderNode> */
    private array $nodes;

    private bool $streaming;

    public int $cyclesCount = 1;

    public function __construct(bool $streaming)
    {
        $this->nodes = [];
        $this->streaming = $streaming;
    }

    /**
     * @param ?Closure(string): (Generator<string|int|float|bool|Stringable|ComponentRenderNode>|false) $slotRenderer
     * @throws SapinException
     */
    public function render(Renderable $component, ?Closure $slotRenderer): void
    {
        $this->discoverNodes($component->render($slotRenderer));

        while (count($this->nodes) > 0) {
            $this->cycle();
        }
    }

    /**
     * @phpstan-assert Renderable $component
     * @throws SapinException
     */
    public static function ensureIsRenderable(Component $component): void
    {
        if (!($component instanceof Renderable)) {
            throw new SapinException(sprintf(
                'This is not a valid component to render: "%s". Subtype of Sapin\\Renderable expected',
                $component::class,
            ));
        }
    }

    /**
     * @param Generator<string|int|float|bool|Stringable|ComponentRenderNode|ComponentLoaderRenderNode> $nodes
     * @throws SapinException
     */
    private function discoverNodes(Generator $nodes): void
    {
        foreach ($nodes as $node) {
            if ($node instanceof ComponentRenderNode) {
                $this->discoverComponentNodes($node, $this);
            } elseif ($node instanceof ComponentLoaderRenderNode) {
                if ($node->loader instanceof AsyncComponentLoaderInterface && !$node->preLoaded) {
                    $node->loader->preLoad();
                    $this->streaming = false;
                    $node->preLoaded = true;
                    $this->nodes[] = $node;
                } else {
                    $this->discoverComponentNodes($node, $this);
                }
            } elseif ($this->streaming) {
                echo $node;
            } else {
                $this->nodes[] = $node;
            }
        }
    }

    /** @throws SapinException */
    private function discoverComponentNodes(ComponentRenderNode|ComponentLoaderRenderNode $node, self $context): void
    {
        $subComponent = $node instanceof ComponentLoaderRenderNode
            ? $node->loader->load()
            : $node->component;

        self::ensureIsRenderable($subComponent);

        $context->discoverNodes($subComponent->render($node->slotRenderer));
    }

    /** @throws SapinException */
    private function cycle(): void
    {
        ++$this->cyclesCount;
        $this->streaming = true;

        $index = 0;
        $splices = [];
        while ($index < count($this->nodes)) {
            $node = $this->nodes[$index];

            if ($node instanceof ComponentRenderNode || $node instanceof ComponentLoaderRenderNode) {
                $this->discoverComponentNodes($node, $subRenderer = new self($this->streaming));

                $this->streaming = $subRenderer->streaming;
                $splices[$index] = $subRenderer->nodes;

                ++$index;
            } elseif ($this->streaming) {
                echo $node;
                array_shift($this->nodes);
            }
        }

        // replace discovered components trees in reverse order
        end($splices);
        while (is_int($index = key($splices)) && ($nodes = current($splices)) !== false) {
            array_splice($this->nodes, $index, 1, $nodes);
            prev($splices);
        }
    }
}
