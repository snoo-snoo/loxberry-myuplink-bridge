<?php
require_once "loxberry_web.php";
require_once "Config/Lite.php";
require_once "loxberry_log.php";

require_once('class.myUplinkAPI.php');
require_once('class.myUplinkGateway.php');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// CONFIG (set up your own application on https://api.myUplinkuplink.com to get these things)
//==========
$cfg = new Config_Lite("$lbpconfigdir/MyUplink.cfg");

$CLIENT_ID = $cfg['Section']['myuplink_api_client_id']; // MyUplinkUplink API Application Identifier
$CLIENT_SECRET = $cfg['Section']['myuplink_api_client_secret']; // MyUplinkUplink API Application Secret
$REDIRECT_URL = $cfg['Section']['redirect_url']; // the URL on your raspberryPi to the folder containing this script (this can and should only be accessible from your LAN for security reasons!)

$myUplinkAPI = new MyUplinkAPI($CLIENT_ID, $CLIENT_SECRET, $REDIRECT_URL);
$myUplinkGateway = new MyUplinkGateway($myUplinkAPI);


if ($myUplinkAPI->debugActive)
{
	file_put_contents('/tmp/myUplink.log', '['.date("c").'] '.$_SERVER['REQUEST_URI']."\n", FILE_APPEND);
}

if (empty($_GET)) {
    // The Navigation Bar
	$navbar[1]['Name'] = "Einstellungen";
	$navbar[1]['URL'] = 'config.cgi';
	$navbar[2]['Name'] = "MyUplinkAPI";
	$navbar[2]['URL'] = 'index.php';
	$navbar[2]['active'] = True;

	// This will read your language files to the array $L
	$L = LBSystem::readlanguage("language.txt");
	$template_title = "MyUplinkUplink Bridge";
	$helplink = "http://www.loxwiki.eu:80/x/2wzL";
	$helptemplate = "help.html";
	LBWeb::lbheader($template_title, $helplink, $helptemplate);
	$notify = LBLog::get_notifications_html( LBPPLUGINDIR, null, "error");
	echo $notify;
}

$myUplinkGateway->main();

if (empty($_GET)) {
	LBWeb::lbfooter();
}

?>
