<?php


namespace AdvanceContent\command;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;

use pocketmine\Player;

use AdvanceContent\AdvanceContent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;


class ContentGiveUpCommand extends Command implements Listener
{
	
	/** @var null|AdvanceContent */
	protected $plugin = null;
	
	/** @var string */
	public const PERMISSION = "user";
	
	
	public function __construct (AdvanceContent $plugin)
	{
		$this->plugin = $plugin;
		parent::__construct ("컨텐츠 포기", "컨텐츠 포기 명령어 입니다.");
		$this->setPermission (self::PERMISSION);
		$this->plugin->getServer ()->getPluginManager ()->registerEvents ($this, $this->plugin);
	}
	
	public function execute (CommandSender $player, string $label, array $args): bool
	{
		if ($player instanceof Player) {
			if (isset ($this->plugin->coontent [$player->getName ()])) {
				unset ($this->plugin->coontent [$player->getName ()]);
				AdvanceContent::message ($player, "컨텐츠를 포기하셨습니다.");
			} else {
				$this->plugin->ContentUI ($player);
			}
		} else {
			AdvanceContent::message ($player, "인게임에서만 사용이 가능합니다.");
		}
		return true;
	}
	
	public function onInteract (PlayerInteractEvent $event): void
	{
		$player = $event->getPlayer ();
		$block = $event->getBlock ();
		
		if (isset ($this->plugin->coontent [$player->getName ()])) {
			$pos = intval ($block->x) . ":" . intval ($block->y) . ":" . intval ($block->z) . ":" . $block->level->getFolderName ();
			if ($this->plugin->coontent [$player->getName ()] ["end"] === $pos) {
				$this->plugin->getData ()->clearContent ($player, $this->plugin->coontent [$player->getName ()] ["content"], $this->plugin->coontent [$player->getName ()] ["time"]);
				unset ($this->plugin->coontent [$player->getName ()]);
			}
		}
	}
}