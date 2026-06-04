<?php

/**
 * SCSSPHP
 *
 * @copyright 2012-2020 Leaf Corcoran
 *
 * @license http://opensource.org/licenses/MIT MIT
 *
 * @link http://scssphp.github.io/scssphp
 */

namespace ScssPhp\ScssPhp\Ast\Sass\Statement;

use League\Uri\Contracts\UriInterface;
use ScssPhp\ScssPhp\Ast\Sass\ConfiguredVariable;
use ScssPhp\ScssPhp\Ast\Sass\Statement;
use ScssPhp\ScssPhp\Visitor\StatementVisitor;
use SourceSpan\FileSpan;

/**
 * A `@use` rule.
 *
 * @internal
 */
final class UseRule implements Statement
{
    private readonly UriInterface $url;

    /**
     * The namespace under which the module's members are made available, or
     * `null` if the members are available without a namespace.
     */
    private readonly ?string $namespace;

    /**
     * The variables configured for the loaded module via a `with` clause.
     *
     * @var list<ConfiguredVariable>
     */
    private readonly array $configuration;

    private readonly FileSpan $span;

    /**
     * @param list<ConfiguredVariable> $configuration
     */
    public function __construct(UriInterface $url, ?string $namespace, FileSpan $span, array $configuration = [])
    {
        $this->url = $url;
        $this->namespace = $namespace;
        $this->span = $span;
        $this->configuration = $configuration;
    }

    public function getUrl(): UriInterface
    {
        return $this->url;
    }

    public function getNamespace(): ?string
    {
        return $this->namespace;
    }

    /**
     * @return list<ConfiguredVariable>
     */
    public function getConfiguration(): array
    {
        return $this->configuration;
    }

    public function getSpan(): FileSpan
    {
        return $this->span;
    }

    public function accept(StatementVisitor $visitor)
    {
        return $visitor->visitUseRule($this);
    }

    public function __toString(): string
    {
        $buffer = '@use ' . $this->url;

        $basename = $this->url->getPath();
        $dot = strrpos($basename, '.');
        if ($this->namespace !== substr($basename, 0, $dot === false ? \strlen($basename) : $dot)) {
            $buffer .= ' as ' . ($this->namespace ?? '*');
        }

        if ($this->configuration !== []) {
            $buffer .= ' with (' . implode(', ', $this->configuration) . ')';
        }

        return $buffer . ';';
    }
}
