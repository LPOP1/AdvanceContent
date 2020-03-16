<?php


namespace AdvanceContent;

class Content
{
	
	/** @var string */
	protected $content;
	
	/** @var array */
	protected $data = [];
	
	
	public function __construct (string $content, array $data)
	{
		$this->content = $content;
		$this->data = $data;
	}
	
	public function getContent (): string
	{
		return $this->content;
	}
	
	public function getDataArray (): array
	{
		return $this->data;
	}
	
	public function getClearCount (string $name, int $date = 1): int
	{
		if (!isset ($this->data ["player"] [$date])) {
			$this->data ["player"] [$date] = [];
		}
		if (isset ($this->data ["player"] [$date] [$name])) {
			return intval ($this->data ["player"] [$date] [$name]);
		}
		return 0;
	}
	
	public function getDayLimit (): int
	{
		return $this->data ["dayLimit"];
	}
	
	public function getRewards (): array
	{
		return $this->data ["reward"];
	}
	
	public function getSpawn (): string
	{
		return $this->data ["spawn"];
	}
	
	public function getEnd (): string
	{
		return $this->data ["end"];
	}
	
	public function clearData (string $name, int $date = 1, int $time = 10000): void
	{
		if (!isset ($this->data ["player"] [$date] [$name])) {
			$this->data ["player"] [$date] [$name] = 0;
		}
		$this->data ["player"] [$date] [$name] ++;
		$this->data ["time"] [$name] = $time;
	}
	
	public function getRank (): array
	{
		$arr = $this->data ["time"];
		asort ($arr);
		return $arr;
	}
	
	public function getPlayerRank (string $name): int
	{
		$index = 0;
		foreach ($this->getRank () as $nick => $time) {
			$index ++;
			if ($name === $nick) {
				return $index;
			}
		}
		return -1;
	}
	
}