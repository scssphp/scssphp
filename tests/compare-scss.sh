#!/bin/bash

test_dir=$(dirname $0)
diff_tool="$1"

for file in $(ls $test_dir/inputs/*.scss); do
	out_file=$(echo $file | sed -e 's/inputs/outputs/' -e 's/\.scss$/\.css/')
	sass=$(sass --stdin --no-source-map < $file 2> /dev/null)
	if [ $? = "0" ]; then
		# echo $file
		# echo "$sass"
		# echo

		if [ "$(cat $out_file)" != "$sass" ]; then
			echo "* [FAIL] $file"
			if [ -n "$diff_tool" ]; then
				$diff_tool $out_file <(echo "$sass") 2> /dev/null
			fi
		else
			echo "  [PASS] $file"
		fi
	else
		echo "         $file"
	fi
done

