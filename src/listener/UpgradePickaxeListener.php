<?php

namespace upgradepickaxe\listener;

use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\Listener;
use upgradepickaxe\manager\UpgradePickaxeManager;
use upgradepickaxe\UpgradePickaxe;

final class UpgradePickaxeListener implements Listener {

    /**
     * @priority LOWEST
     * @param BlockBreakEvent $event
     * @return void
     */
    public function onBlockBreak(BlockBreakEvent $event): void {
        $player = $event->getPlayer();
        $block = $event->getBlock();
        $item = $event->getItem();
        $config = UpgradePickaxe::getInstance()->getConfig();
        if (!$player->isCreative()) {
            if (!UpgradePickaxeManager::getInstance()->hasNecessaryLevelToBreakOre($block, UpgradePickaxeManager::getInstance()->getPickaxeLevel($item))) {
                $player->sendMessage(str_replace(["{block}", "{level}"], [UpgradePickaxeManager::getInstance()->getFrenchBlockName($block), UpgradePickaxeManager::getInstance()->getPickaxeLevel($item)], $config->getNested("upgrade-pickaxe.message.cannot-break-block")));
                $event->cancel();
            }
        }
    }

}
