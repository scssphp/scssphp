Although `:before`, `:after`, `:first-line`, and `:first-letter` are
syntactically pseudo-classes, CSS treats them as pseudo-elements. This means
that they're required to appear *after* real pseudo-classes in selectors. These
specs verify that that's preserved with `@extend`.
