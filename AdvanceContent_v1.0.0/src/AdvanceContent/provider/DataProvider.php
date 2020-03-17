<?php


namespace AdvanceContent\provider;

use pocketmine\utils\Config;
use pocketmine\Player;

use pocketmine\item\Item;

use AdvanceContent\AdvanceContent;
use AdvanceContent\Content;

class DataProvider
{
	
	/** @var null|AdvanceContent */
	protected $plugin = null;
	
	/** @var Config */
	protected $config;
	
	/** @var array */
	protected $data = [];
	
	
	public function __construct (AdvanceContent $plugin, Config $config)
	{
		$this->plugin = $plugin;
		$this->config = $config;
		$this->data = $this->config->getAll ();
		
		foreach (array_keys ($this->data ["content"]) as $name) {
			AdvanceContent::$content [$name] = new Content ($name, $this->data ["content"] [$name]);
		}
	}
	
	public function onSave (): void
	{
		$this->config->setAll ($this->data);
		$this->config->save ();
	}
	
	public function isContent (string $name): bool
	{
		return isset ($this->data ["content"] [$name]);
	}
	
	public function addContent (string $name, string $start, string $end, int $dayLimit = -1): void
	{
		$this->data ["content"] [$name] = [
			"dayLimit" => $dayLimit,
			"reward" => [],
			"spawn" => $start,
			"end" => $end,
			"player" => [],
			"time" => []
		];
		AdvanceContent::$content [$name] = new Content ($name, $this->data ["content"] [$name]);
	}
	
	public function deleteContent (string $name): void
	{
		unset ($this->data ["content"] [$name]);
		unset (AdvanceContent::$content [$name]);
	}
	
	public function clearContent (Player $player, string $content, int $time): void
	{
		$name = $player->getName ();
		
		if (($class = AdvanceContent::$content [$content]) instanceof Content) {
			if ($class->getClearCount ($name, date ("m:d")) < $class->getDayLimit ()) {
				foreach ($class->getRewards ($content) as $reward => $bool) {
					$type = explode (":", $reward) [0];
					if ($type === "item") {
						$item = Item::get (explode (":", $reward) [1], explode (":", $reward) [2], explode (":", $reward) [3], base64_decode (explode (":", $reward) [4]));
						$player->getInventory ()->addItem ($item);
					}
				}
				$nowTime = time ();
				$record = $nowTime - $time;
				$class->clearData ($name, date ("m:d"), $record);
				foreach ($this->plugin->getServer ()->getOnlinePlayers () as $players) {
					AdvanceContent::message ($players, "§a{$content}§r§a 컨텐츠§7 을(를) §e" . date ("i분 s초", $record) . "§7 안에 클리어 하셨습니다. [컨텐츠 순위: §a" . $class->getPlayerRank ($name) . "위§7]");
				}
			} else {
				AdvanceContent::message ($player, "오늘은 해당 컨텐츠를 더의상 클리어 할 수 없습니다.");
			}
		}
	}
	
	public function getContents (): array
	{
		return $this->data ["content"];
	}
	
	public function getRewards (string $name): array
	{
		return $this->data ["content"] [$name] ["reward"];
	}
	
	public function addReward (string $name, string $type = "item", string $code): void
	{
		$this->data ["content"] [$name] ["reward"] [$type . ":" . $code] = true;
	}
	
	public function deleteReward (string $name, string $code): void
	{
		unset ($this->data ["content"] [$name] ["reward"] [$code]);
	}
	
	public function rank (string $content): array
	{
	   return asort ($this->data ["content"] [$content] ["time"]);
	}
}
