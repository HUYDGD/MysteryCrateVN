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

use JackMD\MysteryCrate\lang\Lang;
use pocketmine\block\Block;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\entity\EntityLevelChangeEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\level\particle\FloatingTextParticle;
use pocketmine\level\particle\LavaParticle;
use pocketmine\math\Vector3;
use pocketmine\utils\TextFormat;

class EventListener implements Listener{

	/** @var Main */
	private $plugin;

	/** @var array */
	private const CRATE_BLOCKS = [
		Block::CHEST,
		Block::ENDER_CHEST,
		Block::TRAPPED_CHEST
	];

	/**
	 * EventListener constructor.
	 *
	 * @param Main $plugin
	 */
	public function __construct(Main $plugin){
		$this->plugin = $plugin;
	}

	/**
	 * @param BlockBreakEvent $event
	 * @priority        HIGHEST
	 */
	public function onBreak(BlockBreakEvent $event){
		$player = $event->getPlayer();
		$block = $event->getBlock();
		$level = $this->plugin->getServer()->getLevelByName((string) $this->plugin->getConfig()->get("crateWorld"));
		if(!($player->hasPermission("mc.crates.destroy"))){
			if($this->plugin->isCrateBlock($block->getId(), $block->getDamage())){
				if(in_array($block->getLevel()->getBlock($block->add(0, 1))->getId(), self::CRATE_BLOCKS)){
					$player->sendMessage(Lang::$no_perm_destroy);
					$event->setCancelled();
				}
			}elseif(in_array($block->getId(), self::CRATE_BLOCKS)){
				$typeBlock = $block->getLevel()->getBlock($block->subtract(0, 1));
				if($this->plugin->isCrateBlock($typeBlock->getId(), $typeBlock->getDamage())){
					$player->sendMessage(Lang::$no_perm_destroy);
					$event->setCancelled();
				}
			}
		}else{
			if(in_array($block->getId(), self::CRATE_BLOCKS)){
				if($player->getLevel() === $level){
					$typeBlock = $block->getLevel()->getBlock($block->subtract(0, 1));
					if($type = $this->plugin->isCrateBlock($typeBlock->getId(), $typeBlock->getDamage())){
						$config = $this->plugin->getBlocksConfig();
						if(!empty($config->get($type))){
							$config->remove($type);
							$config->remove($type . ".x");
							$config->remove($type . ".y");
							$config->remove($type . ".z");
							$config->save();
							if(isset($this->plugin->getTextParticles()[$type])){
								unset($this->plugin->getTextParticles()[$type]);
								$this->plugin->initTextParticle();
							}
							$player->sendMessage(Lang::$crate_destroy_successful);
						}
					}
				}
			}
		}
	}

	/**
	 * @param BlockPlaceEvent $event
	 * @priority        HIGHEST
	 */
	public function onPlace(BlockPlaceEvent $event){
		$player = $event->getPlayer();
		$block = $event->getBlock();
		$level = $this->plugin->getServer()->getLevelByName((string) $this->plugin->getConfig()->get("crateWorld"));
		if(!($player->hasPermission("mc.crates.create"))){
			if($this->plugin->isCrateBlock($block->getId(), $block->getDamage())){
				if(in_array($block->getLevel()->getBlock($block->add(0, 1))->getId(), self::CRATE_BLOCKS)){
					$player->sendMessage(Lang::$no_perm_create);
					$event->setCancelled();
				}
			}elseif(in_array($block->getId(), self::CRATE_BLOCKS)){
				$typeBlock = $block->getLevel()->getBlock($block->subtract(0, 1));
				if($this->plugin->isCrateBlock($typeBlock->getId(), $typeBlock->getDamage())){
					$player->sendMessage(Lang::$no_perm_create);
					$event->setCancelled();
				}
			}
		}else{
			if(in_array($block->getId(), self::CRATE_BLOCKS)){
				if($player->getLevel() === $level){
					$typeBlock = $block->getLevel()->getBlock($block->subtract(0, 1));
					if($type = $this->plugin->isCrateBlock($typeBlock->getId(), $typeBlock->getDamage())){
						$x = $block->getX();
						$y = $block->getY();
						$z = $block->getZ();
						$config = $this->plugin->getBlocksConfig();
						if(empty($config->get($type))){
							$config->set($type, TextFormat::GOLD . ucfirst($type) . TextFormat::GREEN . " Crate");
							$config->set($type . ".x", $x);
							$config->set($type . ".y", $y);
							$config->set($type . ".z", $z);
							$config->save();
							$player->sendMessage(Lang::$crate_place_successful);
						}
					}
				}
			}
		}
	}

