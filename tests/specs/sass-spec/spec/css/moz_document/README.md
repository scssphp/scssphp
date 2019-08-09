`@-moz-document` is a Firefox-specific at-rule that was originally based on a
draft of the [CSS Conditional Rules][] module. It's syntactically unusual in
that it defines a url-prefix() "function" that takes an unquoted URL. This can't
be parsed using Sass's normal unknown-at-rule parsing, since it may contain the
characters `//` that should not be interpreted as a single-line comment.

[CSS Conditional Rules]: https://www.w3.org/TR/css3-conditional/

However, support for `@-moz-document` is [being removed from Firefox][] for
security concerns. Sass support for them should be deprecated and eventually
removed; see [issue 2529][] for details. These specs track deprecated support
for the old syntax, as well as the special case of `@-moz-document url-prefix()`
which is still supported at time of writing as a hack for targeting CSS at
Firefox only.

[being removed from Firefox]: https://www.fxsitecompat.com/en-CA/docs/2018/moz-document-support-has-been-dropped-except-for-empty-url-prefix/
[issue 2529]: https://github.com/sass/sass/issues/2529
