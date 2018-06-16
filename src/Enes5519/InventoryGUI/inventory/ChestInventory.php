<?php

/*
 *      _____                      _                    _____ _    _ _____
 *     |_   _|                    | |                  / ____| |  | |_   _|
 *       | |  _ ____   _____ _ __ | |_ ___  _ __ _   _| |  __| |  | | | |
 *       | | | '_ \ \ / / _ \ '_ \| __/ _ \| '__| | | | | |_ | |  | | | |
 *      _| |_| | | \ V /  __/ | | | || (_) | |  | |_| | |__| | |__| |_| |_
 *     |_____|_| |_|\_/ \___|_| |_|\__\___/|_|   \__, |\_____|\____/|_____|
 *                                                __/ |
 *                                               |___/
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author Enes5519
 * @link http://github.com/Enes5519
 */

declare(strict_types=1);

namespace Enes5519\InventoryGUI\inventory;

use Enes5519\InventoryGUI\FakeInventoryEntry;
use pocketmine\block\Block;
use pocketmine\network\mcpe\protocol\types\WindowTypes;
use pocketmine\tile\Tile;

class ChestInventory extends FakeInventory{

	public function __construct(){
		parent::__construct(new FakeInventoryEntry(Block::CHEST, 0, Tile::CHEST));
	}

	public function getName() : string{
		return "Fake Chest";
	}

	public function getDefaultSize() : int{
		return 27;
	}

	public function getWindowType() : int{
		return WindowTypes::CONTAINER;
	}
}