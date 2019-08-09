use strict;
use warnings;

use File::chdir;



my @input = (
	['01_literal', qq(literal)],
	['02_double_quoted', qq("dquoted")],
	['03_single_quoted', qq('squoted')],
	['04_space_list_quoted', qq("alpha" 'beta')],
	['05_comma_list_quoted', qq("alpha", 'beta')],
	['06_space_list_complex', qq(gamme "'"delta"'")],
	['07_comma_list_complex', qq(gamma, "'"delta"'")],
	['10_escaped_backslash', qq(\\\\)],
	['11_escaped_literal', qq(l\\\\ite\\ral)],
	['12_escaped_double_quoted', qq("l\\\\ite\\ral")],
	['13_escaped_single_quoted', qq('l\\\\ite\\ral')],

	['14_escapes_literal_numbers', qq(\\1\\2\\3\\4\\5\\6\\7\\8\\9)],
	['15_escapes_double_quoted_numbers', qq("\\1\\2\\3\\4\\5\\6\\7\\8\\9")],
	['16_escapes_single_quoted_numbers', qq('\\1\\2\\3\\4\\5\\6\\7\\8\\9')],
	['17_escapes_literal_lowercase', qq(\\b\\c\\d\\e\\f\\g\\h\\i\\j\\k\\l\\m\\n\\o\\p\\q\\r\\s\\t\\u\\v\\w\\x\\y\\z)],
	['18_escapes_double_quoted_lowercase', qq("\\b\\c\\d\\e\\f\\g\\h\\i\\j\\k\\l\\m\\n\\o\\p\\q\\r\\s\\t\\u\\v\\w\\x\\y\\z")],
	['19_escapes_single_quoted_lowercase', qq('\\b\\c\\d\\e\\f\\g\\h\\i\\j\\k\\l\\m\\n\\o\\p\\q\\r\\s\\t\\u\\v\\w\\x\\y\\z')],
	['20_escapes_literal_uppercase', qq(\\B\\C\\D\\E\\F\\G\\H\\I\\J\\K\\L\\M\\N\\O\\P\\Q\\R\\S\\T\\U\\V\\W\\X\\Y\\Z)],
	['21_escapes_double_quoted_uppercase', qq("\\B\\C\\D\\E\\F\\G\\H\\I\\J\\K\\L\\M\\N\\O\\P\\Q\\R\\S\\T\\U\\V\\W\\X\\Y\\Z")],
	['22_escapes_single_quoted_uppercase', qq('\\B\\C\\D\\E\\F\\G\\H\\I\\J\\K\\L\\M\\N\\O\\P\\Q\\R\\S\\T\\U\\V\\W\\X\\Y\\Z')],

	['23_escapes_literal_specials', qq(\\0_\\a_\\A)],
	['24_escapes_double_quoted_specials', qq("\\0_\\a_\\A")],
	['25_escapes_single_quoted_specials', qq('\\0_\\a_\\A')],

	['26_escaped_literal_quotes', qq(\\\"\\\')],
	['27_escaped_double_quotes', qq("\\\"")],
	['28_escaped_single_quotes', qq('\\\'')],

	['29_binary_operation', qq("foo#{'ba' + 'r'}baz")],
	['30_base_test', qq("foo#{'ba' + 'r'}baz")],

	['31_schema_simple', qq("["'foo'"]")],
	['32_comma_list', qq("["',foo,   '"]")],
	['33_space_list', qq("["'foo   '"]"    "bar")],
	['34_mixed_list', qq("["',foo   ,   '"]"    "bar")],

);

my @template;

push @template, "01_inline";
push @template, << "EOF";
.result {
  output: %%;
  output: #{%%};
  output: "[#{%%}]";
  output: "#{%%}";
  output: '#{%%}';
  output: "['#{%%}']";
}
EOF


push @template, "02_variable";
push @template, << "EOF";
\$input: %%;
.result {
  output: \$input;
  output: #{\$input};
  output: "[#{\$input}]";
  output: "#{\$input}";
  output: '#{\$input}';
  output: "['#{\$input}']";
}
EOF

push @template, "03_inline_double";
push @template, << "EOF";
.result {
  output: #{#{%%}};
  output: #{"[#{%%}]"};
  output: #{"#{%%}"};
  output: #{'#{%%}'};
  output: #{"['#{%%}']"};
}
EOF

push @template, "04_variable_double";
push @template, << "EOF";
\$input: %%;
.result {
  output: #{#{\$input}};
  output: #{"[#{\$input}]"};
  output: #{"#{\$input}"};
  output: #{'#{\$input}'};
  output: #{"['#{\$input}']"};
}
EOF

push @template, "05_variable_quoted_double";
push @template, << "EOF";
\$input: %%;
.result {
  dquoted: "#{#{\$input}}";
  dquoted: "#{"[#{\$input}]"}";
  dquoted: "#{"#{\$input}"}";
  dquoted: "#{'#{\$input}'}";
  dquoted: "#{"['#{\$input}']"}";
  squoted: '#{#{\$input}}';
  squoted: '#{"[#{\$input}]"}';
  squoted: '#{"#{\$input}"}';
  squoted: '#{'#{\$input}'}';
  squoted: '#{"['#{\$input}']"}';
}
EOF
# ruby sass cannot handle these cases ...
# pop(@template); pop(@template);

push @template, "06_escape_interpolation";
push @template, << "EOF";
\$input: %%;
.result {
  output: "[\\#{%%}]";
  output: "\\#{%%}";
  output: '\\#{%%}';
  output: "['\\#{%%}']";
}
EOF


sub render {
	use File::Slurp qw(write_file);
	my ($names, $template, $input) = @_;
	$template =~ s/\%\%/$input/g;
	local $CWD = $CWD;
	foreach (@{$names}) {
		mkdir $_;
		$CWD = $_;
	}
	print "created ", join("/", @{$names}), "\n";
	return write_file('input.scss', { binmode => ':raw' }, $template);

}

while (defined(my $name = shift @template)) {
	my $template = shift(@template);
	foreach my $input (@input) {
		render([$input->[0], $name], $template, $input->[1]);
	}
}

# <>;