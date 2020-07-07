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

namespace JackMD\MysteryCrate\Commands;

use JackMD\MysteryCrate\Main;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginCommand;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class KeyCommand extends PluginCommand
{

    public function __construct(string $name, Main $plugin)
    {
        parent::__construct($name, $plugin);
        $this->setDescription("Đưa Key đến người chơi.");
        $this->setUsage("/key [tên Crate] [người chơi] [số lượng]");
        $this->setPermission("mc.command.key");
    }


    public function execute(CommandSender $sender, string $commandLabel, array $args)
    {
	if (!$this->testPermission($sender)) {
		return true;
	}
	
        $plugin = $this->getPlugin();
        if ($plugin instanceof Main) {
            if (!isset($args[0])) {
                $sender->sendMessage("Sử dụng: /key [tên Crate] [người chơi] [số lượng]");
                return true;
            }
            $target = $sender;
            $args[0] = strtolower($args[0]);
            if (isset($args[1])) {
                $target = $plugin->getServer()->getPlayer($args[1]);
                if (!$target instanceof Player) {
                    $sender->sendMessage(TextFormat::RED . "Người chơi không hợp lệ.");
                    return true;
                }
            } else {
                if (!$target instanceof Player) {
                    $sender->sendMessage(TextFormat::RED . "Vui lòng nhập tên người chơi !");
                    return true;
                }
            }
            if (!$plugin->getCrateType($args[0])) {
                $sender->sendMessage(TextFormat::RED . "Tên Crate không hợp lệ ! Hãy xem crate.yml lại nhé bạn !");
                return true;
            }
			if (isset($args[2]) and is_numeric($args[2])) {
				$amount = (int)$args[2];
			} else {
				$amount = (int)1;
			}
            $plugin->giveKey($target, $args[0], $amount);
            $sender->sendMessage(TextFormat::GREEN . ucfirst($args[0]) . " đã được chuyển về túi đồ của bạn ! Hãy check !");
            return true;
        }
        return true;
    }
}
