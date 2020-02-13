#!/usr/bin/perl

##
# Check the authorization to nibeuplink
# If there no authorization a notification will be created.
##

use warnings;
use strict;
use LoxBerry::Log;
use Time::HiRes qw ( sleep );
use LWP::Simple;

my $log = LoxBerry::Log->new(name => 'checkAuth daemon',);

LOGSTART("Start checkAuth DAEMON");

my $ua = LWP::UserAgent->new;
my $isFaulty = 0;
#endless loop
while(1){
	sleep(300);

	my $response = $ua->get("http://localhost/plugins/nibeuplink/?mode=raw");
	if ($response->is_success) {
	    print $response->decoded_content;
			$isFaulty = 0;
			next;
	}

	if ($isFaulty){
		next;
	}
	notify( $lbpplugindir,
					"daemon",
					"Your attantion is needed! ".
					"There are problems with the connection to Nibe Uplink! ".
					"Server message: " . $response->status_line,
					"error");

	LOGERR "There are problems with the connection to Nibe Uplink! ".
					"Server message: " . $response->status_line;

	$isFaulty = 1;
}


exit;
END
{
    if ($log) {
        $log->LOGEND;
    }
}
