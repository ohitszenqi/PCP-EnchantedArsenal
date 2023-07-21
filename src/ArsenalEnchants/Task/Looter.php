<?php

namespace ArsenalEnchants\Task;

use ArsenalEnchants\Enchants\ELooter;
use ArsenalEnchants\Enchants\Looter as EnchantsLooter;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\scheduler\Task;
use pocketmine\item\Item;
use pocketmine\entity\object\ItemEntity;
use pocketmine\math\AxisAlignedBB;
use pocketmine\scheduler\TaskHandler;

class Looter extends Task {
    private $player;
    private ELooter $looter;
    public function __construct(Player $player) {
        $this->player = $player;
        $this->looter = new ELooter();
    }

   
    public function onRun() : void {
       
            $playerPosition = $this->player->getPosition();

            $level = $this->player->getArmorInventory()->getChestplate()->getEnchantmentLevel($this->looter);
            
            $rad = 5 + (1.5 * $level);
            $minX = $playerPosition->x - $rad;
            $minY = $playerPosition->y - $rad;
            $minZ = $playerPosition->z - $rad;
            $maxX = $playerPosition->x + $rad;
            $maxY = $playerPosition->y + $rad;
            $maxZ = $playerPosition->z + $rad;

            $bb = new AxisAlignedBB($minX, $minY, $minZ, $maxX, $maxY, $maxZ);
            $nearbyItems = $this->player->getWorld()->getNearbyEntities($bb);


            foreach ($nearbyItems as $item) {
                foreach($this->player->getArmorInventory()->getChestplate()->getEnchantments() as $enchants) {
                   $name = $enchants->getType()->getName();
                    if ($item->isAlive() && $name === "Looter") {
                        // Check if the item is a dropped item and not picked up by any player
                        if ($item instanceof ItemEntity && !$item->getOwner()) {
                            // Add the item to the player's inventory
                            $this->player->getInventory()->addItem($item->getItem());
                            // Destroy the dropped item entity
                            $item->flagForDespawn();
                        }
                    }
                }
            }   
        }
    
}
