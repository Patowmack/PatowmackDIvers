#
# word list source:  http://www.smartphrase.com/Italian/it_general_words_voc.shtmll
#http://www.learnalanguage.com/learn-italian/italian-words/
#
# alternatives:
# http://www.studyspanish.com/vocab/bath.htm
# http://www.spanishclassonline.com/vocabulary/animalsdomestic.htm
# http://www.lingolex.com/spanish.htm
# http://spanish.about.com/od/spanishvocabulary/a/vocaboverview.htm
# http://www.espanyol.hu/en/grammar/gender.php ===BEST
# http://italian.about.com/library/fare/blfare206.htm
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
# Modify htdocs/resetItalian.php to make htdocs/resetGreek.php
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
	@lines0 = split(/ => /, $_);  # break on word breaks
	if ($#ARGV == 1) {
	   printf("%s|%s\n", $lines0[1], $lines0[0]);
           $ith->execute($group, $lines0[1], $lines0[0]);
   }else {
	   printf("%s|%s\n", $lines0[0], $lines0[1]);
           $ith->execute($group, $lines0[0], $lines0[1]);
   }
}


