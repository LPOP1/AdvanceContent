<?php


namespace AdvanceContent;

use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;

use AdvanceContent\provider\DataProvider;
use AdvanceContent\command\{
	AddContentDataCommand,
	DeleteContentDataCommand,
	ContentRankCommand,
	ContentGiveUpCommand,
	AddContentRewardCommand
};

use pocketmine\scheduler\Task;
use pocketmine\entity\Entity;
use pocketmine\math\Vector3;
use pocketmine\level\Position;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\network\mcpe\protocol\AddPlayerPacket;
use pocketmine\utils\UUID;
use pocketmine\network\mcpe\protocol\RemoveActorPacket;

use pocketmine\Player;
use CustomUI\CustomUI;

class AdvanceContent extends PluginBase
{
	
	private static $instance = null;
	
	public static $prefix = "§l§6[알림]§r§7 ";
	
	protected $config;
	
	protected $db = null;
	
	public static $content = [];
	
	public $blockTag = [];
	
	public $coontent = [];
	
	
	public static function runFunction (): AdvanceContent
	{
		return self::$instance;
	}
	
	public function onLoad (): void
	{
		if (self::$instance === null) {
			self::$instance = $this;
		}
		if (!file_exists ($this->getDataFolder ())) {
			@mkdir ($this->getDataFolder ());
		}
		$this->config = new Config ($this->getDataFolder () . "config.yml", Config::YAML, [
			"content" => []
		]);
		$this->db = new DataProvider ($this, $this->config);
	}
	
	public function onEnable (): void
	{
		$this->getServer ()->getCommandMap ()->registerAll ("avas", [
			new AddContentDataCommand ($this),
			new DeleteContentDataCommand ($this),
			new ContentRankCommand ($this),
			new ContentGiveUpCommand ($this),
			new AddContentRewardCommand ($this)
		]);
		/*$this->getScheduler ()->scheduleRepeatingTask (new class ($this) extends Task{
			protected $plugin;
			
			public function __construct (AdvanceContent $plugin)
			{
				$this->plugin = $plugin;
			}
			
			public function onRun (int $currentTick)
			{
				$this->plugin->sendTag ();
			}
		}, 25 * 5);*/
	}
	
	public function onDisable (): void
	{
		$this->db->onSave ();
	}
	
	public static function message ($player, string $msg): void
	{
		$player->sendMessage (self::$prefix . $msg);
	}
	
	public function getData (): DataProvider
	{
		return $this->db;
	}
	
	/*public function blockTag (string $pos, array $data): void
	{
		[ $x, $y, $z, $level ] = explode (":", $pos);
		foreach ($this->getServer ()->getOnlinePlayers () as $player) {
			if (isset ($this->blockTag [$pos])) {
				$packet = new RemoveActorPacket ();
				$packet->entityUniqueId = $this->blockTag [$pos];
				$player->sendDataPacket ($packet);
			}
			if ($player->getLevel ()->getFolderName () === $level) {
				$vector = new Vector3 (intval ($x) + 0.5, intval ($y), intval ($z) + 0.5);
				$uuid = UUID::fromRandom ();
				$packet = new AddPlayerPacket ();
				$packet->uuid = $uuid;
				$packet->username = "§l§6【 §f컨텐츠 클리어 §6】\n§f컨텐츠: §a" . $data ["content"] . "\n§7터치로 클리어가 가능합니다 !\n§6*§7 하루에 §a{$data ["dayLimit"]}번§7 클리어 가능 §a*";
				$eid = Entity::$entityCount ++;
				$this->blockTag [$pos] = $eid;
				$packet->entityRuntimeId = $eid;
				$packet->position = $vector;
				$packet->item = ItemFactory::get (Item::AIR);
				$flags = (1 << Entity::DATA_FLAG_IMMOBILE);
				$packet->metadata = [
					Entity::DATA_FLAGS => [
						Entity::DATA_TYPE_LONG,
						$flags
					],
					Entity::DATA_SCALE => [
						Entity::DATA_TYPE_FLOAT,
						0.01
					]
				];
				$player->sendDataPacket ($packet);
			}
		}
	}
	
	public function sendTag (): void
	{
		foreach (self::$content as $name => $class) {
			if ($class instanceof Content) {
				$this->blockTag ($class->getEnd (), [
					"content" => $name,
					"dayLimit" => $class->getDayLimit ()
				]);
			}
		}
	}*/
	
	public function ContentUI (Player $player): void
	{
		if (!isset ($this->coontent [$player->getName ()])) {
			$arr = [];
			$index = 0;
			foreach ($this->getData ()->getContents () as $name => $data) {
				$arr [$index ++] = $name;
			}
			$handle = CustomUI::runFunction ()->SimpleForm (function (Player $player, array $data) {
				if (!isset ($data [0])) {
					return false;
				}
				$db = [];
				$index = 0;
				foreach ($this->getData ()->getContents () as $name => $dd) {
					$db [$index ++] = $name;
				}
				$num = $data [0];
			//	echo self::$content [$db [$num]];
				if (isset ($db [$num])) {
					$content = self::$content [$db [$num]];
					if ($content instanceof Content) {
						[ $x, $y, $z, $level ] = explode (":", $content->getSpawn ());
						$player->teleport (new Position ((float) $x + 0.5, (float) $y, (float) $z + 0.5, $this->getServer ()->getLevelByName ($level)));
						self::message ($player, "§a{$db [$num]}§r§7 컨텐츠를 시작합니다. 빨리 깨주세요!");
						$this->coontent [$player->getName ()] = [
							"content" => $db [$num],
							"end" => $content->getEnd (),
							"time" => time ()
						];
					}
				} else {
					return false;
				}
			});
			$handle->setTitle ("§l컨텐츠");
			$handle->setContent ("\n§f플레이를 원하시는 컨텐츠를 눌러주세요.\n§f버튼을 누르자 마자 타이머가 시작됩니다!");
			foreach ($this->getData ()->getContents () as $name => $data) {
				$handle->addButton ("§l▶ {$name}\n§r§8- §3{$name}§8 컨텐츠 플레이 하기! -");
			}
			$handle->sendToPlayer ($player);
		} else {
			self::message ($player, "이미 컨텐츠를 진행중입니다. 포기하실려면 /컨텐츠 포기 명령어를 해주세요.");
		}
	}
}