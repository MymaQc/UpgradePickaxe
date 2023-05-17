<?php

namespace upgradepickaxe\command;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\Config;
use upgradepickaxe\manager\UpgradePickaxeManager;
use upgradepickaxe\UpgradePickaxe;
use upgradepickaxe\util\UpgradePickaxeIds;

final class UpgradePickaxeCommand extends Command {

    /* @var Config */
    public Config $config;

    /* CONSTRUCT */
    public function __construct() {
        $this->config = UpgradePickaxe::getInstance()->getConfig();
        parent::__construct(
            UpgradePickaxeManager::getInstance()->getCommandName() ?? "upgrade",
            UpgradePickaxeManager::getInstance()->getCommandDescription() ?? "Améliorer le niveau de sa pioche",
            UpgradePickaxeManager::getInstance()->getCommandUsage() ?? null,
            UpgradePickaxeManager::getInstance()->getCommandAliases() ?? []
        );
    }

    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     * @return void
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args): void {
        if ($sender instanceof Player) {
            $item = $sender->getInventory()->getItemInHand();
            if (!is_null(UpgradePickaxeManager::getInstance()->getPriceToUpgrade($item))) {
                $sender->sendForm(UpgradePickaxeManager::getInstance()->sendUpgradeUi($item));
            } else if ($item->getId() === UpgradePickaxeIds::NETHERITE_PICKAXE) {
                $sender->sendMessage($this->config->getNested("upgrade-pickaxe.message.already-maxed"));
            } else {
                $sender->sendMessage($this->config->getNested("upgrade-pickaxe.message.not-valid-item"));
            }
        } else {
            $sender->sendMessage($this->config->getNested("upgrade-pickaxe.message.no-console"));
        }
    }

}
