#!/bin/bash

test_dir=$(dirname $0)
diff_tool="$1"
sass_executable=${SASS_EXECUTABLE:-sass}
glob=${INPUT_GLOB:-*}

for file in $(ls $test_dir/inputs/$glob.scss); do
	out_file=$(echo $file | sed -e 's/inputs/outputs/' -e 's/\.scss$/\.css/')
	sass=$($sass_executable --style=expanded $file 2> /dev/null)
	if [ $? = "0" ]; then
	  # Perform the same normalization than SassSpecTest regarding formatting
		normalized_sass=$(echo "$sass" | sed -e ':a' -e 'N' -e '$!ba' -e 's/}\n\n/}\n/g' -e 's/,\n/, /g')
		# echo $file
		# echo "$sass"
		# echo

		if [ "$(cat $out_file)" != "$sass" ] && [ "$(cat $out_file)" != "$normalized_sass" ]; then
			echo "* [FAIL]    $file"
			if [ -n "$diff_tool" ]; then
				$diff_tool $out_file <(echo "$normalized_sass") 2> /dev/null
			fi
		else
			echo "  [PASS]    $file"
		fi
	else
		echo "x [INVALID] $file"
	fi
done

