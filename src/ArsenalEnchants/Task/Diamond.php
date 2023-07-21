<?php

namespace ArsenalEnchants\Task;

use ArsenalEnchants\Main;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use pocketmine\scheduler\Task;
use pocketmine\player\Player ;
use pocketmine\utils\TextFormat;
use pocketmine\world\Position;

class Diamond extends Task {
    private $player;
    private $main;
    private $blockIds = [56]; // Diamond Ore block ID

    public function __construct(Player $player, Main $main) {
        $this->player = $player;
        $this->main = $main;
    }

    public function isPickaxe(Item $item): bool {
        $chestplateIds = [
            ItemFactory::getInstance()->get(ItemIds::IRON_PICKAXE)->getId(),
            ItemFactory::getInstance()->get(ItemIds::GOLD_PICKAXE)->getId(),
            ItemFactory::getInstance()->get(ItemIds::DIAMOND_PICKAXE)->getId(),
            ItemFactory::getInstance()->get(ItemIds::WOODEN_PICKAXE)->getId(),
            ItemFactory::getInstance()->get(ItemIds::STONE_PICKAXE)->getId()
        ];

        if ($item->hasEnchantment($this->main->Prospector)) {
            return in_array($item->getId(), $chestplateIds);
        }

        return false;
    }

    public function onRun() : void {
        $playerPosition = $this->player->getPosition();
        if ($this->isPickaxe($this->player->getInventory()->getItemInHand())) {
                $lvl = $this->player->getInventory()->getItemInHand()->getEnchantmentLevel($this->main->Prospector) * 5;
                $nearbyDiamondOre = $this->findNearbyDiamondOre($playerPosition, $lvl);
                if ($nearbyDiamondOre !== null) {
                    
                    $message = "Diamond Ore nearby at X: " . $nearbyDiamondOre->getPosition()->getX() . ", Y: " . $nearbyDiamondOre->getPosition()->getY() . ", Z: " . $nearbyDiamondOre->getPosition()->getZ();
                $this->player->sendActionBarMessage($message);
                }
        }
    }

    private function findNearbyDiamondOre(Position $position, $radius) {
        $level = $position->getWorld();
        $x = $position->getX();
        $y = $position->getY();
        $z = $position->getZ();

        

        for ($dx = -$radius; $dx <= $radius; $dx++) {
            for ($dy = -$radius; $dy <= $radius; $dy++) {
                for ($dz = -$radius; $dz <= $radius; $dz++) {
                    $block = $level->getBlockAt($x + $dx, $y + $dy, $z + $dz);
                    if (in_array($block->getId(), $this->blockIds)) {
                        return $block;
                        
                    }
                }
            }
        }

        return null;
    }

}
