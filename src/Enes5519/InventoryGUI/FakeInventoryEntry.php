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

use pocketmine\block\Block;
use pocketmine\block\BlockFactory;

class FakeInventoryEntry{

	/** @var int */
	protected $blockId, $blockData;
	/** @var Block */
	protected $block;
	/** @var string */
	protected $tileId;

	public function __construct(int $id, int $data = 0, string $tileId){
		$this->blockId = $id;
		$this->blockData = $data;
		$this->block = BlockFactory::get($this->blockId, $this->blockData);
		$this->tileId = $tileId;
	}

	/**
	 * @return Block
	 */
	public function getBlock() : Block{
		return clone $this->block;
	}

	/**
	 * @return int
	 */
	public function getBlockId() : int{
		return $this->blockId;
	}

	/**
	 * @return int
	 */
	public function getBlockData() : int{
		return $this->blockData;
	}

	/**
	 * @return string
	 */
	public function getTileId() : string{
		return $this->tileId;
	}

}