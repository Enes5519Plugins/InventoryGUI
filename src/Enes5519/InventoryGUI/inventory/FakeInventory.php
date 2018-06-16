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
	/** @var string */
	protected $customName = null;

	public function __construct(FakeInventoryEntry $entry){
		$this->entry = $entry;

		parent::__construct();
	}

	abstract public function getWindowType() : int;

	public function getCustomName() : string{
		return $this->customName;
	}

	public function setCustomName(?string $customName) : self{
		$this->customName = $customName;

		return $this;
	}

	public function getFakeEntry() : FakeInventoryEntry{
		return $this->entry;
	}

	public function onOpen(Player $player) : void{
		parent::onOpen($player);

		$nbt = $this->getNBT($player);
		$this->sendBlocks($player, $nbt);
		$this->sendFakeTile($player, $nbt);
		$this->sendFakeContainer($player, $nbt);
	}

	public function onClose(Player $player) : void{
		parent::onClose($player);

		$nbt = $this->getNBT($player);
		$this->sendBlocks($player, $nbt, false);

		$pk = new ContainerClosePacket();
		$pk->windowId = $player->getWindowId($this);
		$player->dataPacket($pk);
	}

	protected function sendBlocks(Player $player, CompoundTag $tag, bool $fake = true) : void{
		$x = $tag->getInt(Tile::TAG_X);
		$y = $tag->getInt(Tile::TAG_Y);
		$z = $tag->getInt(Tile::TAG_Z);
		if($fake){
			$blocks = $this->getFakeBlocks($x, $y, $z);
		}else{
			$blocks = $this->getRealBlocks($player, $x, $y, $z);
		}

		$player->level->sendBlocks([$player], $blocks);
	}

	public function sendFakeTile(Player $player, CompoundTag $tag) : void{
		$pk = new BlockEntityDataPacket();
		$pk->x = $tag->getInt(Tile::TAG_X);
		$pk->y = $tag->getInt(Tile::TAG_Y);
		$pk->z = $tag->getInt(Tile::TAG_Z);

		$writer = self::$nbtWriter ?? (self::$nbtWriter = new NetworkLittleEndianNBTStream());
		$pk->namedtag = $writer->write($tag);
		$player->dataPacket($pk);
	}

	public function sendFakeContainer(Player $player, CompoundTag $tag) : void{
		$pk = new ContainerOpenPacket();
		$pk->windowId = $player->getWindowId($this);
		$pk->type = $this->getWindowType();
		$pk->x = $tag->getInt(Tile::TAG_X);
		$pk->y = $tag->getInt(Tile::TAG_Y);
		$pk->z = $tag->getInt(Tile::TAG_Z);
		$player->dataPacket($pk);

		$this->sendContents($player);
	}

	public function getNBT(Player $player) : CompoundTag{
		$tag = new CompoundTag("", [
			new StringTag(Tile::TAG_ID, $this->entry->getTileId()),
			new IntTag(Tile::TAG_X, (int) floor($player->x)),
			new IntTag(Tile::TAG_Y, (int) floor($player->y + self::FAKE_BLOCK_HEIGHT)),
			new IntTag(Tile::TAG_Z, (int) floor($player->z))
		]);
		if($this->customName !== null){
			$tag->setString(Nameable::TAG_CUSTOM_NAME, $this->customName);
		}

		return $tag;
	}

	/**
	 * @param Player $player
	 * @param int $x
	 * @param int $y
	 * @param int $z
	 *
	 * @return Block[]
	 */
	public function getRealBlocks(Player $player, int $x, int $y, int $z) : array{
		return [
			$player->level->getBlockAt($x, $y, $z)
		];
	}

	/**
	 * @param int $x
	 * @param int $y
	 * @param int $z
	 *
	 * @return Block[]
	 */
	public function getFakeBlocks(int $x, int $y, int $z) : array{
		return [
			$this->entry->getBlock()->setComponents($x, $y, $z)
		];
	}
}