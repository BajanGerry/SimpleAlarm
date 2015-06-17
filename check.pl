#!/usr/bin/perl

use strict;
use Net::SNMP;
use warnings;
use Email::Send;
use Email::Simple::Creator;
use Config::Simple;
use DBD::mysql;

my %alarmStates = (
        1       => 'OK',
        2       => 'MINOR',
        3       => 'MAJOR',
        4       => 'CRITICAL'
);

my %ERRORS = (
        'OK'            => 0,
        'WARNING'       => 1,
        'CRITICAL'      => 2,
        'UNKNOWN'       => 3,
        'DEPENDENT'     => 4
);

sub DBconnect {
my ($level, $state, $r_sysName, $host, $errorMessage, $EMAILADDY, $COMPANY) = @_;
# Compare the current PBX state with previous state and if different then update database and state.txt file with new state level

my $file = "$host.txt";
if (-f $file) {
}
else {
	open FH, ">>", "$file";
}
open FH,"$file";
my $line  = <FH>;
chomp $line;
my $last_line;
while (<FH>) {$last_line = $_;}
chomp $last_line;
close $file;

if ($errorMessage ne $line) { 

if ($r_sysName eq "") {$r_sysName = $last_line;}
# Open database to check and add new entry with alarm details if state changed
# MySQL Connection parameters
my $dbuser= "monitor";
my $dbpassword= "password";
my $dbhost= "localhost";
my $currentDate ='';
my $currentAlarm = '';

# Establish the connection which returns a DB handle
my $dbh= DBI->connect("dbi:mysql:database=monitoring;host=$dbhost",$dbuser,$dbpassword) or die $DBI::errstr;

# Get Current date from table
my $sql = "SELECT date, alarm FROM mitel_alarms WHERE name = '$r_sysName' ORDER  BY date DESC LIMIT  1";
my $script = $dbh->prepare($sql);
$script->execute or die "SQL Error: $DBI::errstr\n";
	while (my @row = $script->fetchrow_array) {
	$currentDate = $row[0];
	$currentAlarm = $row[1];
	} 
 
my $sth= $dbh->prepare("INSERT INTO mitel_alarms (name, ip, state, alarm, date, last, company) VALUES('$r_sysName', '$host', '$state', '$errorMessage', NOW(), '$currentDate', '$COMPANY' )") or die $DBI::errstr;
# Send the statement to the server
$sth->execute();
# Close the database connection
$dbh->disconnect or die $DBI::errstr;

#Open host.txt file and update to new state
	open (my $fh,'>', $file);
	print $fh "$errorMessage\n$r_sysName";

	close $file;
	
# Send email alert with new state
&send_email ($level, $state, $r_sysName, $host, $errorMessage, $EMAILADDY);
} # Close if 

} # Close DBConnect subroutine

# Email alarms subroutine (need to change the email address used for this!)
sub send_email {
  my ($level, $state, $r_sysName, $host, $errorMessage, $EMAILADDY) = @_;
  my $emailcfg = new Config::Simple();
  $emailcfg->read('config.ini');
  my $FROM = $emailcfg->param("fromemail");
  my $EMAILHOST = $emailcfg->param("emailhost");
  my $SMTPADDY = $emailcfg->param("smtpaddress");
  my $SMTPPASS = $emailcfg->param("smtppass");
  my $messageSubject = "The state has change to $state with $errorMessage alarm on $r_sysName";
  my $messageBody = "Dear Support, \n\n There is an alarm of $errorMessage on the PBX named $r_sysName at IP address $host. \n\n Please investigate ASAP!";

  my $email = Email::Simple->create(
      header => [
          From    => $FROM,
          To      => $EMAILADDY,
          Subject => $messageSubject,
      ],
      body => $messageBody,
  );

  my $sender = Email::Send->new(
      {   mailer      => 'SMTP',
          mailer_args => [
			  Host 	   => $EMAILHOST,
              username => $SMTPADDY,
              password => $SMTPPASS,
          ]
      }
  );
  eval { $sender->send($email) };
  die "Error sending email: $@" if $@;

} #End of emailing subroutine

