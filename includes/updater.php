<?php
	if(php_sapi_name() != "cli") {
		die("Must be run within the command line.");
	}

	function parseImportantTML($str) {
		$data = [];

		$all = split("\n", $str);
		foreach ($all as $line) {
			$line = trim($line);

			if(strstr($line, "<repository:")) {
				$name = str_replace("<repository:", "", $line);
				$name = str_replace(">", "", $name);

				$data["name"] = $name;

				continue;
			}

			if(strstr($line, "<addon:")) {
				if(!array_key_exists("add-ons", $data)) {
					$data["add-ons"] = [];
				}

				$addon = str_replace("<addon:", "", $line);
				$addon = str_replace(">", "", $addon);

				$data["add-ons"][] = [
					"name" => $addon
				];

				continue;
			}

			if(strstr($line, "<channel:")) {
				if(!array_key_exists("channels", $data["add-ons"][count($data["add-ons"])-1])) {
					$data["add-ons"][count($data["add-ons"])-1]["channels"] = [];
				}

				$channel = str_replace("<channel:", "", $line);
				$channel = str_replace(">", "", $channel);

				$data["add-ons"][count($data["add-ons"])-1]["channels"][] = [
					"name" => $channel
				];

				continue;
			}

			// please correctly parse TML
			// don't do what i did
			// make something else

			if(strstr($line, "<version:")) {
				$version = str_replace("<version:", "", $line);
				$version = str_replace(">", "", $version);

				// i am crying
				$data["add-ons"][count($data["add-ons"])-1]["channels"][count($data["add-ons"][count($data["add-ons"])-1]["channels"])-1]["version"] = $version;

				continue;
			}

			if(strstr($line, "<file:")) {
				$file = str_replace("<file:", "", $line);
				$file = str_replace(">", "", $file);

				$data["add-ons"][count($data["add-ons"])-1]["channels"][count($data["add-ons"][count($data["add-ons"])-1]["channels"])-1]["file"] = $file;

				continue;
			}

			if(strstr($line, "<desc:")) {
				$desc = str_replace("<desc:", "", $line);
				$desc = str_replace(">", "", $desc);

				$data["add-ons"][count($data["add-ons"])-1]["description"] = $desc;

				continue;
			}
		}

		return $data;
	}

	ini_set('default_socket_timeout', 5);

	$root = dirname(__FILE__);
	$cache_fn = "$root/cache.json";
	$repo_fn = "$root/repos.txt";

	if(!is_file($repo_fn)) {
		die("No repos available.\n");
	}

	$main_list = file($repo_fn);
	if(is_file($cache_fn)) {
		$cache = json_decode(file_get_contents($cache_fn), true);
	} else {
		$cache = [];
		file_put_contents($cache_fn, "");
	}

	foreach($main_list as $repo) {
		$repo = str_replace("http://", "", trim($repo));
		$repo = str_replace("https://", "", $repo);

		$status = 0;
		
		if($content = file_get_contents("http://$repo")) {
			$content = trim($content);
			$status = 1;
		}

		if(array_key_exists($repo, $cache)) {
			$cache[$repo]["status"] = ($status ? "ok" : "fail");

			if($status) {
				$new_hash = md5($content);

				if($new_hash != $cache[$repo]["hash"]) {
					$cache[$repo]["hash"] = $new_hash;
					
					$cache[$repo]["last_modified"] = time();

					if(trim($content)[0] == "<") {
						$decoded_data = parseImportantTML($content);
					} else {
						$decoded_data = json_decode($content, true);
					}
					$decoded_data = json_decode($content, true);
					$cache[$repo]["return"] = $decoded_data;
				}
			}
		} else {
			$cache[$repo] = [
				"status" => ($status ? "ok" : "fail"),
				"repo" => $repo,
				"last_modified" => time()
			];

			if($status) {
				$cache[$repo]["hash"] = md5($content);

				if($content[0] == "<") {
					$decoded_data = parseImportantTML($content);
				} else {
					$decoded_data = json_decode($content, true);
				}
				$cache[$repo]["return"] = $decoded_data;
			}
		}

		$in_use[$repo] = 1;
	}

	foreach ($cache as $repo => $data) {
		if(!array_key_exists($repo, $in_use)) {
			unset($cache[$repo]);
		}
	}

	file_put_contents($cache_fn, json_encode($cache));
?>