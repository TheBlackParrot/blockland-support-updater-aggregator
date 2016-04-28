<?php
	$root = dirname(__FILE__);

	$cache_fn = "$root/includes/cache.json";

	define("SECOND", 1);
	define("MINUTE", 60 * SECOND);
	define("HOUR", 60 * MINUTE);
	define("DAY", 24 * HOUR);
	define("MONTH", 30 * DAY);
	function relativeTime($time)
	{   
		$delta = time() - $time;

		if ($delta < 1 * MINUTE)
		{
			return $delta == 1 ? "one second ago" : $delta . " seconds ago";
		}
		if ($delta < 2 * MINUTE)
		{
		  return "a minute ago";
		}
		if ($delta < 45 * MINUTE)
		{
			return floor($delta / MINUTE) . " minutes ago";
		}
		if ($delta < 90 * MINUTE)
		{
		  return "an hour ago";
		}
		if ($delta < 24 * HOUR)
		{
		  return floor($delta / HOUR) . " hours ago";
		}
		if ($delta < 48 * HOUR)
		{
		  return "yesterday";
		}
		if ($delta < 30 * DAY)
		{
			return floor($delta / DAY) . " days ago";
		}
		if ($delta < 12 * MONTH)
		{
		  $months = floor($delta / DAY / 30);
		  return $months <= 1 ? "one month ago" : $months . " months ago";
		}
		else
		{
			$years = floor($delta / DAY / 365);
			return $years <= 1 ? "one year ago" : $years . " years ago";
		}
	} 

	function determineRepoColor($type) {
		switch($type) {
			case "testing":
			case "dev":
			case "development":
			case "alpha":
				return "#faa";
				break;
			
			case "beta":
			case "unstable":
				return "#ffa";
				break;

			case "release":
			case "default":
			case "stable":
			case "*":
				return "#afa";
				break;

			default:
				return "#fff";
				break;
		}
	}

	$download_img = '<img src="images/world_link.png"/>';
	$link_img = '<img src="images/link.png"/>';
	$ok_element = '<img src="images/accept.png"/> Repository responded successfully';
	$fail_element = '<img src="images/cancel.png"/> Repository did not respond';
?>
<html>

<head>
	<link rel="stylesheet" type="text/css" href="css/reset.css">
	<link rel="stylesheet" type="text/css" href="css/main.css?prevent_cloudflare=042816-1527">
</head>

<body>
	<div class="header">
		<div class="left">
			<h1>Support_Updater Addons</h1>
		</div>
		<div class="right">
			<?php if(file_exists($cache_fn)) { ?>
				<span id="lastUpdated">Last modified <?php echo date("F jS, Y,  H:i:s T", filemtime($cache_fn)); ?></span>
			<?php } else { ?>
				<span id="lastUpdated">No cache file exists!</span>
			<?php } ?>
		</div>
	</div>
	<div class="wrapper">
		<?php
			if(!is_file($cache_fn)) {
				return;
			}

			$cache = json_decode(file_get_contents($cache_fn), true);
			$names = [];
			foreach($cache as $repo => $data) {
				$ret = $data["return"];
				$names[$repo] = strtolower((array_key_exists("name", $ret) ? $ret["name"] : $repo));
			}
			array_multisort($names, $cache);

			echo '<div class="repo_list">';
				foreach($cache as $repo => $data) {
					echo '<a href="#' . md5($repo) . '">' . (array_key_exists("name", $data["return"]) ? $data["return"]["name"] : $repo) . '</a>';
				}
			echo '</div>';

			foreach($cache as $repo => $data) {
				$ret = $data["return"];

				echo '<div class="repository" id="' . md5($repo) . '">';
					$repo_name = $repo;
					if(array_key_exists("name", $ret)) {
						$repo_name = $ret["name"];
					}

					echo '<h1><a href="#' . md5($repo) . '">' . $link_img . '</a> ' . $repo_name . '</h1>';
					echo '<span class="status">' . ($data['status'] == "ok" ? $ok_element : $fail_element);
					echo '<br/>Last updated ' . relativeTime($data['last_modified']) . '</span>';

					foreach ($ret["add-ons"] as $addon_data) {
						echo '<div class="table_wrap">';
							echo '<table>';
								echo '<tr>';
									echo '<td>Name</td>';
									echo '<td><strong>' . $addon_data["name"] . '</strong></td>';
								echo '</tr>';
								echo '<tr>';
									echo '<td>Description</td>';
									echo '<td colspan="' . count($addon_data["channels"]) . '">' . (array_key_exists("description", $addon_data) ? $addon_data["description"] : "No description provided.") . '</td>';
								echo '</tr>';
								echo '<tr>';
									echo '<td>Channels</td>';
									foreach ($addon_data["channels"] as $channel) {
										echo '<td style="background-color: ' . determineRepoColor($channel["name"]) . '">';
											$file_url = str_replace("http://", "", $channel["file"]);
											$file_url = str_replace("https://", "", $file_url);

											echo '<a href="http://' . $file_url . '">' . $download_img . '</a>';
											echo ' <strong>' . $channel["name"] . '</strong><br/>v' . $channel["version"];
										echo '</td>';
									}
								echo '</tr>';
							echo '</table>';
						echo '</div>';
					}

					echo '<a class="source" href="http://' . $repo . '">' . $repo . '</a>';
				echo '</div>';
			}
		?>
	</div>
</body>

</html>