# Start of processing
# Reading configuration parameters from the config.ini file
my $cfg = new Config::Simple();
$cfg->read('config.ini');

my $HOST = $cfg->param("pbx");
my $COMPANY = $cfg->param("company");
my $EMAILADDY = $cfg->param("alertemail");
my $COMMUNITY = $cfg->param("community");
my $PORT = $cfg->param("port");

print "Connecting to the $COMPANY PBX $HOST with SNMP community $COMMUNITY on port $PORT\n";
my ($session, $error) = Net::SNMP->session(
	-hostname	=> $HOST,
	-community	=> $COMMUNITY,
	-port		=> $PORT

);

if (!defined($session)) {
	printf("ERROR: %s\n", $error);
	exit $ERRORS{'UNKNOWN'};
}

	my $sysName  = '.1.3.6.1.2.1.1.5.0';
	my $resultName = $session->get_request(
	-varbindlist => [
		"$sysName",
	],
	);
	my $r_sysName = $resultName->{"$sysName"};

	my $mitelAlarmState = '1.3.6.1.4.1.1027.4.1.1.2.2.1.0';
	my $result = $session->get_request(
		-varbindlist	=> [$mitelAlarmState]
);


if (!defined($result)) {
	printf("ERROR: %s\n", $session->error);
	&DBconnect ("CRITICAL", "CRITICAL", $r_sysName, $session->hostname, $session->error, $EMAILADDY, $COMPANY);
	$session->close;
	exit $ERRORS{'UNKNOWN'};	
}

my $alarmState = $result->{$mitelAlarmState};

if ($alarmState > 1) {
	my $mitelAlarmCategories = '1.3.6.1.4.1.1027.4.1.1.2.2.4.1.8';
	
	$result = $session->get_table($mitelAlarmCategories);
	my %results = %{$result};
	$error = $session->error;

	if ($error) {
		printf("ERROR: %s\n", $error);
		$session->close;
		exit $ERRORS{'UNKNOWN'};
	}
	
	my $errorString = '';
	while (my($key, $value) = each(%results)) {
		$errorString = $errorString . $value .',';
	}
	$errorString = substr($errorString, 0, length($errorString)-1);
	
	if ($alarmState == 2) {
		printf("WARNING - Minor Alarm on %s: %s\n", $r_sysName, $errorString);
		&DBconnect ($alarmState, "MINOR", $r_sysName, $session->hostname, $errorString, $EMAILADDY, $COMPANY);
		$session->close;
		exit $ERRORS{'WARNING'};
	} elsif ($alarmState == 3) {
		printf("CRITICAL - Major Alarm on %s: %s\n", $r_sysName, $errorString);
		&DBconnect ($alarmState, "MAJOR", $r_sysName, $session->hostname, $errorString, $EMAILADDY, $COMPANY);
		$session->close;
		exit $ERRORS{'CRITICAL'};
	} elsif ($alarmState == 4) {
		printf("CRITICAL - Critical Alarm on %s: %s\n", $r_sysName, $errorString);
		&DBconnect ($alarmState, "CRITICAL", $r_sysName, $session->hostname, $errorString, $EMAILADDY, $COMPANY);
		$session->close;
		exit $ERRORS{'CRITICAL'};
	} else {
		printf("UNKNOWN STATE\n");
		&DBconnect ($alarmState, "UKNOWN STATE", $r_sysName, $session->hostname, $errorString, $EMAILADDY, $COMPANY);
		$session->close;
		exit $ERRORS{'UNKNOWN'};
	}
} else {
	printf("State OK - No Alarms on %s\n", $r_sysName);
	&DBconnect ($alarmState, "OK", $r_sysName, $session->hostname, "No Alarms", $EMAILADDY, $COMPANY);
	$session->close;
	exit $ERRORS{'OK'};
}
$session->close;
exit $ERRORS{'UNKNOWN'};