	/**
	 * @param PlayerInteractEvent $event
	 * @priority        HIGHEST
	 */
	public function onInteract(PlayerInteractEvent $event){
		$level = $this->plugin->getServer()->getLevelByName((string) $this->plugin->getConfig()->get("crateWorld"));
		$player = $event->getPlayer();
		$block = $event->getBlock();
		$typeBlock = $block->getLevel()->getBlock($block->subtract(0, 1));
		$item = $event->getItem();
		if($player->getLevel() === $level){
			if((in_array($block->getId(), self::CRATE_BLOCKS)) && ($type = $this->plugin->isCrateBlock($typeBlock->getId(), $typeBlock->getDamage())) !== false){
				$event->setCancelled();

				if(!$player->hasPermission("mc.crates.use")){
					$player->sendMessage(Lang::$no_perm_use_crate);

					return;
				}else{
					if($player->isSneaking()){
						$player->sendMessage(Lang::$error_sneak);

						return;
					}
					if(!($keytype = $this->plugin->isCrateKey($item)) || $keytype !== $type){
						$player->sendMessage(str_replace(["%TYPE%"], [ucfirst($type)], Lang::$no_key));

						return;
					}

					$t_delay = $this->plugin->getConfig()->get("tickDelay") * 20;

					$item = $player->getInventory()->getItemInHand();
					$item->setCount($item->getCount() - 1);
					$item->setDamage($item->getDamage());
					$player->getInventory()->setItemInHand($item);

					$this->plugin->getScheduler()->scheduleRepeatingTask(new UpdaterEvent($this->plugin, $player, $block, $t_delay), (int) $this->plugin->getConfig()->get("scrollSpeed"));

					if($this->plugin->isBroadcastEnabled($type)){
						$cmd = $this->plugin->getBroadcastMessage($type);
						$this->plugin->getServer()->dispatchCommand(new ConsoleCommandSender(), str_replace("%PLAYER%", $player->getName(), $cmd));
					}

					//Particle upon opening chest
					$cx = $block->getX() + 0.5;
					$cy = $block->getY() + 1.2;
					$cz = $block->getZ() + 0.5;
					$radius = (int) 1;
					for($i = 0; $i < 361; $i += 1.1){
						$x = $cx + ($radius * cos($i));
						$z = $cz + ($radius * sin($i));
						$pos = new Vector3($x, $cy, $z);
						$block->level->addParticle(new LavaParticle($pos));
					}
				}
			}
		}
	}

	/**
	 * @param EntityLevelChangeEvent $event
	 */
	public function onLevelChange(EntityLevelChangeEvent $event){
		$targetLevel = $event->getTarget();
		$crateLevel = $this->plugin->getConfig()->get("crateWorld");
		if(!empty($this->plugin->getTextParticles())){
			$particles = $this->plugin->getTextParticles();
			foreach($particles as $particle){
				if($particle instanceof FloatingTextParticle){
					if($targetLevel->getFolderName() === $crateLevel){
						$particle->setInvisible(false);
						$lev = $event->getTarget();
						$lev->addParticle($particle, [$event->getEntity()]);
					}else{
						$particle->setInvisible(true);
						$lev = $event->getOrigin();
						$lev->addParticle($particle, [$event->getEntity()]);
					}
				}
			}
		}
	}

	/**
	 * @param PlayerJoinEvent $event
	 */
	public function onJoin(PlayerJoinEvent $event){
		$this->plugin->addParticles($event->getPlayer());
	}
}
