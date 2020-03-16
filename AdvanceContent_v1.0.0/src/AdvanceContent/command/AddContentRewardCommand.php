<?php


namespace AdvanceContent\command;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;

use pocketmine\Player;

use AdvanceContent\AdvanceContent;

class AddContentRewardCommand extends Command
{
	
	/** @var null|AdvanceContent */
	protected $plugin = null;
	
	/** @var string */
	public const PERMISSION = "op";
	
	
	public function __construct (AdvanceContent $plugin)
	{
		$this->plugin = $plugin;
		parent::__construct ("컨텐츠 보상추가", "컨텐츠 보상추가 명령어 입니다.");
		$this->setPermission (self::PERMISSION);
	}
	
	public function execute (CommandSender $player, string $label, array $args): bool
	{
		if ($player instanceof Player) {
			if ($player->hasPermission (self::PERMISSION)) {
				if (isset ($args [0]) and isset ($args [1]) and is_numeric ($args [1])) {
					if ($this->plugin->getData ()->isContent ($args [0])) {
						$item = $player->getInventory ()->getItemInHand ();
						if ($item->getId () !== 0) {
							$code = $item->getId () . ":" . $item->getDamage () . ":" . $args [1] . ":" . base64_encode ($item->getCompoundTag ());
							$this->plugin->getData ()->addReward ($args [0], "item", $code);
							AdvanceContent::message ($player, "보상을 추가하셨습니다.");
						} else {
							AdvanceContent::message ($player, "공기는 추가할 수 없습니다.");
						}
					} else {
						AdvanceContent::message ($player, "존재하지 않는 컨텐츠 입니다.");
					}
				} else {
					AdvanceContent::message ($player, "/컨텐츠 보상추가 (컨텐츠명) (수량)");
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