<p align="center">
 <img src="https://media.discordapp.net/attachments/692618172012167200/1132810272361435166/avatar.png?width=960&height=164" >
</p>

<h1 align="center">Information</h1>
<ul>
  <li>Author: Zenqi</li>
  <li>API: 5.0.0</li>
  <li>Version: 1.0.0</li>
  <li>Time spent: 2 hours</li>
</ul>
<h1 align="center">How does it work?</h1>

- on ``PlayerLogin`` the **player** would be added in jump array
```php
public function onLogin(PlayerLoginEvent $event) {
        $player = $event->getPlayer();
        $this->jump[$player->getName()] = 0;
    }
```
- on ``PlayerQuit`` the **player** would be removed from the jump array
```php
  public function onQuit(PlayerQuitEvent $event) {
        $player = $event->getPlayer();
        if (isset($this->jump[$player->getName()])) {
            unset($this->jump[$player->getName()]);
        }
    }
```
- on ``PlayerJump`` the jump array will *temporary* **enable** the **player** permission to **fly**, and keep track of number of jumps that has maximum of ``2`` so if player stops **jumping** it would go back to ``0``, by temporary enabling the permission to fly, the player now can trigger the ``PlayerToggleFlightEvent`` which can make the player jump mid-air without actually toggling the fly function
```php
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
```
- on ``PlayerToggleFlightEvent`` the direction where the player is will be the direction where the player will jump mid-air, this is a necessary function due to other ``plugins`` jumping to random sides, using ``cosine`` and ``sine`` to get the direction of player.
```php
 public function onToggle(PlayerToggleFlightEvent $event) {
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
```
