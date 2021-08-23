#!/usr/bin/perl

##
# Check the authorization to myuplink all 15 minutes
# If there no authorization a notification will be created.
##

use warnings;
use strict;
use LoxBerry::Log;
use Time::HiRes qw ( sleep );
use LWP::Simple;

my $log = LoxBerry::Log->new(name => 'checkAuth daemon',);

LOGSTART("Communication Status Control MyUplink");

my $ua = LWP::UserAgent->new;
my $isFaulty = 0;
#endless loop
while(1){
	sleep(900);

# Send a request to the index page from plugin. This page send a status request
# to myUplink uplink.
	my $response = $ua->get("http://localhost/plugins/myuplink/?mode=raw");
# if response comes back with status code 200, we can go to sleep
	if ($response->is_success) {
			$isFaulty = 0;
			next;
	}

# if the response is wrong but we notify before, we can go to sleep and wait.
	if ($isFaulty){
		next;
	}
	notify( $lbpplugindir,
					"daemon",
					"Your attantion is needed! ".
					"There are problems with the connection to MyUplinkUplink! ".
					"Server message: " . $response->status_line,
					"error");

	LOGERR "There are problems with the connection to MyUplinkUplink! ".
					"Server message: " . $response->status_line;

# after we create a notify we set the fault-flag. So we know that a notification was
# created.
	$isFaulty = 1;
}


exit;
END
{
    if ($log) {
        $log->LOGEND;
    }
}
