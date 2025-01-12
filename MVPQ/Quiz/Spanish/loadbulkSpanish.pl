#
# Current favorite source of words http://www.vocab.co.uk/vocabulary.htm
# word list source:  http://www.smartphrase.com/Italian/spanish.shtml
#http://www.languagelearninglibrary.org/spanish/nouns_work.htm
#
# alternatives:
# http://www.studyspanish.com/vocab/bath.htm
# http://www.spanishclassonline.com/vocabulary/animalsdomestic.htm
# http://www.lingolex.com/spanish.htm
# http://spanish.about.com/od/spanishvocabulary/a/vocaboverview.htm
# http://www.espanyol.hu/en/grammar/gender.php ===BEST
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
my $db = DBI->connect("dbi:SQLite:quiz.sqlite3", "", "", {RaiseError => 1, AutoCommit => 1});

open(INFILE, "<:encoding(unicode)", $group);
# binmode STDOUT, ">:unicode";
($group) = $group =~/(.*)\.txt/;

#insert
   my $ith = $db->prepare(<<SQL) || die "SQL error:" . DBI->errstr;
   INSERT INTO quiz ("group", xOther, english) VALUES (?, ?, ?)
SQL

while (<INFILE>){
	if ($#ARGV == 1) {
	   if (/\|/) { ($english, $xOther) = /\s*(.*)\|(.*)\s*/; } else { ($english, $xother) = /\s*(.*)(( => )|(\|))(.*)\s*/; }
                } else { if (/\|/) {($xOther, $english) = /\s*(.*)\|(.*)\s*/; } else {($xOther, $english) = /\s*(.*)(( => )|(\|))(.*)\s*/; }}
  printf("|%s|%s|\n", $xOther, $english); 
   $ith->execute($group, $xOther, $english);
}


