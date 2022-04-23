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

namespace ScssPhp\ScssPhp\Ast\Sass;

use ScssPhp\ScssPhp\Exception\SassFormatException;
use ScssPhp\ScssPhp\Exception\SassScriptException;
use ScssPhp\ScssPhp\Logger\LoggerInterface;
use ScssPhp\ScssPhp\Parser\ScssParser;
use ScssPhp\ScssPhp\SourceSpan\FileSpan;

/**
 * An argument declaration, as for a function or mixin definition.
 *
 * @internal
 */
final class ArgumentDeclaration implements SassNode
{
    /**
     * @var list<Argument>
     * @readonly
     */
    private $arguments;

    /**
     * @var string|null
     * @readonly
     */
    private $restArgument;

    /**
     * @var FileSpan
     * @readonly
     */
    private $span;

    /**
     * @param list<Argument> $arguments
     * @param FileSpan      $span
     * @param string|null $restArgument
     */
    public function __construct(array $arguments, FileSpan $span, ?string $restArgument = null)
    {
        $this->arguments = $arguments;
        $this->restArgument = $restArgument;
        $this->span = $span;
    }

    public static function createEmpty(FileSpan $span): ArgumentDeclaration
    {
        return new self([], $span);
    }

    /**
     * Parses an argument declaration from $contents, which should be of the
     * form `@rule name(args) {`.
     *
     * If passed, $url is the name of the file from which $contents comes.
     *
     * @throws SassFormatException if parsing fails.
     */
    public static function parse(string $contents, ?LoggerInterface $logger = null, ?string $url = null): ArgumentDeclaration
    {
        return (new ScssParser($contents, $logger, $url))->parseArgumentDeclaration();
    }

    public function isEmpty(): bool
    {
        return \count($this->arguments) === 0 && $this->restArgument === null;
    }

    /**
     * @return list<Argument>
     */
    public function getArguments(): array
    {
        return $this->arguments;
    }

    public function getRestArgument(): ?string
    {
        return $this->restArgument;
    }

    public function getSpan(): FileSpan
    {
        return $this->span;
    }

    /**
     * @param int                  $positional
     * @param array<string, mixed> $names Only keys are relevant
     *
     * @throws SassScriptException if $positional and $names aren't valid for this argument declaration.
     */
    public function verify(int $positional, array $names): void
    {
        $nameUsed = 0;

        foreach ($this->arguments as $i => $argument) {
            if ($i < $positional) {
                if (isset($names[$argument->getName()])) {
                    $originalName = $this->originalArgumentName($argument->getName());
                    throw new SassScriptException(sprintf('Argument $%s was passed both by position and by name.', $originalName));
                }
            } elseif (isset($names[$argument->getName()])) {
                $nameUsed++;
            } elseif ($argument->getDefaultValue() === null) {
                $originalName = $this->originalArgumentName($argument->getName());
                throw new SassScriptException(sprintf('Missing argument $%s', $originalName));
            }
        }

        if ($this->restArgument !== null) {
            return;
        }

        if ($positional > \count($this->arguments)) {
            $message = sprintf(
                'Only %d %sargument%s allowed, but %d %s passed.',
                \count($this->arguments),
                empty($names) ? '' : 'positional ',
                \count($this->arguments) === 1 ? '' : 's',
                $positional,
                $positional === 1 ? 'was' : 'were'
            );
            throw new SassScriptException($message);
        }

        if ($nameUsed < \count($names)) {
            $unknownNames = array_values(array_diff(array_keys($names), array_map(function ($argument) {
                return $argument->getName();
            }, $this->arguments)));
            $lastName = array_pop($unknownNames);
            $message = sprintf(
                'No argument%s named $%s%s.',
                $unknownNames ? 's' : '',
                $unknownNames ? implode(', $', $unknownNames) . ' or $' : '',
                $lastName
            );
            throw new SassScriptException($message);
        }
    }

    private function originalArgumentName(string $name): string
    {
        if ($name === $this->restArgument) {
            $text = $this->span->getText();
            $lastDollar = strrpos($text, '$');
            assert($lastDollar !== false);
            $fromDollar = substr($text, $lastDollar);
            $dot = strrpos($fromDollar, '.');
            assert($dot !== false);

            return substr($fromDollar, 0, $dot);
        }

        foreach ($this->arguments as $argument) {
            if ($argument->getName() === $name) {
                return $argument->getOriginalName();
            }
        }

        throw new \InvalidArgumentException("This declaration has no argument named \"\$$name\".");
    }

    /**
     * Returns whether $positional and $names are valid for this argument
     * declaration.
     *
     * @param int                  $positional
     * @param array<string, mixed> $names Only keys are relevant
     *
     * @return bool
     */
    public function matches(int $positional, array $names): bool
    {
        $nameUsed = 0;

        foreach ($this->arguments as $i => $argument) {
            if ($i < $positional) {
                if (isset($names[$argument->getName()])) {
                    return false;
                }
            } elseif (isset($names[$argument->getName()])) {
                $nameUsed++;
            } elseif ($argument->getDefaultValue() === null) {
                return false;
            }
        }

        if ($this->restArgument !== null) {
            return true;
        }

        if ($positional > \count($this->arguments)) {
            return false;
        }

        if ($nameUsed < \count($names)) {
            return false;
        }

        return true;
    }

    public function __toString(): string
    {
        $parts = [];
        foreach ($this->arguments as $arg) {
            $parts[] = "\$$arg";
        }
        if ($this->restArgument !== null) {
            $parts[] = "\$$this->restArgument...";
        }

        return implode(', ', $parts);
    }
}
