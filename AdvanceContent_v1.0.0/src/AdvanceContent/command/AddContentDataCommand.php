<?php


namespace AdvanceContent\command;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;

use pocketmine\Player;

use pocketmine\block\SignPost;
use pocketmine\tile\Sign;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;

use AdvanceContent\AdvanceContent;

class AddContentDataCommand extends Command implements Listener
{
	
	/** @var null|AdvanceContent */
	protected $plugin = null;
	
	/** @var string */
	public const ADD_COMMAND_DATA_PERMISSION = "op";
	
	/** @var array */
	private $mode = [];
	
	
	public function __construct (AdvanceContent $plugin)
	{
		$this->plugin = $plugin;
		parent::__construct ("컨텐츠 추가", "컨텐츠 추가 명령어 입니다.");
		$this->setPermission (self::ADD_COMMAND_DATA_PERMISSION);
		$this->plugin->getServer ()->getPluginManager ()->registerEvents ($this, $plugin);
	}
	
	public function execute (CommandSender $player, string $label, array $args): bool
	{
		if ($player instanceof Player) {
			if ($player->hasPermission (self::ADD_COMMAND_DATA_PERMISSION)) {
				// /컨텐츠 추가 (컨텐츠명) (하루제한)
				if (isset ($args [0]) and isset ($args [1]) and is_numeric ($args [1])) {
					if (!$this->plugin->getData ()->isContent ($args [0])) {
						$this->mode [$player->getName ()] = [
							"content" => $args [0],
							"dayLimit" => $args [1],
							"start" => "false"
						];
						AdvanceContent::message ($player, "컨텐츠 시작 위치를 터치해주세요.");
					} else {
						AdvanceContent::message ($player, "이미 존재하는 컨텐츠 입니다.");
					}
				} else {
					AdvanceContent::message ($player, "/컨텐츠 추가 (컨텐츠명) (하루제한수)");
				}
			} else {
				AdvanceContent::message ($player, "당신은 이 명령어를 사용할 권한이 없습니다.");
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
		
		if (isset ($this->mode [$player->getName ()])) {
			$pos = intval ($block->x) . ":" . intval ($block->y) . ":" . intval ($block->z) . ":" . $block->level->getFolderName ();
			if ($this->mode [$player->getName ()] ["start"] !== "false") {
			   $data = $this->mode [$player->getName ()];
				if ($block instanceof SignPost) {
					$tile = $block->level->getTile ($block);
					if ($tile instanceof Sign) {
						$tile->setText (
							"§l§6【 §f컨텐츠 클리어 §6】",
							"§f컨텐츠: §a" . $data ["content"] . "",
							"§7터치로 클리어가 가능 !",
							"§6*§7 하루에 §a{$data ["dayLimit"]}번§7 클리어 가능 §a*"
						);
						$data = $this->mode [$player->getName ()];
				$this->plugin->getData ()->addContent ($data ["content"], $data ["start"], $pos, (int) $data ["dayLimit"]);
				AdvanceContent::message ($player, "§a{$data ["content"]}§r§7 컨텐츠를 생성하셨습니다.");
					}
					unset ($this->mode [$player->getName ()]);
				}
			} else {
				$this->mode [$player->getName ()] ["start"] = $pos;
				AdvanceContent::message ($player, "컨텐츠 클리어 블럭 위치를 터치해주세요. (표지판 or 블럭)");
			}
		}
	}
}