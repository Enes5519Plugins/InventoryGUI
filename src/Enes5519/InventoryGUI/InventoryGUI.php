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

namespace Enes5519\InventoryGUI;

use Enes5519\InventoryGUI\event\InventoryClickEvent;
use Enes5519\InventoryGUI\inventory\FakeInventory;
use pocketmine\event\inventory\InventoryTransactionEvent;
use pocketmine\event\Listener;
use pocketmine\inventory\PlayerCursorInventory;
use pocketmine\inventory\PlayerInventory;
use pocketmine\inventory\transaction\action\SlotChangeAction;
use pocketmine\plugin\PluginBase;

class InventoryGUI extends PluginBase implements Listener{

	/** @var InventoryGUI */
	private static $api;

	public function onLoad(){
		self::$api = $this;
	}

	public function onEnable(){
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
	}

	public function onTransaction(InventoryTransactionEvent $event){
		$actions = $event->getTransaction()->getActions();

		$invAction = $player = null;

		foreach($actions as $action){
			if($action instanceof SlotChangeAction){
				$inv = $action->getInventory();
				if($inv instanceof FakeInventory){
					$invAction = $action;
				}elseif($inv instanceof PlayerInventory or $inv instanceof PlayerCursorInventory){
					$player = $inv->getHolder();
				}
			}
		}

		if($invAction !== null && $player !== null){
			/** @var FakeInventory $inv */
			$inv = $invAction->getInventory();
			$player->getServer()->getPluginManager()->callEvent($ev = new InventoryClickEvent($player, $inv, $invAction->getSourceItem(), $invAction->getSlot()));
			$event->setCancelled($ev->isCancelled());
		}
	}

	/**
	 * @return InventoryGUI
	 */
	public static function getAPI() : InventoryGUI{
		return self::$api;
	}
}