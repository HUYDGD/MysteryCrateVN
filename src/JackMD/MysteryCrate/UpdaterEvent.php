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

use JackMD\MysteryCrate\Task\PutChest;
use JackMD\MysteryCrate\Task\RemoveChest;
use PiggyCustomEnchants\CustomEnchants\CustomEnchants;
use PiggyCustomEnchants\Main as CE;
use pocketmine\block\Chest as ChestBlock;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\Item;
use pocketmine\level\sound\ClickSound;
use pocketmine\Player;
use pocketmine\scheduler\PluginTask;
use pocketmine\Server;
use pocketmine\tile\Chest as ChestTile;
use pocketmine\utils\TextFormat;

class UpdaterEvent extends PluginTask
{
	private $cmd;

	public $canTakeItem = false;
	public $t_delay = 2 * 20;
	public $scheduler, $main, $level, $plugin, $item, $player, $chest;


	public $block;


	public function __construct(Main $plugin)
	{
		parent::__construct($plugin);
		$this->plugin = $plugin;
	}

	public function onRun(int $timer)
	{
		$t_delay = $this->getTDelay();
		if ($this->chest instanceof ChestTile != null) {
			$this->setTDelay(--$t_delay);
			if ($t_delay >= 0) {
				if ($this->chest instanceof ChestTile) {

					$i = 0;
					while ($i < 27) {
						if ($i != 4 && $i != 10 && $i != 11 && $i != 12 && $i != 13 && $i != 14 && $i != 15 && $i != 16 && $i != 22) {
							$this->setItemINT($i , 106 , 1);
						}
						$i++;
					}

					$this->setItemINT(4 , 208 , 1);
					$this->setItemINT(22 , 208 , 1);

					$block = $this->block;
					$block->getLevel()->addSound(new ClickSound($block) , [$this->player]);

					$b = $block->getLevel()->getBlock($block->subtract(0 , 1));
					$type = $this->plugin->isCrateBlock($b->getId() , $b->getDamage());
					$drops = array_rand($this->plugin->getCrateDrops($type) , 1);
					if (!is_array($drops)) {
						$drops = [$drops];
					}

					foreach ($drops as $drop) {
						$values = $this->plugin->getCrateDrops($type)[$drop];;
						$i = Item::get(($values["id"]) , $values["meta"] , $values["amount"]);
						$i->setCustomName($values["name"]);

						if (isset($values["enchantments"])) {
							foreach ($values["enchantments"] as $enchantment => $enchantmentinfo) {
								$level = $enchantmentinfo["level"];
								$ce = $this->plugin->getServer()->getPluginManager()->getPlugin("PiggyCustomEnchants");
								if (!is_null($ce) && !is_null($enchant = CustomEnchants::getEnchantmentByName($enchantment))) {
									if($ce instanceof CE) {
										$i = $ce->addEnchantment($i , $enchantment , $level);
									}
								} else {
									if (!is_null($enchant = Enchantment::getEnchantmentByName($enchantment))) {
										$i->addEnchantment(new EnchantmentInstance($enchant, $level));
									}
								}
							}
						}

						if (isset($values["lore"])) {
							$i->setLore([$values["lore"]]);
						}

						$player = $this->player;
						if (isset($values["command"])) {
							$cmd = $values["command"];
							$cmd = str_replace(["%PLAYER%"], [$player->getName()], $cmd);
							$this->cmd = $cmd;
						}

						$cInv = $this->chest->getInventory();

						$this->setItem(10 , $cInv->getItem(11) , $cInv->getItem(11)->getCount() , $cInv->getItem(11)->getDamage());
						$this->setItem(11 , $cInv->getItem(12) , $cInv->getItem(12)->getCount() , $cInv->getItem(12)->getDamage());
						$this->setItem(12 , $cInv->getItem(13) , $cInv->getItem(13)->getCount() , $cInv->getItem(13)->getDamage());
						$this->setItem(13 , $cInv->getItem(14) , $cInv->getItem(14)->getCount() , $cInv->getItem(14)->getDamage());//reward
						$this->setItem(14 , $cInv->getItem(15) , $cInv->getItem(15)->getCount() , $cInv->getItem(15)->getDamage());
						$this->setItem(15 , $cInv->getItem(16) , $cInv->getItem(16)->getCount() , $cInv->getItem(16)->getDamage());
						$this->setItem(16 , $i , $i->getCount() , $i->getDamage());
					}
				}
			}

			if ($t_delay == -1) {
				if ($this->chest instanceof ChestTile) {

					$this->setItemINT(10 , 0 , 0);
					$this->setItemINT(11 , 0 , 0);
					$this->setItemINT(12 , 0 , 0);
					$this->setItemINT(14 , 0 , 0);
					$this->setItemINT(15 , 0 , 1);
					$this->setItemINT(16 , 0 , 1);

					$this->setCanTakeItem(true);
					$slot13 = $this->chest->getInventory()->getItem(13);

					$block = $this->block;
					$b = $block->getLevel()->getBlock($block->subtract(0 , 1));
					$type = $this->plugin->isCrateBlock($b->getId() , $b->getDamage());

					if ($this->player instanceof Player) {
						if ($slot13->getDamage() === $this->plugin->getConfig()->get("commandMeta")){
							Server::getInstance()->dispatchCommand(new ConsoleCommandSender(), $this->cmd);
						}else{
							$this->player->getInventory()->addItem($slot13);
						}
						$this->player->sendMessage(TextFormat::GREEN . "Bạn vừa nhận được " . TextFormat::YELLOW . $slot13->getName() . TextFormat::LIGHT_PURPLE . " (x" . $slot13->getCount() . ")" . TextFormat::GREEN . " từ " . TextFormat::GOLD . ucfirst($type) . TextFormat::GREEN . " Crate. Xin chúc mừng bạn !");
					}

					$cpos = $block;
					$dmg = $block->getDamage();

					$this->plugin->getServer()->getScheduler()->scheduleDelayedTask(new RemoveChest($this->plugin , $cpos) , 20);
					$this->plugin->getServer()->getScheduler()->scheduleDelayedTask(new PutChest($this->plugin , $cpos , $dmg) , 24);

					$this->plugin->getServer()->getScheduler()->cancelTask($this->getTaskId());
				}
			}
		}
	}


	public function setItemINT($index , int $id , $count , $dmg = 0)
	{
		$item = Item::get($id);
		$item->setCount($count);
		$item->setDamage($dmg);

		if ($this->chest instanceof ChestTile) {
			$this->chest->getInventory()->setItem($index , $item);
		}
	}


	public function setItem($index , Item $item , $count , $dmg = 0)
	{
		$item->setCount($count);
		$item->setDamage($dmg);

		if ($this->chest instanceof ChestTile) {
			$this->chest->getInventory()->setItem($index , $item);
		}
	}


	public function isCanTakeItem() : bool
	{
		return $this->canTakeItem;
	}


	public function setCanTakeItem(bool $canTakeItem)
	{
		$this->canTakeItem = $canTakeItem;
	}


	public function getTDelay() : int
	{
		return $this->t_delay;
	}


	public function setTDelay(int $t_delay)
	{
		$this->t_delay = $t_delay;
	}
}