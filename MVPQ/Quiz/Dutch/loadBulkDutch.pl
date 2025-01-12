#
#!/usr/bin/perl 
# Current sources:
# http://mylanguages.org/dutch_vocabulary.php
# http://www.101languages.net/dutch/dutch-word-list/
# http://www.heardutchhere.net/BasicDutchPage1.html
#
# use:  perl -CS loadbulk.pl <filename.txt>
#
# To modify for a new language:
# Create quizGreek directory
# Move loadbulk.pl, sqlite and related files to directory
# Find web sources for vocab and format English => Greek
# in htdocs
# get picture of keyboard and download 1000 px (WikiPedia)
# add englishGreek.php based on other englishX.php files
# add Greek to language bar to get keyboard
# Modify htdocs/resetSpanish.php to make htdocs/resetGreek.php
#
use DBI;
use Encode;

my $group = $ARGV[0];
my $db = DBI->connect("dbi:SQLite:../quizDutch.sqlite3", "", "", {RaiseError => 1, AutoCommit => 1});

open(INFILE, "<:encoding(unicode)", $group);
# binmode STDOUT, ">:unicode";
($group) = $group =~/(.*)\.txt/;
printf("%s\n", $group);

#insert
   my $ith = $db->prepare(<<SQL) || die "SQL error:" . DBI->errstr;
   INSERT INTO quiz ("group", xOther, english, language, xe_corrects, xe_attempts, ex_corrects, ex_attempts) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
SQL

# Try using https://regex101.com/ to test regex
while (<INFILE>){
	($english, $xOther) = /(.*)\t(.*)/; 
   printf("|%s|%s|\n", $xOther, $english); 
   $ith->execute($group, $xOther, $english, "Dutch", 0, 0, 0, 0);
}


