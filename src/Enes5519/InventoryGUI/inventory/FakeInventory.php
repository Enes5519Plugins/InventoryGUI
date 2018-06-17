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
use pocketmine\math\Vector3;
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

		$tag = $this->getNBT($player);
		$pos = new Vector3(
			$tag->getInt(Tile::TAG_X),
			$tag->getInt(Tile::TAG_Y),
			$tag->getInt(Tile::TAG_Z)
		);

		$this->sendBlocks($player, $pos);
		$this->sendFakeTile($player, $tag, $pos);
		$this->sendFakeContainer($player, $pos);
	}

	public function onClose(Player $player) : void{
		parent::onClose($player);

		$tag = $this->getNBT($player);
		$pos = new Vector3(
			$tag->getInt(Tile::TAG_X),
			$tag->getInt(Tile::TAG_Y),
			$tag->getInt(Tile::TAG_Z)
		);
		$this->sendBlocks($player, $pos, false);

		$pk = new ContainerClosePacket();
		$pk->windowId = $player->getWindowId($this);
		$player->dataPacket($pk);
	}

	protected function sendBlocks(Player $player, Vector3 $pos, bool $fake = true) : void{
		if($fake){
			$blocks = $this->getFakeBlocks($pos);
		}else{
			$blocks = $this->getRealBlocks($player, $pos);
		}

		$player->level->sendBlocks([$player], $blocks);
	}

	public function sendFakeTile(Player $player, CompoundTag $tag, Vector3 $pos) : void{
		$writer = self::$nbtWriter ?? (self::$nbtWriter = new NetworkLittleEndianNBTStream());

		$pk = new BlockEntityDataPacket();
		$pk->x = $pos->x;
		$pk->y = $pos->y;
		$pk->z = $pos->z;
		$pk->namedtag = $writer->write($tag);
		$player->dataPacket($pk);
	}

	public function sendFakeContainer(Player $player, Vector3 $pos) : void{
		$pk = new ContainerOpenPacket();
		$pk->windowId = $player->getWindowId($this);
		$pk->type = $this->getWindowType();
		$pk->x = $pos->x;
		$pk->y = $pos->y;
		$pk->z = $pos->z;
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
	 * @param Vector3 $pos
	 *
	 * @return Block[]
	 */
	public function getRealBlocks(Player $player, Vector3 $pos) : array{
		return [
			$player->level->getBlockAt($pos->x, $pos->y, $pos->z)
		];
	}

	/**
	 * @param Vector3 $pos
	 * @return Block[]
	 */
	public function getFakeBlocks(Vector3 $pos) : array{
		return [
			$this->entry->getBlock()->setComponents($pos->x, $pos->y, $pos->z)
		];
	}
}