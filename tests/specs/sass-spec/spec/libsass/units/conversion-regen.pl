use strict;
use warnings;

use File::chdir;

my @input = (["conversion"]);

my @names = ("size", "angle", "time", "frequency", "resolution");

my %units = (
	"size"        => ["px", "pt", "pc", "mm", "cm", "in"],
	"angle"       => ["deg", "grad", "rad", "turn"],
	"time"        => ["s", "ms"],
	"frequency"   => ["Hz", "kHz"],
	"resolution"  => ["dpi", "dpcm", "dppx"],
);

my @template;

foreach my $name (@names)
{
	my @units = @{$units{$name}};
	my $tmpl = ".result {\n";
	for (my $i = 0; $i < scalar(@units); $i++) {
		for (my $n = 0; $n < scalar(@units); $n++) {
			$tmpl .= sprintf('  output: (0%s + 1%s)', $units[$i], $units[$n]) . ";\n";
			$tmpl .= sprintf('  output: (4.2%s / 1%s)', $units[$i], $units[$n]) . ";\n";
			$tmpl .= sprintf('  output: (4.2%s * 1%s / 1%s)', $units[$i], $units[$n], $units[$i]) . ";\n";
		}
	}
	$tmpl .= "}\n";
	push @template, $name;
	push @template, $tmpl;
}

sub render {
	use File::Slurp qw(write_file);
	my ($names, $template, @fields) = @_;
	$template =~ s/\%(\d+)\%/$fields[$1]/g;
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
		render([$input->[0], $name], $template, @{$input});
	}
}

# <>;