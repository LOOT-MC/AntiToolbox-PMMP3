<?php
declare(strict_types=1);

namespace lootmc\antitoolbox;

use pocketmine\plugin\PluginBase;

use pocketmine\command\ConsoleCommandSender;

use pocketmine\Player;


class AntiToolbox extends PluginBase {

	/** @var string[] */
	private $onToolboxCmds;

	public function onEnable() {
		$config = $this->getConfig();
		$this->onToolboxCmds = $config->get("on-toolbox-cmds");

		$this->getServer()->getPluginManager()->registerEvents(new ToolboxListener($this), $this);
	}

	public function applyDefaultAction(Player $player) {
		$sender = new ConsoleCommandSender();
		foreach ($this->onToolboxCmds as $cmd) {
			$this->getServer()->dispatchCommand($sender, str_replace("%player%", $player->getName(), $cmd));
		}
	}

	public static function testToolbox(array $clientData) : bool {
		if ($clientData["DeviceOS"] === 1) { // is Android
			$modelSplit = explode(" ", $clientData["DeviceModel"]);
			return $modelSplit[0] !== strtoupper($modelSplit[0]);
		}
		return false;
	}

}