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

use pocketmine\block\Block;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\entity\EntityLevelChangeEvent;
use pocketmine\event\inventory\InventoryCloseEvent;
use pocketmine\event\inventory\InventoryTransactionEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\inventory\ChestInventory;
use pocketmine\inventory\transaction\action\SlotChangeAction;
use pocketmine\level\particle\FloatingTextParticle;
use pocketmine\level\particle\LavaParticle;
use pocketmine\math\Vector3;
use pocketmine\Server;
use pocketmine\tile\Chest as ChestTile;
use pocketmine\utils\TextFormat;

class EventListener implements Listener
{
	public $plugin;


	public function __construct(Main $plugin)
	{
		$this->plugin = $plugin;
	}


	public function onBreak(BlockBreakEvent $event)
	{
		$player = $event->getPlayer();
		$block = $event->getBlock();

		if (!$player->isOp()) {
			if ($this->plugin->isCrateBlock($block->getId() , $block->getDamage())) {
				if ($block->getLevel()->getBlock($block->add(0 , 1))->getId() == Block::CHEST) {
					if (!$player->hasPermission("mc.crates.destroy")) {
						$player->sendMessage(TextFormat::RED . "Bạn không có quyền để phá hủy Crate.");
						$event->setCancelled();
					}
				}
			} elseif ($block->getId() == Block::CHEST) {
				$b = $block->getLevel()->getBlock($block->subtract(0 , 1));
				if ($this->plugin->isCrateBlock($b->getId() , $b->getDamage())) {
					if (!$player->hasPermission("mc.crates.destroy")) {
						$player->sendMessage(TextFormat::RED . "Bạn không có quyền để phá hủy Crate.");
						$event->setCancelled();
					}
				}
			}
		}

		if ($block->getId() == Block::CHEST) {
			$b = $block->getLevel()->getBlock($block->subtract(0 , 1));
			if ($type = $this->plugin->isCrateBlock($b->getId() , $b->getDamage())) {
				$cfg = $this->plugin->blocks;
				if (!empty($cfg->get($type))) {
					$cfg->remove($type);
					$cfg->remove("$type.x");
					$cfg->remove("$type.y");
					$cfg->remove("$type.z");
					$cfg->save();
					$player->sendMessage(TextFormat::DARK_GREEN . "Phá hủy Crate thành công !. Hãy reload lại server để xóa Floating Text !");
				}
			}
		}
	}


	public function onPlace(BlockPlaceEvent $event)
	{
		$player = $event->getPlayer();
		$block = $event->getBlock();

		if (!$player->isOp()) {
			if ($this->plugin->isCrateBlock($block->getId() , $block->getDamage())) {
				if ($block->getLevel()->getBlock($block->add(0 , 1))->getId() == Block::CHEST) {
					if (!$player->hasPermission("mc.crates.create")) {
						$player->sendMessage(TextFormat::RED . "Bạn không có quyền để tạo Crate.");
						$event->setCancelled();
					}
				}
			} elseif ($block->getId() == Block::CHEST) {
				$b = $block->getLevel()->getBlock($block->subtract(0 , 1));
				if ($this->plugin->isCrateBlock($b->getId() , $b->getDamage())) {
					if (!$player->hasPermission("mc.crates.create")) {
						$player->sendMessage(TextFormat::RED . "Bạn không có quyền để tạo Crate.");
						$event->setCancelled();
					}
				}
			}
		}

		if ($block->getId() == Block::CHEST) {
			$b = $block->getLevel()->getBlock($block->subtract(0 , 1));
			if ($type = $this->plugin->isCrateBlock($b->getId() , $b->getDamage())) {
				$x = $block->getX();
				$y = $block->getY();
				$z = $block->getZ();
				$cfg = $this->plugin->blocks;
				if (empty($cfg->get($type))) {
					$cfg->set($type , TextFormat::GOLD . ucfirst($type) . " " . TextFormat::GREEN . "Crate");
					$cfg->set("$type.x" , $x);
					$cfg->set("$type.y" , $y);
					$cfg->set("$type.z" , $z);
					$cfg->save();
					$player->sendMessage(TextFormat::DARK_GREEN . "Đặt Crate thành công !. Hãy reload server để Floating Text xuất hiện !");
				}
			}
		}
	}


