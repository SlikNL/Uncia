<?php
namespace Slik\Uncia;

class Progress
{
	public function __construct($total)
	{
		assert('$total > 0');
		$this->total = $total;
		$this->show();
		$this->started = time();
	}

	public function inc()
	{
		$this->curr++;
		$this->show();
	}

	private $curr = 0;
	private $last;
	private $started;
	private $total;

	private function show()
	{
		$now = time();
		if ($now - 1 < $last) {
			return;
		}
		echo ((($now - $started) / $this->curr) * ($this->total - $this->curr)).'S';
		return;
		$remaining = new \DateInterval(((($now - $started) / $this->curr) * ($this->total - $this->curr)).'S');
		return;
		$percentage = number_format(floor($this->curr * 10000 / $this->total) / 100, 2);
		Output::create(
			"\r"
			.$percentage.'%'
			.' '.$remaining->format('%d days, %h hours, %m minutes, %s seconds remaining')
		)->noNewline()->stderr();
	}
}
