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

namespace JackMD\MysteryCrate\lang;

use JackMD\MysteryCrate\Main;
use pocketmine\utils\Config;

class Lang{

	/** @var string */
	public static $no_perm_destroy;
	/** @var string */
	public static $no_perm_create;
	/** @var string */
	public static $crate_destroy_successful;
	/** @var string */
	public static $crate_place_successful;
	/** @var string */
	public static $no_perm_use_crate;
	/** @var string */
	public static $no_key;
	/** @var string */
	public static $error_sneak;
	/** @var string */
	public static $error_crate_in_use;
	/** @var string */
	public static $win_message;

	/**
	 * @param Main $plugin
	 */
	public static function init(Main $plugin){
		$plugin->saveResource("lang.yml");
		$lang = new Config($plugin->getDataFolder() . "lang.yml", Config::YAML);
		self::loadMessages($lang);
	}

	/**
	 * @param Config $lang
	 */
	private static function loadMessages(Config $lang){
		self::$no_perm_destroy = $lang->get("no_perm_destroy");
		self::$no_perm_create = $lang->get("no_perm_create");
		self::$crate_destroy_successful = $lang->get("crate_destroy_successful");
		self::$crate_place_successful = $lang->get("crate_place_successful");
		self::$no_perm_use_crate = $lang->get("no_perm_use_crate");
		self::$no_key = $lang->get("no_key");
		self::$error_sneak = $lang->get("error_sneak");
		self::$error_crate_in_use = $lang->get("error_crate_in_use");
		self::$win_message = $lang->get("win_message");
	}
}
