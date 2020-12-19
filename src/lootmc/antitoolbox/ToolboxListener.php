<?php
declare(strict_types=1);

namespace lootmc\antitoolbox;

use pocketmine\event\Listener;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\event\player\PlayerQuitEvent;

use pocketmine\network\mcpe\protocol\LoginPacket;

use pocketmine\Player;

use lootmc\antitoolbox\event\ToolboxDetectedEvent;

class ToolboxListener implements Listener {

	/** @var AntiToolbox */
	private $plugin;

	/** @var array */
	private $detected = [];

	public function __construct(AntiToolbox $plugin) {
		$this->plugin = $plugin;
	}

	public function onPacketReceived(DataPacketReceiveEvent $ev) {
		if (!$ev->getPacket() instanceof LoginPacket) {
			return;
		}

		if (AntiToolbox::testToolbox($ev->getPacket()->clientData)) {
			$this->detected[spl_object_hash($ev->getPlayer())] = true;
		}
	}

	/**
	 * @priority LOW
	 */
	public function onLogin(PlayerLoginEvent $ev) {
		$hash = spl_object_hash($ev->getPlayer());
		if (isset($this->detected[$hash])) {
			unset($this->detected[$hash]);

			$event = new ToolboxDetectedEvent($ev->getPlayer());
			$event->call();
			if (!$event->isCancelled()) {
				$this->plugin->applyDefaultAction($ev->getPlayer());
			}
		}
	}

	public function onQuit(PlayerQuitEvent $ev) {
		unset($this->detected[spl_object_hash($ev->getPlayer())]);
	}

}