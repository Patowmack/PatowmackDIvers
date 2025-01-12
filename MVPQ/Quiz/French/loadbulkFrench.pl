#
# word list source:  
# Current favorite source of words http://www.vocab.co.uk/vocabulary.htm
# http://www.languagelearninglibrary.org
#
# alternatives:
# http://www.learnalanguage.com/learn-french/french-words/
# http://www.ocr.org.uk/download/sm/ocr_13550_sm_voc_list.pdf
# http://www.france-pub.com/french/index3.html  British test list (SAT?)
# http://www.bbc.co.uk/languages/french/lj/glossary/
# http://www.languageguide.org/ has audio for vocab and readings

# http://french.about.com/od/verb_conjugations/a/parler.htm
# http://www.conjugation-fr.com/conjugate.php?verb=apprendre&x=0&y=0
# http://www.verbix.com/webverbix/go.php?T1=savoir&imageField.x=0&imageField.y=0&D1=3&H1=103
# http://conjugator.reverso.net/conjugation-french-verb-mourir.html
# http://machaut.uchicago.edu/?verb=descendre&action=search&resource=conjugator
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


