# arg_parser
A simple command line option parser.

# Example:
```php
$parser = new ArgParser;
$parser->addBool('flag', false);
$parser->addInt('times', 3);
$parser->addFloat('salary', 14000.00);
$parser->addString('c', 'this is comment');

$parser->parse();
var_dump($parser->getArgs());

$opts = $parser->getOptions();
var_dump($opts);

extract($opts);
// $flag
// $times
// $salary
// $c

var_dump($parser->getOption('flag'));
```
