<?php


namespace AdvanceContent\command;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;

use pocketmine\Player;

use AdvanceContent\AdvanceContent;

class DeleteContentDataCommand extends Command
{
	
	/** @var null|AdvanceContent */
	protected $plugin = null;
	
	/** @var string */
	public const DELETE_COMMAND_DATA_PERMISSION = "op";
	
	
	public function __construct (AdvanceContent $plugin)
	{
		$this->plugin = $plugin;
		parent::__construct ("컨텐츠 삭제", "컨텐츠 삭제 명령어 입니다.");
		$this->setPermission (self::DELETE_COMMAND_DATA_PERMISSION);
	}
	
	public function execute (CommandSender $player, string $label, array $args): bool
	{
		if ($player instanceof Player) {
			if ($player->hasPermission (self::DELETE_COMMAND_DATA_PERMISSION)) {
				if (isset ($args [0])) {
					if ($this->plugin->getData ()->isContent ($args [0])) {
						$this->plugin->getData ()->deleteContent ($args [0]);
						AdvanceContent::message ($player, "§a{$args [0]}§r§7 컨텐츠를 삭제하셨습니다.");
					} else {
						AdavnceContent::message ($player, "존재하지 않는 컨텐츠 입니다.");
					}
				} else {
					AdvanceContent::message ($player, "/컨텐츠 삭제 (컨텐츠명)");
				}
			} else {
				AdvanceContent::message ($player, "당신은 이 명령어를 사용할 권한이 없습니다.");
			}
		} else {
			AdvanceContent::message ($player, "인게임에서만 사용이 가능합니다.");
		}
		return true;
	}
}