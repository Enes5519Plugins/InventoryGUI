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
use pocketmine\inventory\BaseInventory;
use pocketmine\level\Level;
use pocketmine\level\Position;
use pocketmine\nbt\NetworkLittleEndianNBTStream;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\network\mcpe\protocol\BlockEntityDataPacket;
use pocketmine\network\mcpe\protocol\ContainerClosePacket;
use pocketmine\network\mcpe\protocol\ContainerOpenPacket;
use pocketmine\Player;
use pocketmine\tile\Nameable;
use pocketmine\tile\Tile;

abstract class FakeInventory extends BaseInventory{

	public const FAKE_BLOCK_HEIGHT = 4;

	/** @var NetworkLittleEndianNBTStream */
	protected static $nbtWriter;

	/** @var FakeInventoryEntry */
	protected $entry;
	/** @var CompoundTag */
	protected $nbt;
	/** @var bool */
	protected $readOnly = false;

	public function __construct(FakeInventoryEntry $entry){
		$this->entry = $entry;
		$this->nbt = new CompoundTag("", [
			new StringTag(Tile::TAG_ID, $this->entry->getTileId()),
			new IntTag(Tile::TAG_X, 0),
			new IntTag(Tile::TAG_Y, 0),
			new IntTag(Tile::TAG_Z, 0)
		]);

		parent::__construct();
	}

	abstract public function getWindowType() : int;

	public function getFakeEntry() : FakeInventoryEntry{
		return $this->entry;
	}

	public function getCustomName() : ?string{
		$customName = $this->nbt->getString(Nameable::TAG_CUSTOM_NAME, "");
		return $customName == "" ? null : $customName;
	}

	public function setCustomName(?string $customName) : self{
		if($customName == null){
			$this->nbt->removeTag(Nameable::TAG_CUSTOM_NAME);
		}else{
			$this->nbt->setString(Nameable::TAG_CUSTOM_NAME, $customName);
		}

		return $this;
	}

	public function onOpen(Player $player) : void{
		parent::onOpen($player);

		$this->getNBT($player); // update pos
		$pos = $this->getPositionFromNBT($player->level);
		$this->sendBlocks($player, $pos);
		$this->sendFakeTile($player, $pos);
		$this->sendFakeContainer($player, $pos);
	}

	public function onClose(Player $player) : void{
		parent::onClose($player);

		$pos = $this->getPositionFromNBT($player->level);
		$this->sendBlocks($player, $pos, false);

		$pk = new ContainerClosePacket();
		$pk->windowId = $player->getWindowId($this);
		$player->dataPacket($pk);
	}

	protected function sendBlocks(Player $player, Position $pos, bool $fake = true) : void{
		if($fake){
			$blocks = $this->getFakeBlocks($pos);
		}else{
			$blocks = $this->getRealBlocks($pos);
		}

		$player->level->sendBlocks([$player], $blocks);
	}

	public function sendFakeTile(Player $player, Position $pos) : void{
		$writer = self::$nbtWriter ?? (self::$nbtWriter = new NetworkLittleEndianNBTStream());

		$pk = new BlockEntityDataPacket();
		$pk->x = $pos->x;
		$pk->y = $pos->y;
		$pk->z = $pos->z;
		$pk->namedtag = $writer->write($this->nbt);
		$player->dataPacket($pk);
	}

	public function sendFakeContainer(Player $player, Position $pos) : void{
		$pk = new ContainerOpenPacket();
		$pk->windowId = $player->getWindowId($this);
		$pk->type = $this->getWindowType();
		$pk->x = $pos->x;
		$pk->y = $pos->y;
		$pk->z = $pos->z;
		$player->dataPacket($pk);

		$this->sendContents($player);
	}

	/**
	 * @param Player|null $player If this null not update nbt positions
	 * @return CompoundTag
	 */
	public function getNBT(?Player $player) : CompoundTag{
		if($player !== null){
			$this->nbt->setInt(Tile::TAG_X, $player->getFloorX());
			$this->nbt->setInt(Tile::TAG_Y, $player->getFloorY() + self::FAKE_BLOCK_HEIGHT);
			$this->nbt->setInt(Tile::TAG_Z, $player->getFloorZ());
		}

		return $this->nbt;
	}

	/**
	 * @param Level|null $level
	 *
	 * @return Position
	 */
	public function getPositionFromNBT(?Level $level) : Position{
		return new Position(
			$this->nbt->getInt(Tile::TAG_X),
			$this->nbt->getInt(Tile::TAG_Y),
			$this->nbt->getInt(Tile::TAG_Z),
			$level
		);
	}

	/**
	 * @param Position $pos
	 *
	 * @return Block[]
	 */
	public function getRealBlocks(Position $pos) : array{
		return [
			$pos->level->getBlockAt($pos->x, $pos->y, $pos->z)
		];
	}

	/**
	 * @param Position $pos
	 *
	 * @return Block[]
	 */
	public function getFakeBlocks(Position $pos) : array{
		return [
			$this->entry->getBlock()->setComponents($pos->x, $pos->y, $pos->z)
		];
	}

	public function isReadOnly() : bool{
		return $this->readOnly;
	}

	/**
	 * @param bool $readOnly
	 *
	 * @return FakeInventory
	 */
	public function setReadOnly(bool $readOnly = true) : self{
		$this->readOnly = $readOnly;

		return $this;
	}
}