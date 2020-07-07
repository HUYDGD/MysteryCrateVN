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

namespace JackMD\MysteryCrate\Particles;

use JackMD\MysteryCrate\Main;
use pocketmine\level\particle\ExplodeParticle;
use pocketmine\level\particle\WaterDripParticle;
use pocketmine\math\Vector3;
use pocketmine\scheduler\PluginTask;

class CloudRain extends PluginTask
{
    private $plugin, $pos;

    public function __construct(Main $plugin, Vector3 $pos)
    {
        parent::__construct($plugin);
        $this->plugin = $plugin;
        $this->pos = $pos;
    }


    public function onRun(int $tick)
    {
        $level = $this->plugin->getServer()->getLevelByName($this->plugin->getConfig()->get("crateWorld"));
        $cpos = $this->pos;

        $time = 1;
        $pi = 3.14159;
        $time = $time + 0.1 / $pi;
        for ($i = 0; $i <= 2 * $pi; $i += $pi / 8) {
            $x = $time * cos($i);
            $y = exp(-0.1 * $time) * sin($time) + 1.5;
            $z = $time * sin($i);
            $level->addParticle(new ExplodeParticle($cpos->add($x, $y, $z)));
            $level->addParticle(new WaterDripParticle($cpos->add($x, $y, $z)));

        }
    }
}
