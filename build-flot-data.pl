#!/usr/bin/perl
# Copyright (c) 2013 Llorenç Cerdà-Alabern, http://personals.ac.upc.edu/llorenc
# build-flot-data.pl is free software: you can redistribute it and/or modify
# it under the terms of the GNU Affero Public License as published by
# the Free Software Foundation, either version 3 of the License, or
# (at your option) any later version.
# Foobar is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU Affero Public License for more details.
# You should have received a copy of the GNU Affero Public License
# along with Foobar.  If not, see <http://www.gnu.org/licenses/>.

use strict ;
use List::Util qw(min max);
use Getopt::Long; Getopt::Long::Configure ("gnu_getopt") ;
use JSON ;

#-----------------------------------------------------------------------
sub usage( ) ;
sub read_json($) ;
sub read_data($) ;

#-----------------------------------------------------------------------
my (@bidir, @unidir, @nodes) ;
my $optsgen =
  {
   'help|h' => "Aquesta ajuda.",
   'graph|g:s' => 'Graph del json <s>.',
   'var|v:s' => 'Només <var>'
} ;
my(%opts, $res) ;
eval('$res = GetOptions(\\%opts, qw(' . join(' ', keys %{$optsgen}) . '))') ;
usage() if !$res || $opts{help} || $#ARGV != 0 ;

my($command) = $0 ; $command =~ s%.*/(\w+)%$1% ;
my $pwd = `pwd` ; chomp $pwd ;
my $sdir = `dirname $0 | sed 's|^[^/]|$pwd|'` ; chomp $sdir ;

if($ARGV[0] ne "-") {
  open(OFILE, ">$ARGV[0]") || die 'No puc obrir el fitxer "' . $ARGV[0] . "\"\n" ;
  select OFILE ;
}

sub print_var($$) {
  my $name = shift ;
  my $data = shift ;
  my $i = 1 ;
  print "var $name = [" ;
  foreach my $v (@{$data}) {
    print($i > 1 ? ",[$i, $v]" : "[$i, $v]") ; ++$i ;
  }
  print "];\n"
}

sub noempty($) {
  return(defined($_[0]) && ($_[0] ne "")) ;
}

foreach my $f (`ls $sdir/jsdata/??-??-??_??-??-??-graf.js`) {
  my $pref = $f ; chomp $pref ;
  $pref =~ s/^.*(..)-(..)-(..)_(..)-(..)-(..).*$/$3-$2-$1 $4:$5:$6/ ;
  open FILE, "<$f" or die $!;
  my $found = 0 ;
 WHILE:
  while (<FILE>) {
    /^.*"bidir" : "([0-9]*)".*$/ && do { push @bidir, $1 ; next WHILE ; } ;
    /^.*"nodes" : "([0-9]*)".*$/ && do { push @nodes, $1 ; next WHILE ; } ;
    /^.*"unidir" : "([0-9]*)".*$/ && do { push @unidir, $1 ; $found = 1 ; last WHILE ; } ;
  }
  print STDERR "Data not found: $f" if ! $found ;
  close FILE ;
}

if((!$opts{var}) || ($opts{var} =~ /FlotBidir/i)) {
  print_var("FlotBidir", \@bidir) ;
}
if((!$opts{var}) || ($opts{var} =~ /FlotUnidir/i)) {
  print_var("FlotUnidir", \@unidir) ;
}
if((!$opts{var}) || ($opts{var} =~ /FlotNodes/i)) {
  print_var("FlotNodes", \@nodes) ;
}
if((!$opts{var}) || ($opts{var} =~ /FlotMax/i)) {
  print "var FlotMax = " . max(@bidir, @unidir, @nodes) . ";\n";
}
if((!$opts{var}) || ($opts{var} =~ /FlotMin/i)) {
  print "var FlotMin = " . min(@bidir, @unidir, @nodes) . ";\n";
}


if($opts{graph}) {
  my $js = read_json($opts{graph}) ;
  my ($gh, $i) ;
  for my $i (1..(@{$js}-1)) {
    my $nn = ${$js}[$i] ;
    my $id = $nn->{id} ;
    $gh->{$id} = $nn ;
  }
  ## nodes
  $i = 0 ;
  if((!$opts{var}) || ($opts{var} =~ /FlotData/i)) {
    print "var FlotData = {" ;
    for my $id (keys %{$gh}) {
      if(noempty($gh->{$id}->{x})) {
	print "," if $i++ > 0 ;
	printf "\"%s\": {label: \"%s\", data: [[%f, %f]]}",
	  $gh->{$id}->{name}, $gh->{$id}->{name}, $gh->{$id}->{x}/1000, $gh->{$id}->{y}/1000 ;
      }
    }
    print "};\n" ;
  }
  $i = 0 ;
  if((!$opts{var}) || ($opts{var} =~ /FlotGraph/i)) {
    print "var FlotGraph = [" ;
    for my $id (keys %{$gh}) {
      for my $ad (@{$gh->{$id}->{adjacencies}}) {
	my $to = $ad->{nodeTo} ;
	if(noempty($gh->{$id}->{x}) && noempty($gh->{$to}->{x})) {
	  print "," if $i++ > 0 ;
	  printf "{color: \"%s\", data:[[%f, %f], [%f, %f]]}", $ad->{data}->{'$color'},
	    $gh->{$id}->{x}/1000, $gh->{$id}->{y}/1000, $gh->{$to}->{x}/1000, $gh->{$to}->{y}/1000 ;
	}
      }
    }
    #  print "]" ;
    for my $id (keys %{$gh}) {
      if(noempty($gh->{$id}->{x})) {
	print ",{" ;
	printf "label: \"%s\", data: [[%f, %f]]}",
	  $gh->{$id}->{name}, $gh->{$id}->{x}/1000, $gh->{$id}->{y}/1000 ;
      }
    }
    print "];\n"
  }
}

#-----------------------------------------------------------------------
sub read_data($) {
  my $js = $_[0] ;
  my $ipv6 = $js->{data}->{ipv6gl}[0] ;
  $ipv6 =~ s/\/.*$// ;
  return $ipv6 ;
}

sub read_json($) {
  my $f = $_[0] ;
  ! -e $f && die "$command: Fitxer? $f\n" ;
  my $grafjs = `cat $f | sed -e '1s/var json =//'`;
  my $json = new JSON;
  return $json->allow_nonref->utf8->relaxed->decode($grafjs) ;
}

sub usage() {
  my $msg = 
"Ús: $command [opcions] <fitxer sortida (- per STDOUT)>
Opcions
" ;
  for my $opt (sort {$a cmp $b} keys %{$optsgen}) {
    $msg .= sprintf " %-10s\t%s\n", "$opt", $optsgen->{$opt} ;
  }
  print $msg ;
  exit(1) ;
}

# Local Variables:
# coding: utf-8
# mode: CPerl
# End:
