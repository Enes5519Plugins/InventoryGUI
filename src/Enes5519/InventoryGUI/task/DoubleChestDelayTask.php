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

namespace Enes5519\InventoryGUI\task;

use Enes5519\InventoryGUI\inventory\DoubleChestInventory;
use pocketmine\scheduler\Task;

class DoubleChestDelayTask extends Task{

	/** @var DoubleChestInventory */
	protected $inventory;
	/** @var mixed[] */
	protected $args;

	public function __construct(DoubleChestInventory $inventory, ...$args){
		$this->args = $args;
		$this->inventory = $inventory;
	}

	public function onRun(int $currentTick){
		$this->inventory->sendFakeContainer(...$this->args);
	}

}