<?php

/**
 * ___  ___          _                  _____           _
 *  |  \/  |         | |                /  __ \         | |
 *  | .  . |_   _ ___| |_ ___ _ __ _   _| /  \/_ __ __ _| |_ ___
 * | |\/| | | | / __| __/ _ \ '__| | | | |   | '__/ _` | __/ _ \
 *  | |  | | |_| \__ \ ||  __/ |  | |_| | \__/\ | | (_| | ||  __/
 *  \_|  |_/\__, |___/\__\___|_|   \__, |\____/_|  \__,_|\__\___|
 *           __/ |                  __/ |
 *          |___/                  |___/  Bởi @JackMD cho PMMP
 *                                              
 *
 * MysteryCrate, là một plugin Crate cho PocketMine-MP
 * Bản Quyền (c) 2018 JackMD  < https://github.com/JackMD >
 *
 * Discord: JackMD#3717
 * Twitter: JackMTaylor_
 *
 * Phần mềm này được phân phối theo "Giấy phép Công cộng GNU v3.0".
 * Giấy phép này cho phép bạn sử dụng hoặc sửa đổi nó nhưng bạn không
 * được phép bán plugin này bằng mọi giá. Nếu làm như vậy, chúng tôi
 * sẽ thực hiện một hành động cần thiết.
 *
 * MysteryCrate được phân phối với hy vọng rằng nó sẽ hữu ích,
 * nhưng KHÔNG CÓ BẤT KÌ BẢO HÀNH; thậm chí không có bảo hành ngụ ý
 * TRÁCH NHIỆM hoặc PHÙ HỢP CHO MỘT MỤC ĐÍCH CỤ THỂ. Xem
 * GNU General Public License v3.0 để biết thêm chi tiết.
 *
 * Bạn đã nhận được một bản sao của Giấy phép Công cộng GNU v3.0
 * cùng với chương trình này. Nếu không, hãy xem
 * <https://opensource.org/licenses/GPL-3.0>.
 *
 * PLUGIN ĐƯỢC DỊCH RA TIẾNG VIỆT BỞI SÓI
 * BẢN QUYỀN PLUGIN THUỘC VỀ JACKMD
 *  -----------------------------------------------------------------------
 */

namespace JackMD\MysteryCrate;

use JackMD\MysteryCrate\Commands\KeyCommand;
use JackMD\MysteryCrate\Particles\CloudRain;
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


class Main extends PluginBase
{
	public $notInUse = true;
	public $task, $crates, $crateDrops, $crateBlocks, $textParticles;
	private $key;


	public $blocks;

	public function onEnable() : void
	{
		$this->task = new UpdaterEvent($this);

		if (!is_dir($this->getDataFolder())) {
			mkdir($this->getDataFolder());
		}

		$this->saveDefaultConfig();
		$this->initCrates();
		$this->initTextParticle();

		if ($this->getConfig()->get("showParticle") !== false) {
			if ($this->getServer()->getLevelByName($this->getConfig()->get("crateWorld")) !== null) {
				$this->initParticleShow();
			} else {
				$this->getServer()->getLogger()->critical("Hãy đặt tên thế giới ở crateWorld trong config.yml !");
			}
		}

		$this->key = $this->getConfig()->getNested("key");
		$this->getServer()->getCommandMap()->register("key" , new KeyCommand("key" , $this) , "key");
		$this->getServer()->getPluginManager()->registerEvents(new EventListener($this) , $this);
		$this->getLogger()->info("Plugin đã được bật !");

	}

	public function initCrates()
	{
		$this->saveResource("crates.yml");
		$file = new Config($this->getDataFolder() . "crates.yml");
		foreach ($file->getNested("crates") as $type => $values) {
			$this->crates[$type] = $values;
			$this->crateDrops[$type] = $values["drops"];
			$this->crateBlocks[$values["block"]] = $type;
		}
		$this->saveResource("blocks.yml");
		$this->blocks = new Config($this->getDataFolder() . "blocks.yml");
	}

	public function initTextParticle()
	{
		$positions = $this->blocks;
		$types = $this->getCrateTypes();
		foreach ($types as $type) {
			$text = $positions->get($type);
			$x = $positions->get("$type.x");
			$y = $positions->get("$type.y");
			$z = $positions->get("$type.z");
			if (!empty($x)) {
				$pos = new Vector3($x + 0.5 , $y + 1 , $z + 0.5);
				$this->textParticles[$type] = new FloatingTextParticle($pos , '' , $text . TextFormat::RESET);
			}
		}
	}

	public function initParticleShow()
	{
		$positions = $this->blocks;
		$types = $this->getCrateTypes();
		foreach ($types as $type) {
			$x = $positions->get("$type.x");
			$y = $positions->get("$type.y");
			$z = $positions->get("$type.z");
			if (!empty($x)) {
				$pos = new Vector3($x + 0.5 , $y , $z + 0.5);
				$taskCloud = new CloudRain($this, $pos);
				$this->getServer()->getScheduler()->scheduleRepeatingTask($taskCloud, 5);
			}
		}
	}


	public function isNotInUse() : bool
	{
		return $this->notInUse;
	}


	public function setNotInUse(bool $notInUse)
	{
		$this->notInUse = $notInUse;
	}


	public function getCrateTypes()
	{
		return array_keys($this->crates);
	}


	public function getCrateDropAmount(string $type)
	{
		return !$this->getCrateType($type) ? 0 : $this->crates[$type]["amount"];
	}


	public function getCrateType(string $type)
	{
		return isset($this->crates[$type]) ? $this->crates[$type] : false;
	}


	public function getCrateBlock(string $type)
	{
		return !$this->getCrateDrops($type) ? null : $this->crateBlocks[$type];
	}


	public function getCrateDrops(string $type)
	{
		return !$this->getCrateType($type) ? null : $this->crateDrops[$type];
	}


	public function isCrateBlock(int $id , int $meta)
	{
		return isset($this->crateBlocks[$id . ":" . $meta]) ? $this->crateBlocks[$id . ":" . $meta] : false;
	}


	public function isCrateKey(Item $item)
	{
		$values = explode(":" , $this->key);

		return ($values[0] == $item->getId() && $values[1] == $item->getDamage() && !is_null($keytype = $item->getNamedTagEntry("KeyType"))) ? $keytype->getValue() : false;
	}


	public function giveKey(Player $player , string $type , int $amount)
	{
		if (is_null($this->getCrateDrops($type))) {

			return false;
		}
		$keyID = $this->getConfig()->get("key");
		$key = Item::fromString($keyID);
		$key->setCount($amount);
		$key->setLore([$this->getConfig()->get("descOne") , $this->getConfig()->get("descTwo")]);
		$key->addEnchantment(new EnchantmentInstance(new Enchantment(255 , "" , Enchantment::RARITY_COMMON , Enchantment::SLOT_ALL , Enchantment::SLOT_NONE , 1)));
		$key->setCustomName(ucfirst($type . " Key"));
		$key->setNamedTagEntry(new StringTag("KeyType" , $type));
		$player->getInventory()->addItem($key);

		return true;
	}
}
