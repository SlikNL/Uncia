#!/usr/bin/env php
<?php
use Slik\Uncia;

require_once __DIR__.'/uncia.php';

$args = new Uncia\Args('Demonstrates Uncia capabilities');
$args->str('action')->required()->description('Which part to demonstrate')
	->list('args, cmds, io, ssh');
$args = $args->parse();

switch ($args->action) {
	case 'args':
		stdout('These are the parsed arguments:');
		stdout($args);
		stdout('For all the possibilities, check the source code');
		/*
			$args->int('foo')->required()->description('count of companies');
			$args->int('-n, --number');
			$args->str('-s, --string')->default('foo bar');
			$args->bool('-v, --verbose');
			$args->bool('-q, --quiet');
		*/
		break;
	case 'cmds':
		// Run shell commands easily, errors will throw RunError exceptions
		stdout('I am', run('whoami'));
		stdout('Using parameters:', run('echo ?', array('; rm / \\&')));
		stdout('Passing stdin:', run('cat -', array('stdin' => 'incoming data')));
		stdout('Final example:', run('cat ?', array('-'), array('stdin' => 'incoming data')));
		break;
	case 'io':
		stdout('Write to stdout with awesomeness:');
		stdout('Use', 'as many arguments', 'as you want');
		stdout(array('Even', 'non-string', 'ones'));
		stdout('Numbers: ', 124, 5/3, 1/3, 4.0);
		stdout('Booleans: ', 5 > 2, 1 > 3);
		stdout('Objects: ', $args, new Exception('foo'));
		stdout('Other:', null, function () {});
		stderr('Use stderr in a simple fashion');
		Out('Underline things')->underline('=')->stdout();

		stdout('Use convertion to string for your needs: '.str(5/3));

		// stdin returns a line at a time
		while ($line = stdin()) {
			stdout('Line from stdin:', $line);
		}

		prompt('Ask user for input');
		confirm('Confirm his intentions');
		break;
	case 'ssh':
		stdout('To avoid network connections, this demo is not live.');
		stdout('Read the source code for example.');

		/*
			$server = new Uncia\SSH('example.com');
			$server('whoami');
			$server('echo ?', 'foo');
			$server(array(
				'whoami', 'echo foo', array('echo ?', 'bar'),
			));
			$server->exists('directory/src/my-file');
			$server('mysqldump', array('destination' => 'local-file'));
			$server('mysql -u ?', 'john', array('source' => 'local-file'));
			$server->cp('local-path', 'remote:new-path');
			$server->cp('remote:file', 'local-path');
		*/
}
