# arg_parser
A simple command line option parser.

# Example:
```php
ArgParser::addBool('flag', false);
ArgParser::addInt('times', 3);
ArgParser::addFloat('salary', 14000.00);
ArgParser::addString('c', 'this is comment');

ArgParser::parse();
var_dump(ArgParser::getArgs());

$opts = ArgParser::getOptions();
var_dump($opts);

extract($opts);
// $flag
// $times
// $salary
// $c

var_dump(ArgParser::getOption('flag'));
```
