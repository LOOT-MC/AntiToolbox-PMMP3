<?php
declare(strict_types=1);

namespace lootmc\antitoolbox;

use pocketmine\plugin\PluginBase;

use pocketmine\command\ConsoleCommandSender;

use pocketmine\Player;

use pocketmine\event\Listener;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\event\player\PlayerQuitEvent;

use pocketmine\network\mcpe\protocol\LoginPacket;

use pocketmine\scheduler\ClosureTask;

use lootmc\antitoolbox\event\ToolboxDetectedEvent;


class AntiToolbox extends PluginBase implements Listener {

	/** @var string[] */
	private $onToolboxCmds;

	/** @var array */
	private $detected;

	public function onEnable() {
		$config = $this->getConfig();
		$this->onToolboxCmds = $config->get("on-toolbox-cmds");

		$this->getServer()->getPluginManager()->registerEvents($this, $this);
	}

	public function applyDefaultAction(Player $player) {
		$sender = new ConsoleCommandSender();
		foreach ($this->onToolboxCmds as $cmd) {
			$this->getServer()->dispatchCommand($sender, str_replace("%player%", $player->getName(), $cmd));
		}
	}

	public function testToolbox(Player $player) {
		return isset($this->detected[spl_object_hash($player)]);
	}

	public function handlePacketReceived(DataPacketReceiveEvent $ev) {
		if ($ev->getPacket() instanceof LoginPacket) {
			$clientData = $ev->getPacket()->clientData;
			if ($clientData["DeviceOS"] === 1) { // is Android
				$model = explode(" ", $clientData["DeviceModel"], 1)[0];
				if ($model !== strtoupper($model)) {
					$this->detected[spl_object_hash($ev->getPlayer())] = true;
				}
			}
		}
	}

	/**
	 * @priority LOW
	 */
	public function handleLogin(PlayerLoginEvent $ev) {
		$player = $ev->getPlayer();
		if ($this->testToolbox($player)) {
			$this->getScheduler()->scheduleDelayedTask(new ClosureTask(function(int $tick) use ($player): void {
				if ($player->isConnected()) {
					$event = new ToolboxDetectedEvent($player);
					$event->call();
					if (!$event->isCancelled()) {
						$this->applyDefaultAction($player);
					}
				}
			}), 1);
		}
	}

	public function handleQuit(PlayerQuitEvent $ev) {
		unset($this->detected[spl_object_hash($ev->getPlayer())]);
	}

}