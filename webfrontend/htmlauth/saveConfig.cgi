#!/usr/bin/perl -w

use strict;
use CGI::Carp qw(fatalsToBrowser);
use LoxBerry::System;
use LoxBerry::Web;
use Config::Simple qw/-strict/;

my $pcfg = new Config::Simple("$lbpconfigdir/myuplink.cfg");

read(STDIN, my $data, $ENV{'CONTENT_LENGTH'});
my @formfield = split(/&/, $data);
my ($field, $name, $value);
my %form;
foreach $field (@formfield) {
  (my $name, my $value) = split(/=/, $field);
  $value =~ tr/+/ /;
  $value =~ s/%([a-fA-F0-9][a-fA-F0-9])/pack("C", hex($1))/eg;
  $value =~ s/</&lt;/g;
  $value =~ s/>/&gt;/g;
  $form{$name} = $value;
 }

$pcfg->param('Section.myuplink_api_client_id', $form{myuplink_api_client_id});
$pcfg->param('Section.myuplink_api_client_secret', $form{myuplink_api_client_secret});
$pcfg->param('Section.redirect_url', $form{redirect_url});

$pcfg->save();

our %navbar;
$navbar{1}{Name} = "Einstellungen";
$navbar{1}{URL} = 'config.cgi';
$navbar{1}{active} = 1;
 
$navbar{2}{Name} = "myuplinkAPI";
$navbar{2}{URL} = 'index.cgi';


my $version = LoxBerry::System::pluginversion();
my $plugintitle = "myuplinkUplink Bridge";
LoxBerry::Web::lbheader("$plugintitle V$version", "http://www.loxwiki.eu/display/LOXBERRY/Any+Plugin", "help.html");

my $template = HTML::Template->new(filename => "$lbptemplatedir/saveConfig.html");
# fill in some parameters
$template->param("myuplink_api_client_id" => $form{myuplink_api_client_id});
$template->param("myuplink_api_client_secret" => $form{myuplink_api_client_secret});
$template->param("redirect_url" => $form{redirect_url});

# Nun wird das Template ausgegeben.
print $template->output();


LoxBerry::Web::lbfooter();
exit;