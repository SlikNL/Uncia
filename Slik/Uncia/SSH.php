<?php
namespace Slik\Uncia;

require_once __DIR__ . '/run.php';

class SSH
{
	public function __construct($host)
	{
		$this->host = $host;
	}

	public function __invoke($command, $values = array(), array $options=array())
	{
		if (is_string($command)) {
			if (strpos($command, '?') === false && !$options) {
				$options = $values;
				$values = null;
			}
			$command = array(_run_placeholders($command, $values));
		} else {
			$options = $values;
			$values = null;

			$command = array_map(function ($c) {
				if (is_string($c)) {
					return $c;
				}
				if (!is_array($c)) {
					throw new SyntaxError('Strange parameter type: '.gettype($c));
				}
				if (count($c) === 0) {
					return '';
				}
				if (count($c) === 1) {
					return $c[0];
				}
				if (count($c) === 2) {
					return _run_placeholders($c[0], $c[1]);
				}
				throw new SyntaxError('Too many arguments');
			}, $command);
		}

		$options = array_merge(array(
			'destination' => null,
			'source' => null,
		), $options);

		$cmd = array('ssh');
		$args = array();

		if ($this->identity) {
			$cmd[] = '-i ?';
			$args[] = $this->identity;
		}

		if ($this->port) {
			$cmd[] = '-p ?';
			$args[] = $this->port;
		}

		$cmd[] = '?';
		$args[] = ($this->username ? $this->username.'@' : '').$this->host;

		$cmd[] = '?';
		$args[] = implode('&&', $command);

		if ($options['destination']) {
			$cmd[] = '> ?';
			$args[] = $options['destination'];
		}

		if ($options['source']) {
			$cmd[] = '< ?';
			$args[] = $options['source'];
		}

		return run(implode(' ', $cmd), $args);
	}

	public function cp($from, $to)
	{
		$prefix = ($this->username ? $this->username.'@' : '').$this->host.':';
		$from = preg_replace('/^remote:/', $prefix, $from);
		$to = preg_replace('/^remote:/', $prefix, $to);
		return run(
			'scp'
			.' -r'
			.($this->identity ? ' -i '.$this->identity : '')
			.($this->port ? ' -P '.$this->port : '')
			.' '.$from
			.' '.$to
		);
	}

	public function exists($path)
	{
		try {
			$this('test -e '.$path);
			return true;
		} catch (Exception\RunError $e) {
			return false;
		}
	}

	public function identity($path)
	{
		$this->identity = $path;
		return $this;
	}

	public function port($port)
	{
		$this->port = $port;
		return $this;
	}

	public function username($username)
	{
		$this->username = $username;
		return $this;
	}

	private $host, $identity, $port, $username;
}
