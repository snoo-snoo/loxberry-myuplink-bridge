#!/usr/bin/perl

use CGI qw/:standard/;
use LoxBerry::System;
use LoxBerry::Web;


my $lbhostname = lbhostname();
my $plugin = LoxBerry::System::plugindata();
my $pluginname = lc($plugin->{PLUGINDB_NAME});
print redirect(-url=>"http://$lbhostname/admin/plugins/$pluginname/config.cgi");

