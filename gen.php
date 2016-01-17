<?php
	/*
		cd c:/godot
		/usr/bin/find . -name '*.c' > GodotToCmakeForVisualStudio/all_c_files.txt
		/usr/bin/find . -name '*.cpp' > GodotToCmakeForVisualStudio/all_cpp_files.txt
		/usr/bin/find . -name '*.h' > GodotToCmakeForVisualStudio/all_h_files.txt
		/usr/bin/find . -name '*.hpp' > GodotToCmakeForVisualStudio/all_hpp_files.txt
		cd GodotToCmakeForVisualStudio
		php gen.php > ../CMakeLists.txt
		mkdir visualstudio
		cd visualstudio
		cmake ../..
	*/

	function get_dirs_from_file($filename) {
		$files = file($filename); // save each line of $filename in array
		$alldirs = array();
		foreach ($files as $file) {
			$file = trim($file);
			$file = substr($file, 2); // remove ./ from ./cors/os e.g.
			$path = dirname($file);
			$alldirs[] = $path;
			//echo "$path\n";
		}
		$dirs = array_unique($alldirs);
		asort($dirs, SORT_REGULAR);
		return $dirs;
	}
	
	function include_headers($filename) {
		$headers = get_dirs_from_file($filename);
		foreach ($headers as $header)
			echo "include_directories($header)\n";
	}

	// this function is using an $id, because the $varname is generated based on path, which conflicts with .c and .cpp in same folder
	// we need a varname, so we can later reference the globed files to add_executable(...)
	function include_source($filename, $id) {
		$dirs = get_dirs_from_file($filename);
		$cmakevarnames = array();
		foreach ($dirs as $dir) {
			$varname = $id . "_" . str_replace("/", "_", $dir);
			echo "file(GLOB $varname $dir/*.cpp)\n";
			$groupname = str_replace("/", "\\\\", $dir);
			$cmakevarname = "$" . "{" . $varname . "}";
			echo "SOURCE_GROUP(\"$groupname\" FILES $cmakevarname)\n";
			$cmakevarnames[] = $cmakevarname;
		}
		$vars = implode(" ", $cmakevarnames);
		return $vars;
	}
	
	// we basically just generate a minimum CMakeLists.txt file which references every .cpp/.c and add every folder which contains a .h/.hpp file
	// cmake.exe then can turn CMakeLists.txt into a Visual Studio solution or any other IDE, like CodeBlocks, CodeLite, Eclipse, Kate etc.
	function main() {
		echo "cmake_minimum_required(VERSION 2.8)\n";
		echo "project (GodotEngine)\n";
		echo "include_directories(.)\n";
		include_headers("all_h_files.txt");
		include_headers("all_hpp_files.txt");
		$vars_c = include_source("all_c_files.txt", "id_c");
		$vars_cpp = include_source("all_cpp_files.txt", "id_cpp");
		echo "add_executable(godot $vars_c $vars_cpp)\n";
		echo "target_compile_definitions(godot PRIVATE TOOLS_ENABLED)\n";
		echo "MESSAGE(\"Have fun with Godot Engine in Visual Studio! :^)\")\n";
	}
	
	// just comment the spammy main out if you wanna play around with the code, to fit it to your needs
	main();