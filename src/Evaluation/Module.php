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

namespace ScssPhp\ScssPhp\Evaluation;

use League\Uri\Contracts\UriInterface;
use ScssPhp\ScssPhp\SassCallable\SassCallable;
use ScssPhp\ScssPhp\Value\Value;

/**
 * A built-in module in the `sass:` namespace (for example `sass:math`).
 *
 * Built-in modules are immutable: they expose a fixed set of functions and
 * variables and cannot be configured. Userland modules are not supported yet.
 *
 * @internal
 */
final class Module
{
    private readonly UriInterface $url;

    /**
     * The functions exposed by this module, keyed by their hyphenated name.
     *
     * @var array<string, SassCallable>
     */
    private readonly array $functions;

    /**
     * The variables exposed by this module, keyed by their name without the
     * leading `$`.
     *
     * @var array<string, Value>
     */
    private readonly array $variables;

    /**
     * @param array<string, SassCallable> $functions
     * @param array<string, Value>        $variables
     */
    public function __construct(UriInterface $url, array $functions, array $variables = [])
    {
        $this->url = $url;
        $this->functions = $functions;
        $this->variables = $variables;
    }

    public function getUrl(): UriInterface
    {
        return $this->url;
    }

    public function getFunction(string $name): ?SassCallable
    {
        return $this->functions[$name] ?? null;
    }

    public function functionExists(string $name): bool
    {
        return isset($this->functions[$name]);
    }

    public function getVariable(string $name): ?Value
    {
        return $this->variables[$name] ?? null;
    }

    public function variableExists(string $name): bool
    {
        return isset($this->variables[$name]);
    }
}
