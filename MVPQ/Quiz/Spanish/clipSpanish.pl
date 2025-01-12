#
# get Spanish verbs from http://www.verbix.com/languages/spanish.shtml
# Top 10 verbs:  iestar, ir, ser, hacer, tener, decir, venir, oir, poder, poner
# other web sites might have different formatting
#
# clip entire tense such as presente or passato prossimo
# run clip.pl from command line with "verb tense" on command line
# there may be issues with accent marks
# add English translation as prompted
# program will create records in quizSpanish/quiz.sqlite3
# program will create records in G:\XAMPP\xampplite\apache\htdocs\Quiz\quizSpanish.sqlite3 (see $db)
#

use Win32::Clipboard;
use DBI;
use Encode;

$text = Win32::Clipboard::GetAs(CF_UNICODETEXT);
$text = Encode::decode("UTF16-LE", $text);

@english =('I', 'you (s)', 'he', 'we', 'you (p)', 'they');
@lines = split(/\n/, $text);

my $group = $ARGV[0];  # modify to get as many parameters as there are? or 
                       # use "
		       # perl clip.pl "fare pas rem"
		       
printf("%s\n\n", $group);
# printf("%s\n", $ARGV[0]);
#
# prepare SQL
#
#my $db = DBI->connect("dbi:SQLite:quiz.sqlite3", "", "", {RaiseError => 1, AutoCommit => 1});
my $db = DBI->connect("dbi:SQLite:G:/XAMPP/xampplite/apache/htdocs/Quiz/quizSpanish.sqlite3", "", "", {RaiseError => 1, AutoCommit => 1});
#insert
   my $ith = $db->prepare(<<SQL) || die "SQL error:" . DBI->errstr;
   INSERT INTO quiz ("group", xOther, english, language) VALUES (?, ?, ?, "Spanish")
SQL

#
# clipboard
#
my $i;
foreach (@lines){

   $line = $_;
   @lines0 = split(/\s+/, $line);
   $pronoun = splice(@lines0, 0, 1);
   printf("%s %s->%s ", $pronoun, join(' ', @lines0), $english[$i]);
   $read = <STDIN>;
   chomp($read);
   $english = $english[$i] . " " . $read;
   $xOther = join(' ', @lines0);
   printf("%s->%s->\'%s\'\n\n", $group, $english, $xOther);
   $ith->execute($group, $xOther, $english);
   $i++;
}

  
