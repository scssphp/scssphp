These specs are for CSS files that are imported by Sass, using `@import "foo"`
to import `foo.css`. These files should be parsed as plain CSS, and should not
allow any Sass-specific features.

As a rule, anything in the plain CSS files that would be interpreted differently
if it were SCSS should produce an error. Although some of these could
theoretically be valid CSS, such as `@import url("#{foo}")`, it's much more
likely that they're a mistake on the user's part that they should be notified
of.
