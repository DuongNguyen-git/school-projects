<?php

	function instruction() {
		fwrite (STDOUT,"
		Script searchs for all c header files in given direction and it analyses and prints all functions of these files in xml format.
		Script can work with these parameters:
		--input=fileordir    This parameter contains directory, which will be searched and also his subdirectories. If not defined, the 
							 current directory and subdirectory will be searched.
		--output=filename    It writes file analysis to the file filename in xml format. If not defined, the xml will be written to standard output.
							 If filename contains whitespaces, it must be written inside the quotes marks.
		--pretty-xml=k       It makes new line for each element in xml. The child will be intended by k from the parent. If k is not defined, k will be 4. 
							 If the argument is not defined, the xml format will be in 1 line.
		--no-inline          Script will skip functions defined as inline.
		--max-par=n          Script will only analyses functions with less or equal parameters than n. N must be defined if this argument is defined.
		--no-duplicates      Functions, which are defined more than 1 time, will be written only once. 
		--remove-whitespace  Script deletes unnecessary white space in rettype or type element.
		Each of these parameters can only be written one time.\n");
	
	}

	function input(&$headers, $dir) {
		$fulldir = realpath($dir);
		$direction = new RecursiveDirectoryIterator($fulldir);
		$iteration = new RecursiveIteratorIterator($direction);
		$objects = new RegexIterator($iteration, '/^.+\.h$/i', RecursiveRegexIterator::GET_MATCH);
		foreach ($objects as $filename => $object){
			$headers[] = $filename;
		}
	}

	$longopts = array("help", "input:", "output:", "pretty-xml::", "no-inline", "max-par:", "no-duplicates", "remove-whitespace");
	$options = getopt(NULL, $longopts);

	if ($argc > 9) {
		fwrite(STDERR, "Error! Too much parameters. Some of them are duplicated or not existed.\n");
		exit (1);
	}

	$isinput = 0;
	$noinline = 0;
	$noduplicates = 0;
	$maxparam = 0;
	$removewhitespace = 0;
	$prettyxml = 0;
	$path = "./";
	$isfile = 0;
	
	if ((count($options)) != ($argc-1)) { // if parameter is invalid
		echo "Parameter is invalid\n";
		exit (1);
	}

	foreach (array_keys($options) as $value) {

		switch ($value) {
			case "help":
				if (("--$value" != $argv[1]) || ($argc > 2)) {
					fwrite(STDERR, "Error, $value must be the first argument and it must be alone.\n");
					exit (1);
				}
				instruction();
				return 0;
				break;

			case "input":
				$isinput = 1;
				if (is_dir($options[$value])){
					input($header, $options[$value]);
					if ((preg_match("/\/$/", $options[$value])) == 0)
						$path = $options[$value] . "/";
					else
						$path = $options[$value];
				}
				else{
					if ((preg_match("/^.+\.h$/", $options[$value])) == 0){
						fwrite(STDERR, "Error! Input filename doesnt have type .h or its not dir\n");
						exit (2);
					}
					$isfile = 1;
					$header[0] = $options[$value];
					$path = "";
				}
				break; 

			case "output":
				$outputfile = $options[$value];
				
				break;
			case "pretty-xml":
				$prettyxml = 1;
				if ($options[$value] != NULL){
					if ((is_numeric($options[$value])) === TRUE)
						$k = $options[$value];
					else{
						fprintf(STDERR, "Error! Parameter --pretty-xml=k must contain a number if k is defined.\n");
						exit (1);
					}
				}
				else{
					$k = 4;
				}
				break;

			case "no-inline":
				$noinline = 1;
				break;

			case "max-par":
				$n = $options[$value]; 
				$maxparam = 1;
				if ((is_numeric($n)) === FALSE){
					fwrite(STDERR, "Error! Parameter --max-par=n must contain a number.\n");
					exit (1);
				}
				break;

			case "no-duplicates":
				$noduplicates = 1;
				break;

			case "remove-whitespace":
				$removewhitespace = 1;
				break;

			default: 
				fwrite(STDERR,"Error! Invalid argument\n");
				exit (1);
		}
	}

	if ($isinput === 0) {
		input($header, "./");
	}
    
	$xml = new DOMDocument('1.0', 'UTF-8');

	$root = $xml->createElement('functions');
	$root_attribute = $xml->createAttribute('dir');
	$root_attribute->value = $path;

	if (isset($header)) {

		foreach ($header as $fileh) {

			if ($isfile == 1){
				if (!(is_file($fileh))) {
					echo"Error! The file does not exist.\n";
					exit (2);
				}
				else{
					$finput = fopen($fileh, "r");
					if ($finput == FALSE){
						echo"Error! Cannot open the file.\n";
						exit (2);
					}
					fclose($finput);
				}
			}

			$hfile = file_get_contents($fileh);		
			$hfile = preg_replace("/\".*?\"/", "", $hfile);	 
 			$hfile = preg_replace("/#.*?\n/", "", $hfile); 
			$hfile = preg_replace("/\/\*.*?\*\//s", "", $hfile); 
			$hfile = preg_replace("/\/\/.*?\n/", "", $hfile); 
			$equal = preg_match_all("/[a-zA-Z_][[:alpha:][:space:]]*?[[:graph:]]+?[[:space:]]+?[[:graph:]]+?[[:space:]]*?\([[:graph:][:space:]]*?\)[[:space:]]*?[;{]/", $hfile, $fun_array);
			
				if ($isfile == 1){
					$file_name = $fileh;
				}
				else{
					$realpath = realpath($path);
					$file_name = str_replace("$realpath/", "", $fileh);
				}

				$in = 0;
				$dup = array();

				foreach ($fun_array[0] as $func) {

					preg_match("/([a-zA-Z_][[:alpha:][:space:]]*?[[:graph:]]+?)[[:space:]]*?([[:alnum:]_]+?)[[:space:]]*?\(/", $func, $function_data);
					
					$function_data[1] = preg_replace("/[\t\f\n\r\v]/", " ", $function_data[1]); // replace whitespaces

					if ($noinline === 1){
						if ((preg_match("/inline/", $function_data[1])) === 1)
							continue;	
					}
					
					if ($noduplicates === 1){
						if ((in_array($function_data[2], $dup)) === TRUE)
							continue;
					}

					$dup[$in] = $function_data[2];
					$in++;

					$function = $xml->createElement('function');

					$function_attribute = $xml->createAttribute('file');
					$function_attribute->value = $file_name;
					$function->appendChild($function_attribute);

					$function_attribute = $xml->createAttribute('name');
					$function_attribute->value = $function_data[2];
					$function->appendChild($function_attribute);
				

					$parameters = array();

					if (preg_match("/\([[:space:]]*void[[:space:]]*+\)/", $func, $parameters) == 0)		 
						preg_match_all("/[[:alnum:][:space:]*_\[\]]{1,}[),]/", $func, $parameters);
					

					$params = array();


					if ($maxparam === 1){
						$param_count = count($parameters[0]);
						if ($param_count > $n)
							continue;
					}
					
					if (is_array($parameters[0])){
						foreach ($parameters[0] as $param) {

							if ((preg_match("/^\ {1,}/", $param)) == 1)  // remove whitespace in the beginning
								$param = preg_replace("/^\ {1,}/", "", $param);
							if ((preg_match("/[\ \)]{1,}$/", $param)) == 1) // remove whitespaces and ) in the end 
								$param = preg_replace("/[\ \)]{1,}$/", "", $param);
							if ((preg_match("/\,$/", $param)) == 1)
								$param = preg_replace("/\,$/", "", $param);
							$params[] = $param;
						}
					}

					$i = 1;

					foreach ($params as $param_value){
						
						preg_match("/\S*$/", $param_value, $param_name); // take last string
					
						$param_type = preg_replace("/\w{1,}$/", "", $param_value); // replace it with nothing -- delete it 
					
						$param_type = preg_replace("/\s*$/", "", $param_type); // remove whitespace in the end
						
						$param_elem = $xml->createElement('param');

						$param_attribute = $xml->createAttribute('number');
						$param_attribute->value = $i;
						$param_elem->appendChild($param_attribute);
						
						$param_type = preg_replace("/\s/", " ", $param_type); // replace other whitespace with space

						if ($removewhitespace === 1){
							$param_type = preg_replace("/\ {1,}\*/", "*", $param_type);
							$param_type = preg_replace("/\ {1,}/", " ", $param_type);
							$param_type = preg_replace("/\*\ {1,}/", "*", $param_type); // remove whitespace after *
							$function_data[1] = preg_replace("/\ {1,}\*/", "*", $function_data[1]);
							$function_data[1] = preg_replace("/\ {1,}/", " ", $function_data[1]);
						}

						$param_type = preg_replace("/^\ {1,}/", "", $param_type);

						$param_attribute = $xml->createAttribute('type');
						$param_attribute->value = $param_type;
						$param_elem->appendChild($param_attribute);

						$function->appendChild($param_elem);
						$i++;
						
					}
					
					if (preg_match("/[[:space:]]*\.\.\.\)/", $func) === 1)  
						$varargs = "yes";
					else
						$varargs = "no";


					$function_attribute = $xml->createAttribute('varargs');
					$function_attribute->value = $varargs;
					$function->appendChild($function_attribute);

					$function_attribute = $xml->createAttribute('rettype');
					$function_attribute->value = $function_data[1];
					$function->appendChild($function_attribute);

					$root->appendChild($function);
				}	
			
		}
	}

	$root->appendChild($root_attribute);
	$xml->appendChild($root);
	
	$xmlfile = $xml->saveXML();
	
	$xmlfile = preg_replace("/\n/", "", $xmlfile);

	if ($prettyxml === 1) {
		$xmlfile = preg_replace("/>/", ">\n", $xmlfile);

		for ($j = 0; $j < $k; $j++) {
			$xmlfile = preg_replace("/<function /"," <function ", $xmlfile);
			$xmlfile = preg_replace("/<\/function>/"," </function>", $xmlfile);
		}

		for ($j = 0; $j < ($k*2); $j++) {
			$xmlfile = preg_replace("/<param/", " <param", $xmlfile);
		}
	}

	if (isset($outputfile)){
		
		if (is_dir($outputfile) === TRUE){
			fwrite(STDERR, "Error! Output filename must be file.\n");
			exit (3);
		}
	
		$fo = fopen($outputfile,"w");
		if ($fo === FALSE){
			fwrite(STDERR, "Error! Cannot open the output file.\n");
			exit (3);
		}

		fwrite($fo, $xmlfile);
		
		if (fclose($fo) === FALSE){
			fwrite(STDERR, "Error! Cannot close the output file.\n");
			exit (3);
		}
	}
	else{
		echo $xmlfile;
		exit (0);
	}
?>
