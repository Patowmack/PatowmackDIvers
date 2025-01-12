#
# get Italian verbs from http://www.verbix.com/languages/italian.shtml
# Top 10 verbs:  essere, potere, fare, andare, avere, sapere, dare, vedere, dire, parlare
# aprire, chiedere, chiudere correre, decidere, dire, dipingere, leggere, mettere, offrire,
# perdere, prendere, rispondere, scirvere, vedere, uscire, avere, essere, andare, lavorare, capire, conoscere,
# sapere, diventare, 
# other web sites might have different formatting
# clip entire tense such as presente or passato prossimo
# run clip.pl from command line with "verb tense" on command line
# there may be issues with accent marks
# add English translation as prompted
# program will create records in quizItalian/quiz.sqlite3
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
		       
printf("%s\n\n", $group);
# printf("%s\n", $ARGV[0]);
#
# prepare SQL
#
my $db = DBI->connect("dbi:SQLite:quiz.sqlite3", "", "", {RaiseError => 1, AutoCommit => 1});

#insert
   my $ith = $db->prepare(<<SQL) || die "SQL error:" . DBI->errstr;
   INSERT INTO quiz ("group", xOther, english) VALUES (?, ?, ?)
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

  
