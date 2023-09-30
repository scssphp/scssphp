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

namespace ScssPhp\ScssPhp\Ast\Css;

use ScssPhp\ScssPhp\SourceSpan\FileSpan;
use ScssPhp\ScssPhp\Value\SassString;
use ScssPhp\ScssPhp\Value\Value;
use ScssPhp\ScssPhp\Visitor\ModifiableCssVisitor;

/**
 * A modifiable version of {@see CssDeclaration} for use in the evaluation step.
 *
 * @internal
 */
final class ModifiableCssDeclaration extends ModifiableCssNode implements CssDeclaration
{
    /**
     * @var CssValue<string>
     */
    private readonly CssValue $name;

    /**
     * @var CssValue<Value>
     */
    private readonly CssValue $value;

    private readonly bool $parsedAsCustomProperty;

    private readonly FileSpan $valueSpanForMap;

    private readonly FileSpan $span;

    /**
     * @param CssValue<string> $name
     * @param CssValue<Value> $value
     */
    public function __construct(CssValue $name, CssValue $value, FileSpan $span, bool $parsedAsCustomProperty, ?FileSpan $valueSpanForMap = null)
    {
        $this->name = $name;
        $this->value = $value;
        $this->parsedAsCustomProperty = $parsedAsCustomProperty;
        $this->valueSpanForMap = $valueSpanForMap ?? $value->getSpan();
        $this->span = $span;

        if ($parsedAsCustomProperty) {
            if (!$this->isCustomProperty()) {
                throw new \InvalidArgumentException('parsedAsCustomProperty must be false if name doesn\'t begin with "--".');
            }

            if (!$value->getValue() instanceof SassString) {
                throw new \InvalidArgumentException(sprintf('If parsedAsCustomProperty is true, value must contain a SassString (was %s).', get_debug_type($value->getValue())));
            }
        }
    }

    public function getName(): CssValue
    {
        return $this->name;
    }

    public function getValue(): CssValue
    {
        return $this->value;
    }

    public function isParsedAsCustomProperty(): bool
    {
        return $this->parsedAsCustomProperty;
    }

    public function getValueSpanForMap(): FileSpan
    {
        return $this->valueSpanForMap;
    }

    public function getSpan(): FileSpan
    {
        return $this->span;
    }

    public function isCustomProperty(): bool
    {
        return str_starts_with($this->name->getValue(), '--');
    }

    public function accept(ModifiableCssVisitor $visitor)
    {
        return $visitor->visitCssDeclaration($this);
    }
}
