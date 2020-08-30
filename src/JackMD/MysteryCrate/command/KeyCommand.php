<?php
declare(strict_types=1);

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

namespace JackMD\MysteryCrate\command;

use JackMD\MysteryCrate\Main;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginCommand;
use pocketmine\Player;
use pocketmine\plugin\Plugin;
use pocketmine\utils\TextFormat;

class KeyCommand extends PluginCommand{

    /**
     * KeyCommand constructor.
     *
     * @param Main $plugin
     */
    public function __construct(Main $plugin){
        parent::__construct("key", $plugin);
        $this->setDescription("Cung cấp chìa khóa thùng cho người chơi.");
        $this->setUsage("/key [loại] [người chơi] [số lượng]");
        $this->setPermission("mc.command.key");
    }

    /**
     * @return Plugin|Main
     */
    public function getPlugin(): Plugin{
        return parent::getPlugin();
    }

    /**
     * @param CommandSender $sender
     * @param string        $commandLabel
     * @param array         $args
     * @return bool|mixed
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args){
        if(!$this->testPermission($sender) or $this->checkArgs($args, $sender)){
            return true;
        }

        $plugin = $this->getPlugin();
        $target = isset($args[1]) ? $plugin->getServer()->getPlayer($args[1]) : $sender;

        if($target instanceof Player){
            $keyAmount = (isset($args[2]) and is_numeric($args[2])) ? (int) $args[2] : 1;
            $lowercaseCrateType = strtolower($args[0]);
            $plugin->giveKey($target, $lowercaseCrateType, $keyAmount);
            $sender->sendMessage(TextFormat::GREEN . "Chìa khóa thùng " . ucfirst($lowercaseCrateType) . " đã được gửi.");
        }else{
            $sender->sendMessage(TextFormat::RED . "Vui lòng chỉ định một người chơi hợp lệ.");
        }

        return true;
    }

    /**
     * Trả về true khi args không hợp lệ, false khi mọi thứ đều ổn.
     *
     * @param array $args
     * @param CommandSender $sender
     * @return bool
     */
    private function checkArgs(array $args, CommandSender $sender): bool{
        if(!isset($args[0])){
            $sender->sendMessage(TextFormat::RED . "Sử dụng: /key [loại] [người chơi] [số lượng]");
        }elseif(!$this->getPlugin()->getCrateType(strtolower($args[0]))){
            $sender->sendMessage(TextFormat::RED . "Thùng không hợp lệ.");
        }else{
            return false;
        }
        return true;
    }
}