	public function onInteract(PlayerInteractEvent $event)
	{
		$levelName = $this->plugin->getConfig()->get("crateWorld");
		$lev = Server::getInstance()->getLevelByName($levelName);
		$player = $event->getPlayer();
		$block = $event->getBlock();
		$b = $block->getLevel()->getBlock($block->subtract(0 , 1));
		$item = $event->getItem();

		if ($player->getLevel() === $lev) {
			if ($block->getId() == Block::CHEST && ($type = $this->plugin->isCrateBlock($b->getId() , $b->getDamage())) !== false) {
				$this->plugin->task->block = $block;
				if (!$player->hasPermission("mc.crates.use")) {
					$event->setCancelled();
					$player->sendMessage(TextFormat::RED . "Bạn không có quyền để sử dụng Crate.");
				} else {
					if (!($keytype = $this->plugin->isCrateKey($item)) || $keytype !== $type) {
						$event->setCancelled();
						$player->sendMessage(TextFormat::RED . "Bạn cần một " . ucfirst($type) . " Key để mở Crate này !");
					} else {

						$event->setCancelled(false);
						if ($this->plugin->isNotInUse()) {
							$this->plugin->setNotInUse(false);
							$chest = $event->getPlayer()->getLevel()->getTile(new Vector3($event->getBlock()->getX() , $event->getBlock()->getY() , $event->getBlock()->getZ()));;

							if ($chest instanceof ChestTile) {
								$chest->getInventory()->clearAll();
								$this->plugin->task->chest = $chest;
								$this->plugin->task->player = $player;
								$this->plugin->task->setTDelay($this->plugin->getConfig()->get("tickDelay") * 20);
								$item = $player->getInventory()->getItemInHand();
								$item->setCount($item->getCount() - 1);
								$item->setDamage($item->getDamage());
								$event->getPlayer()->getInventory()->setItemInHand($item);
								$this->plugin->task->scheduler = $this->plugin->getServer()->getScheduler();
								$this->plugin->getServer()->getScheduler()->scheduleRepeatingTask($this->plugin->task , 5);


								$cx = $block->getX() + 0.5;
								$cy = $block->getY() + 1.2;
								$cz = $block->getZ() + 0.5;
								$radius = (int) 1;
								for ($i = 0 ; $i < 361 ; $i += 1.1) {
									$x = $cx + ($radius * cos($i));
									$z = $cz + ($radius * sin($i));
									$pos = new Vector3($x , $cy , $z);
									$block->level->addParticle(new LavaParticle($pos));
								}
							}
						} else {
							$event->setCancelled();
							$player->sendMessage(TextFormat::RED . "Đang quay Crate. Đợi một chút nhé...");
						}
					}
				}
			}
		}
	}


	public function onTransaction(InventoryTransactionEvent $event)
	{
		$levelName = $this->plugin->getConfig()->get("crateWorld");
		$lev = Server::getInstance()->getLevelByName($levelName);
		$player = $event->getTransaction()->getSource();
		if ($player->getLevel() === $lev) {
			foreach ($event->getTransaction()->getActions() as $action) {
				if ($action instanceof SlotChangeAction) {
					$cInv = $action->getInventory();
					if ($cInv instanceof ChestInventory) {
						$pos = $cInv->getHolder();
						$block = $lev->getBlock($pos);
						if ($block->getId() == Block::CHEST) {
							$b = $block->getLevel()->getBlock($block->subtract(0 , 1));
							if ($this->plugin->isCrateBlock($b->getId() , $b->getDamage())) {
								$event->setCancelled(true);
							}
						}
					}
				}
			}
		}
	}


	public function onInventoryClose(InventoryCloseEvent $event)
	{
		$levelName = $this->plugin->getConfig()->get("crateWorld");
		$lev = Server::getInstance()->getLevelByName($levelName);
		$che = $event->getInventory();
		$player = $event->getPlayer();
		if ($player->getLevel() === $lev) {
			if ($che instanceof ChestInventory) {
				$pos = $che->getHolder();
				$block = $lev->getBlock($pos);
				if ($block->getId() == Block::CHEST) {
					$b = $block->getLevel()->getBlock($block->subtract(0 , 1));
					if ($this->plugin->isCrateBlock($b->getId() , $b->getDamage())) {
						$this->plugin->setNotInUse(true);
						$this->plugin->getServer()->getScheduler()->cancelTask($this->plugin->task->getTaskId());
					}
				}
			}
		}
	}


	public function PlayerJoinEvent(PlayerJoinEvent $event)
	{
		$lev = $event->getPlayer()->getLevel();
		$crateLevel = $this->plugin->getConfig()->get("crateWorld");
		if (!empty($this->plugin->textParticles)) {
			$particles = array_values($this->plugin->textParticles);
			if ($lev->getFolderName() == $crateLevel) {
				foreach ($particles as $particle) {
					$lev->addParticle($particle , [$event->getPlayer()]);
				}
			}
		}
	}


	public function onLevelChange(EntityLevelChangeEvent $event)
	{
		$targetLevel = $event->getTarget();
		$crateLevel = $this->plugin->getConfig()->get("crateWorld");
		if (!empty($this->plugin->textParticles)) {
			$particles = array_values($this->plugin->textParticles);
			foreach ($particles as $particle) {
				if ($particle instanceof FloatingTextParticle) {
					if ($targetLevel->getFolderName() == $crateLevel) {
						$particle->setInvisible(false);
						$lev = $event->getTarget();
						$lev->addParticle($particle , [$event->getEntity()]);
					} else {
						$particle->setInvisible(true);
						$lev = $event->getOrigin();
						$lev->addParticle($particle , [$event->getEntity()]);
					}
				}
			}
		}
	}
}
