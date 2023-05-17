<?php

namespace upgradepickaxe;

use onebone\economyapi\EconomyAPI;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\SingletonTrait;
use upgradepickaxe\command\UpgradePickaxeCommand;
use upgradepickaxe\listener\UpgradePickaxeListener;

final class UpgradePickaxe extends PluginBase {

    use SingletonTrait;

    /* @return void */
    protected function onLoad(): void {
        self::setInstance($this);
        $this->saveDefaultConfig();
    }

    /* @return void */
    protected function onEnable(): void {
        $this->getServer()->getCommandMap()->register("upgrade", new UpgradePickaxeCommand());

        $this->getServer()->getPluginManager()->registerEvents(new UpgradePickaxeListener(), $this);

        $this->getLogger()->notice($this->getConfig()->getNested("upgrade-pickaxe.message.enable-plugin"));
    }

    /* @return void */
    protected function onDisable(): void {
        $this->getLogger()->notice($this->getConfig()->getNested("upgrade-pickaxe.message.disable-plugin"));
    }

}
