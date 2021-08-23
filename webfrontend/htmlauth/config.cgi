#!/usr/bin/perl


use LoxBerry::System;
use LoxBerry::Web;

use CGI qw/:standard/;
use Config::Simple qw/-strict/;;
use warnings;
use strict;

#define vavigation
our %navbar;
$navbar{1}{Name} = "Einstellungen";
$navbar{1}{URL} = 'config.cgi';
$navbar{1}{active} = 1;

$navbar{2}{Name} = "MyUplink API";
$navbar{2}{URL} = 'index.cgi';


#Set header for our side
my $version = LoxBerry::System::pluginversion();
my $plugintitle = "MyUplink Bridge";
LoxBerry::Web::lbheader("$plugintitle V$version", "http://www.loxwiki.eu/display/LOXBERRY/Any+Plugin", "help.html");


#Load Template and fill with given parameters
my $template = HTML::Template->new(filename => "$lbptemplatedir/config.html");
my $pcfg = new Config::Simple("$lbpconfigdir/myUplink.cfg");
$template->param("myuplink_api_client_id" => $pcfg->param('Section.myuplink_api_client_id'));
$template->param("myuplink_api_client_secret" => $pcfg->param('Section.myuplink_api_client_secret'));
$template->param("redirect_url" => $pcfg->param('Section.redirect_url'));

# Write template
print $template->output();

# set footer for our side
LoxBerry::Web::lbfooter();

exit;
