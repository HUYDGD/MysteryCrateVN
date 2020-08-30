<?php
declare(strict_types = 1);

/*
___  ___          _                  _____           _
|  \/  |         | |                /  __ \         | |
| .  . |_   _ ___| |_ ___ _ __ _   _| /  \/_ __ __ _| |_ ___
| |\/| | | | / __| __/ _ \ '__| | | | |   | '__/ _` | __/ _ \
| |  | | |_| \__ \ ||  __/ |  | |_| | \__/\ | | (_| | ||  __/
\_|  |_/\__, |___/\__\___|_|   \__, |\____/_|  \__,_|\__\___|
		 __/ |                   __/ |
		 |___/                   |___/  Bởi @JackMD cho PMMP


MysteryCrate, một plugin thùng cho PocketMine-MP
Bản quyền (©) 2018 JackMD <https://github.com/JackMD>

Discord: JackMD#3717
Twitter: JackMTaylor_

Phần mềm này được phân phối theo "Giấy phép Công cộng GNU v3.0".
Giấy phép này cho phép bạn sử dụng hoặc sửa đổi nó nhưng bạn không được phép
bán plugin này với bất kỳ giá nào. Nếu bị phát hiện làm như vậy, một
hành động cần thiết bắt buộc sẽ được thực hiện.

MysteryCrate được phân phối với hy vọng rằng nó sẽ hữu ích,
nhưng KHÔNG CÓ BẤT KỲ BẢO HÀNH NÀO; mà không có bảo hành ngụ ý
KHẢ NĂNG PHÁT TRIỂN HOẶC PHÙ HỢP VỚI MỤC ĐÍCH CỤ THỂ. Xem
Giấy phép Công cộng GNU v3.0 để biết thêm chi tiết.

Bạn sẽ nhận được một bản sao của Giấy phép Công cộng GNU v3.0
cùng với chương trình này. Nếu không, hãy xem
<https://opensource.org/licenses/GPL-3.0>.
-----------------------------------------------------------------------
*/

namespace JackMD\MysteryCrate;

use JackMD\MysteryCrate\libs\JackMD\ConfigUpdater\ConfigUpdater;
use JackMD\MysteryCrate\command\KeyAllCommand;
use JackMD\MysteryCrate\command\KeyCommand;
use JackMD\MysteryCrate\lang\Lang;
use JackMD\MysteryCrate\particle\CloudRain;
use JackMD\MysteryCrate\particle\Crown;
use JackMD\MysteryCrate\particle\DoubleHelix;
use JackMD\MysteryCrate\particle\Helix;
use JackMD\MysteryCrate\particle\ParticleType;
use JackMD\MysteryCrate\particle\Ting;
use JackMD\MysteryCrate\libs\JackMD\UpdateNotifier\UpdateNotifier;
use JackMD\MysteryCrate\libs\muqsit\invmenu\InvMenu;
use JackMD\MysteryCrate\libs\muqsit\invmenu\InvMenuHandler;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\Item;
use pocketmine\level\particle\FloatingTextParticle;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\StringTag;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;

class Main extends PluginBase{

	/** @var int */
	private const CRATES_VERSION = 1;
	/** @var int */
	private const CONFIG_VERSION = 2;

	/** @var array */
	private $crates = [];
	/** @var array */
	private $crateDrops = [];
	/** @var array */
	private $crateBlocks = [];
	/** @var array */
	private $crateBroadcast = [];
	/** @var array */
	private $crateBroadcastMessage = [];
	/** @var null|FloatingTextParticle[] */
	private $textParticles;
	/** @var Config */
	private $blocksConfig;

	private function performChecks(): void{
		$this->checkVirions();

		Lang::init($this);

		$this->saveDefaultConfig();
		$this->initCrates();
		$this->checkConfigs();

		UpdateNotifier::checkUpdate($this, $this->getDescription()->getName(), $this->getDescription()->getVersion());
	}

