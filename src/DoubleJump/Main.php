<?php

namespace DoubleJump;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJumpEvent;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerToggleFlightEvent;
use pocketmine\scheduler\ClosureTask;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\LevelEventPacket;

class Main extends PluginBase implements Listener {
    
    public array $jump = [];

    public function onEnable() : void {
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }

    public function onLogin(PlayerLoginEvent $event) {
        $player = $event->getPlayer();
        $this->jump[$player->getName()] = 0;
    }

    public function onQuit(PlayerQuitEvent $event) {
        $player = $event->getPlayer();
        if (isset($this->jump[$player->getName()])) {
            unset($this->jump[$player->getName()]);
        }
    }

    public function onJump(PlayerJumpEvent $event) {
        $player = $event->getPlayer();
        $this->jump[$player->getName()]++;  
        if ($this->jump[$player->getName()] == 1)  {
            $player->setAllowFlight(true);
        }

        if ($this->jump[$player->getName()] == 1) $this->getScheduler()->scheduleDelayedTask(new ClosureTask(function () use ($player) : void {
            $this->jump[$player->getName()] = 0;
        }), 30);
    }

    public function onToggle(PlayerToggleFlightEvent $event) {
        if ($this->jump[$event->getPlayer()->getName()] =< 0) return
        $player = $event->getPlayer();
        # Configurable, recommended: 0.4 - 0.6
        $jumpHeight = 0.4;
        $jumpDistance = 0.4; 
        $motionX = -sin(deg2rad($player->getLocation()->getYaw())) * $jumpDistance;
        $motionY = $jumpHeight;
        $motionZ = cos(deg2rad($player->getLocation()->getYaw())) * $jumpDistance;
        $player->setMotion(new Vector3($motionX, $motionY, $motionZ));
        $this->jump[$player->getName()] = 0;
        $event->cancel();
        $player->setAllowFlight(false);
    }
    
}
