<?php
class MyUplinkGateway {
	var $MyUplinkAPI;
	function __construct($MyUplinkAPI)
	{
		$this->MyUplinkAPI = $MyUplinkAPI;
	}

	function main()
	{
		// handle API callback if needed
		if (isset($_GET["state"]) && $_GET["state"] == "authorization")
		{
			if (!isset($_GET["code"]))
			{
				echo "Missing code!";
				die();
			}
			$CODE = $_GET["code"];
			$token = $this->MyUplinkAPI->authorize($CODE);
			header("refresh:5;url=" . $_SERVER['PHP_SELF']);
			if ($token === false)
			{
				$this->MyUplinkAPI->clear_token();
				echo "Failed to authorize! Redirecting to <a href=\"" . $_SERVER['PHP_SELF']  . "\">status page</a> ...";
			}
			else
			{
				$this->MyUplinkAPI->save_token($token);
				echo "Successfully authorized! Redirecting to <a href=\"" . $_SERVER['PHP_SELF']  . "\">status page</a> ...";
			}
			die();
		}

		else if (isset($_GET["status"]))
		{
			echo ($this->MyUplinkAPI->checkToken() === false) ? "0" : "1";
		}
		else if (isset($_GET["mode"]))
		{
			// always check token first
			$token = $this->MyUplinkAPI->checkToken();
			if ($token === false)
			{
				header("HTTP/1.0 401 Unauthorized");
				$URL = $_SERVER['PHP_SELF'];
				echo "Not authorized yet. Please setup the required token by opening the following URL in your browser from without your LAN:<br />\n";
				echo "<a href=\"$URL\">" . (isset($_SERVER['HTTPS']) ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'] . explode("?",$_SERVER['REQUEST_URI'])[0] . "</a>";
				die();
			}

			// defaults
			$success = false;
			$response = "Invalid mode request";
			if ($_GET["mode"] == "raw")
			{
				if (isset($_GET["exec"]))
				{
					$functionURI=$_GET["exec"];
					$response = $this->MyUplinkAPI->readAPI($functionURI, $token, $success);
				}

				elseif (isset($_GET["get"]))
				{
					$functionURI=$_GET["get"];
					$response = $this->MyUplinkAPI->readAPI($functionURI, $token, $success);
				}

				elseif (isset($_GET["put"]) && isset($_GET["data"]))
				{
					$functionURI=$_GET["put"];
					$postBody=$_GET["data"]; // ex: "{\n  \"mode\": \"DEFAULT_OPERATION\"\n}"
					$response = $this->MyUplinkAPI->putAPI($functionURI, $postBody, $token, $success);
					if ($success)
					{
						header("HTTP/1.0 204 No Content");
					}
				}
				elseif (isset($_GET["post"]) && isset($_GET["data"]))
				{
					$functionURI=$_GET["post"];
					$postBody=$_GET["data"]; // ex: "{\n  \"mode\": \"DEFAULT_OPERATION\"\n}"
					$response = $this->MyUplinkAPI->postAPI($functionURI, $postBody, $token, $success);
					if ($success)
					{
						header("HTTP/1.0 204 No Content");
					}
				}
			}
			elseif ($_GET["mode"] == "set")
			{
				if (!isset($_GET["systemId"]))
				{
					header("HTTP/1.0 400 Bad Request");
					echo "Missing parameter systemId";
					die();
				}
				$systemId =  urlencode($_GET["systemId"]);

				if (isset($_GET["smartHomeMode"])) // DEFAULT_OPERATION , AWAY_FROM_HOME , VACATION
				{
					$postBody="{\n  \"mode\": \"" . urlencode($_GET["smartHomeMode"]) . "\"\n}";
					$response = $this->MyUplinkAPI->putAPI("systems/" . $systemId . "/smarthome/mode", $postBody, $token, $success);
					if ($success)
					{
						header("HTTP/1.0 204 No Content");
					}
				}

				elseif (isset($_GET["hotWaterBoost"])) // DEFAULT_OPERATION , AWAY_FROM_HOME , VACATION
				{
					$postBody="{\n  \"settings\": {\n    \"hot_water_boost\": " . urlencode($_GET["hotWaterBoost"]) . "\n  }\n}";
					$response = $this->MyUplinkAPI->putAPI("systems/" . $systemId . "/parameters", $postBody, $token, $success);
				}

				elseif (isset($_GET["thermostat"]))
				{
					if (!isset($_GET["actualTemp"]) && !isset($_GET["targetTemp"]))
					{
						header("HTTP/1.0 400 Bad Request");
						echo "Nothing to set";
						die();
					}
					$postBody="{\n";

					if (isset($_GET["externalId"])) { $postBody.="  \"externalId\": " . urlencode($_GET["externalId"]) . ",\n"; }
					else { $postBody.="  \"externalId\": 0,\n"; }

					$postBody.="  \"name\": \"" . urlencode($_GET["thermostat"]) . "\",\n";

					if (isset($_GET["actualTemp"])) { $postBody.="  \"actualTemp\": " . urlencode($_GET["actualTemp"]) . ",\n"; }
					if (isset($_GET["targetTemp"])) { $postBody.="  \"targetTemp\": " . urlencode($_GET["targetTemp"]) . ",\n"; }
					if (isset($_GET["climateSystems"])) { $postBody.="  \"climateSystems\": [\n    " . urlencode($_GET["climateSystems"]) . "\n  ]\n}"; }
					else { $postBody.="  \"climateSystems\": [\n    1\n  ]\n}"; }


					$response = $this->MyUplinkAPI->postAPI("systems/" . $systemId . "/smarthome/thermostats", $postBody, $token, $success);
					if ($success)
					{
						header("HTTP/1.0 204 No Content");
					}
				}
			}

			if (!$success)
			{
				header("HTTP/1.0 400 Bad Request");
				print_r($response);
				die(1);
			}
			$output = json_encode($response, JSON_PRETTY_PRINT);
			if (isset($_GET["format"]) && $_GET["format"] == "pretty") $output = "<pre>" . $output . "</pre>";
			echo $output;
			die();
		}
		// handle default access
		else
		{
			$this->displayStatusPage();
		}
	}
	function displayStatusPage()
	{
		$token = $this->MyUplinkAPI->checkToken();
		if ($token === false)
		{
			$URL = $this->MyUplinkAPI->authorizationURL();

			echo "You're not authorized yet.<br /><br />\n";
			echo "<b>Important:</b> If you haven't done that yet, create an application on <a href=\"https://api.myuplink.com/v2/\">https://api.myuplink.com/v2/</a> first and update the config section in the index.php (this file).<br ><br />\n";
			echo "If you think you're ready to connect this bridge to the MyUplinkAPI, click <a href=\"$URL\">here</a>.";
			die();
		}
		if (isset($_GET["autoUpdate"]) && $_GET["autoUpdate"] == "true")
		{
			header("refresh:5;url=" . $_SERVER['PHP_SELF'] . "?autoUpdate=true");
			echo "<center><a href=\"" . $_SERVER['PHP_SELF'] . "?autoUpdate=false\">Disable auto refresh</a></center><br /><br />\n";
		}
		else
		{
			echo "<center><a href=\"" . $_SERVER['PHP_SELF'] . "?autoUpdate=true\">Enable auto refresh</a></center><br /><br />\n";
		}
		echo "<h2>Status</h2>";
		echo "Current status: authorized<br /><br />\n";
		echo "Access-Token:<br />" . $token->access_token . "<br /><br />\n";
		echo "Current Time: " . time() . "<br />\n";
		echo "Last update: " . $this->MyUplinkAPI->last_token_update() . "<br />\n";
		echo "Token expire time: " . $token->expires_in . "<br />\n";
		echo "Remaining seconds: " . ($token->expires_in - (time() - $this->MyUplinkAPI->last_token_update()) . "<br /><br />\n");
		echo "<h2>Status response</h2>";
		$response = $this->MyUplinkAPI->readAPI("systems", $token, $success);
		if (!$success)
		{
			echo "FAILED:<br />\n";
			print_r($response);
		}
		else
		{
			echo "<pre>" . json_encode($response, JSON_PRETTY_PRINT) . "</pre>";
		}
		?>
		<h2>Query</h2>
		<div><form>
			<input type="hidden" name="mode" value="raw" />
			<p>
				Output format: <input type="radio" name="format" value="json" checked>json&nbsp;<input type="radio" name="format" value="pretty">pretty print
			</p>
			<p>
				Function:
				<input type="text" name="exec" value="systems">&nbsp;
				<input type="submit" value="Submit">
			</p>
		</form></div>
		<div>
		<a href="https://api.myuplinkuplink.com/docs/v1/Functions">MyUplink API Documentation</a><br />
		</div>
		<?php
	}
}
?>
