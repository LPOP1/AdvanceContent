<?php


namespace AdvanceContent\command;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;

use pocketmine\Player;

use AdvanceContent\AdvanceContent;

class ContentRankCommand extends Command
{
	
	/** @var null|AdvanceContent */
	protected $plugin = null;
	
	/** @var string */
	public const PERMISSION = "user";
	
	
	public function __construct (AdvanceContent $plugin)
	{
		$this->plugin = $plugin;
		parent::__construct ("컨텐츠 순위", "컨텐츠 순위 명령어 입니다.");
		$this->setPermission (self::PERMISSION);
	}
	
	public function execute (CommandSender $player, string $label, array $args): bool
	{
		if (isset ($args [0]) and isset ($args [1]) and is_numeric ($args [1])) {
			if ($this->plugin->getData ()->isContent ($args [0])) {
				$count = 0;
				$rankindex = $args [1] * 5 - 4;
				$arr = [];
			//	foreach (AdvanceContent::$content [$args [0]]->getRank () as $name => $time) {
			foreach ($this->plugin->getData ()->rank ($args [0]) as $name => $time) {
					if (++$count >= ($args [1] * 5 - 4) and $count <= ($args [1] * 5)) {
						AdvanceContent::message ($player, "§l§6[" . $rankindex ++ . "위]§r§a {$name}§7님  클리어 시간: §a" . date ("i분 s초", $time) . "");
					}
				}
			} else {
				AdvanceContent::message ($player, "존재하지 않는 컨텐츠 입니다.");
			}
		} else {
			AdvanceContent::message ($player, "/컨텐츠 순위 (컨텐츠명) (페이지)");
		}
		return true;
	}
}