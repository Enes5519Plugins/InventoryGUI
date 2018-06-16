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

namespace Enes5519\InventoryGUI\event;

use Enes5519\InventoryGUI\inventory\FakeInventory;
use pocketmine\event\Cancellable;
use pocketmine\event\player\PlayerEvent;
use pocketmine\item\Item;
use pocketmine\Player;

class InventoryClickEvent extends PlayerEvent implements Cancellable{

	/** @var FakeInventory */
	protected $inventory;
	/** @var Item */
	protected $item;
	/** @var int */
	protected $slot;

	public function __construct(Player $player, FakeInventory $inventory, Item $item, int $slot){
		$this->player = $player;
		$this->inventory = $inventory;
		$this->item = $item;
		$this->slot = $slot;
	}

	/**
	 * @return FakeInventory
	 */
	public function getInventory() : FakeInventory{
		return $this->inventory;
	}

	/**
	 * @return Item
	 */
	public function getItem() : Item{
		return $this->item;
	}

	/**
	 * @return int
	 */
	public function getSlot() : int{
		return $this->slot;
	}

}