	/**
	 * Kiểm tra xem các virion/thư viện được yêu cầu có được cài hay không trước khi bật plugin.
	 */
	private function checkVirions(): void{
		if(!class_exists(UpdateNotifier::class) || !class_exists(InvMenu::class) || !class_exists(ConfigUpdater::class)){
			throw new \RuntimeException("Plugin MysteryCrate sẽ chỉ hoạt động nếu bạn sử dụng phar từ Poggit.");
		}
	}

	public function initCrates(): void{
		$this->saveResource("crates.yml");
		$this->saveResource("blocks.yml");
		$this->blocksConfig = new Config($this->getDataFolder() . "blocks.yml");

		$config = new Config($this->getDataFolder() . "crates.yml");
		foreach($config->getNested("crates") as $type => $values){
			$this->crates[$type] = $values;
			$this->crateDrops[$type] = $values["drops"];
			$this->crateBlocks[$values["block"]] = $type;
			$this->crateBroadcast[$type] = $values["broadcast"]["enable"];
			$this->crateBroadcastMessage[$type] = $values["broadcast"]["command"];
		}
	}

	/**
	 * Kiểm tra xem các cấu hình có được cập nhật hay không.
	 */
	private function checkConfigs(): void{
		$cratesConfig = new Config($this->getDataFolder() . "crates.yml", Config::YAML);

		ConfigUpdater::checkUpdate($this, $cratesConfig, "crates-version", self::CRATES_VERSION);
		ConfigUpdater::checkUpdate($this, $this->getConfig(), "config-version", self::CONFIG_VERSION);
	}

	public function onEnable(): void{
		$this->performChecks();

		$this->initParticles();
		$this->initTextParticle();

		if(!InvMenuHandler::isRegistered()){
			InvMenuHandler::register($this);
		}

		$this->getServer()->getCommandMap()->register("mysterycrate", new KeyCommand($this));
		$this->getServer()->getCommandMap()->register("mysterycrate", new KeyAllCommand($this));
		$this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
		$this->getLogger()->info("§aMysteryCrate bản ". $this->getDescription()->getVersion() ." §eFULL VIỆT HÓA §ađã được bật!");
        $this->getLogger()->info("§aPlugin được dịch bởi Sói Oniichan.");
        $this->getLogger()->info("§cYoutube: §ehttps://www.youtube.com/SoiOniichan");
	}

	private function initParticles(): void{
		if($this->getConfig()->get("showParticle")){
			$crateWorld = (string) $this->getConfig()->get("crateWorld");
			if(!$this->getServer()->isLevelLoaded($crateWorld)){
				$this->getServer()->loadLevel($crateWorld);
			}
			if($this->getServer()->getLevelByName($crateWorld) !== null){
				$this->initParticleShow();
			}else{
				$this->getServer()->getLogger()->critical("Vui lòng đặt crateWorld trong config.yml. Hoặc đảm bảo rằng thế giới đã cho tồn tại và được tải.");
			}
		}
	}

	private function initParticleShow(): void{
		$blocksConfig = $this->blocksConfig;
		$types = $this->getCrateTypes();
		$particleType = (string) $this->getConfig()->get("particleType");
		$particleTickRate = (int) $this->getConfig()->get("particleTickRate");

		foreach($types as $type){
			$x = $blocksConfig->get("$type.x");
			$y = $blocksConfig->get("$type.y");
			$z = $blocksConfig->get("$type.z");

			if(!empty($x)){
				$pos = new Vector3($x + 0.5, $y, $z + 0.5);
				$task = null;

				switch($particleType){
					case ParticleType::HELIX:
						$task = new Helix($this, $pos);
						break;
					case ParticleType::DOUBLE_HELIX:
						$task = new DoubleHelix($this, $pos);
						break;
					case ParticleType::CLOUD_RAIN:
						$task = new CloudRain($this, $pos);
						break;
					case ParticleType::TING:
						$task = new Ting($this, $pos);
						break;
					case ParticleType::CROWN:
						$task = new Crown($this, $pos);
						break;
				}

				if(!is_null($task)){
					$this->getScheduler()->scheduleRepeatingTask($task, $particleTickRate);
				}else{
					$this->getLogger()->error("Vui lòng thiết lập chính xác hiệu ứng trong config.yml. Các loại được phép là CloudRain, Helix, DoubleHelix, Ting và Crown");
				}
			}
		}
	}

