<?php

namespace ArsenalEnchants\Enchants;

use pocketmine\block\Air;
use pocketmine\block\Block;
use pocketmine\block\BlockFactory;
use pocketmine\block\VanillaBlocks;
use pocketmine\data\bedrock\EnchantmentIdMap;
use pocketmine\data\bedrock\EnchantmentIds;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\EnchantmentLevelTable;
use pocketmine\item\enchantment\EnchantmentEntry;
use pocketmine\item\enchantment\EnchantmentSlot;
use pocketmine\item\enchantment\ItemFlags;
use pocketmine\item\enchantment\Rarity;
use pocketmine\item\enchantment\StringToEnchantmentParser;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\item\VanillaItems;
use pocketmine\lang\KnownTranslationFactory;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\world\Explosion;
use pocketmine\world\Position;

class Nuke extends Enchantment {

    const ENCHANT_ID = 54;

    
    public function __construct() {
        parent::__construct("Nuke", Rarity::RARE, ItemFlags::PICKAXE, ItemFlags::PICKAXE, 5);
    }
    
    public function getDisplayName() : string {
        return "Nuke";
    }
    public function getIncompatibles() : array {
        return [EnchantmentIds::BANE_OF_ARTHROPODS, EnchantmentIds::SMITE, EnchantmentIds::FIRE_ASPECT, EnchantmentIds::LOOTING];
    }
    
    public function getDamageIncrease(int $level, int $dmg) : float {
        return $dmg + (2.5 * $level);
    }

    public function getMcpeId(): int {
       return self::ENCHANT_ID;
    }
    function breakSurroundingBlocks(int $enchantmentLevel, Block $center, Player $player): void {
        $blocks = [];
    
        $width = 3;
        $height = 3 + ($enchantmentLevel - 1) * 3;
    
        $minX = $center->getPosition()->getX() - 1;
        $maxX = $center->getPosition()->getX() + 1;
        $minY = $center->getPosition()->getY() - ($height - 1);
        $maxY = $center->getPosition()->getY();
        $minZ = $center->getPosition()->getZ() - ($width - 1) / 2;
        $maxZ = $center->getPosition()->getZ() + ($width - 1) / 2;
    
        for ($x = $minX; $x <= $maxX; ++$x) {
            for ($y = $minY; $y <= $maxY; ++$y) {
                for ($z = $minZ; $z <= $maxZ; ++$z) {
                    $block = $player->getWorld()->getBlock(new Vector3($x, $y, $z));
                    if (!$block instanceof Air) {
                        $blocks[] = $block;
                    }
                }
            }
        }
    
        foreach ($blocks as $block) {
            $block->onBreak(ItemFactory::getInstance()->get(0), $player);
        }
    }
    
    
    
    
    
    public function getLevel(Item $item): int {
        $level = $item->getEnchantmentLevel(EnchantmentIdMap::getInstance()->fromId(self::ENCHANT_ID));
        return $level !== null ? $level : 0;
    }
}
