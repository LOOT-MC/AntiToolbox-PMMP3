<?php
declare(strict_types=1);

namespace lootmc\antitoolbox\event;

use pocketmine\event\Cancellable;
use pocketmine\event\player\cheat\PlayerCheatEvent;

use pocketmine\Player;

class ToolboxDetectedEvent extends PlayerCheatEvent implements Cancellable {

	public function __construct(Player $player) {
		$this->player = $player;
	}

}