	/**
	 * @return array
	 */
	public function getCrateTypes(): array{
		return array_keys($this->crates);
	}

	/**
	 * @param Player $player
	 */
	public function addParticles(Player $player): void{
		if(isset($this->textParticles)){
			$particles = array_values($this->textParticles);
			foreach($particles as $particle){
				if($particle instanceof FloatingTextParticle){
					foreach($particle->encode() as $packet){
						$particle->setInvisible(false);
						$player->dataPacket($packet);
					}
				}
			}
		}
	}

	public function initTextParticle(): void{
		$blocksConfig = $this->blocksConfig;
		$types = $this->getCrateTypes();

		foreach($types as $type){
			$text = $blocksConfig->get($type);
			$x = $blocksConfig->get($type . ".x");
			$y = $blocksConfig->get($type . ".y");
			$z = $blocksConfig->get($type . ".z");

			if(!empty($x)){
				$pos = new Vector3($x + 0.5, $y + 1, $z + 0.5);
				$this->textParticles[$type] = new FloatingTextParticle($pos, '', $text . TextFormat::RESET);
			}
		}
	}

	/**
	 * @param string $type
	 * @return int
	 */
	public function getCrateDropAmount(string $type): int{
		return !$this->getCrateType($type) ? 0 : $this->crates[$type]["amount"];
	}

	/**
	 * @param string $type
	 * @return bool|array
	 */
	public function getCrateType(string $type){
		return isset($this->crates[$type]) ? $this->crates[$type] : false;
	}

	/**
	 * @param string $type
	 * @return null|array
	 */
	public function getCrateBlock(string $type){
		return $this->getCrateDrops($type) ? $this->crateBlocks[$type] : null;
	}

	/**
	 * @param string $type
	 * @return null|array
	 */
	public function getCrateDrops(string $type){
		return $this->getCrateType($type) ? $this->crateDrops[$type] : null;
	}

	/**
	 * @param int $id
	 * @param int $meta
	 * @return bool|string
	 */
	public function isCrateBlock(int $id, int $meta){
		return isset($this->crateBlocks[$id . ":" . $meta]) ? $this->crateBlocks[$id . ":" . $meta] : false;
	}

	/**
	 * @param Item $item
	 * @return bool|string
	 */
	public function isCrateKey(Item $item){
		$values = explode(":", $this->getConfig()->getNested("key"));

		return ((int) $values[0] === $item->getId()) && ((int) $values[1] === $item->getDamage()) && (!is_null($keyType = $item->getNamedTagEntry("KeyType"))) ? $keyType->getValue() : false;
	}

	/**
	 * @param string $type
	 * @return bool
	 */
	public function isBroadcastEnabled(string $type): bool{
		return $this->crateBroadcast[$type];
	}

	/**
	 * @param string $type
	 * @return string
	 */
	public function getBroadcastMessage(string $type): string{
		return $this->crateBroadcastMessage[$type];
	}

	/**
	 * @param Player $player
	 * @param string $type
	 * @param int    $amount
	 * @return bool
	 */
	public function giveKey(Player $player, string $type, int $amount): bool{
		if(is_null($this->getCrateDrops($type))){
			return false;
		}

		$keyID = (string) $this->getConfig()->get("key");

		$key = Item::fromString($keyID);
		$key->setCount($amount);
		$key->setLore([$this->getConfig()->get("lore")]);
		$key->addEnchantment(new EnchantmentInstance(new Enchantment(255, "", Enchantment::RARITY_COMMON, Enchantment::SLOT_ALL, Enchantment::SLOT_NONE, 1)));
		$key->setCustomName(ucfirst($type . " Key"));
		$key->setNamedTagEntry(new StringTag("KeyType", $type));

		$player->getInventory()->addItem($key);

		return true;
	}

	/**
	 * @return Config
	 */
	public function getBlocksConfig(): Config{
		return $this->blocksConfig;
	}

	/**
	 * @return null|FloatingTextParticle[]
	 */
	public function getTextParticles(): ?array{
		return $this->textParticles;
	}
}
