<?php

namespace Guirod\DesignPatterns\Observer;

use SplObserver;
use SplSubject;

/**
 * Une classe repository est une classe gérant les ajouts/suppression/modification en BDD d'un model.
 * C'est le subject du design pattern Observer étudié.
 * En effet, de nombreux objets peuvent vouloir suivre les modifications qui peuvent avoir lieu (exemple : logger)
 */
class UserRepository implements SplSubject
{
    /** @var User[]  : La liste des utilisateurs */
    private array $users;

    /** @var array : la liste des observers de notre subject */
    private array $observers;

    public function __construct()
    {
        // A special event group for observers that want to listen to all events.
        $this->observers["*"] = [];
    }

    private function initEventGroup(string $event = "*"): void
    {
        if (!isset($this->observers[$event])) {
            $this->observers[$event] = [];
        }
    }

    private function getEventObservers(string $event = "*"): array
    {
        $this->initEventGroup($event);
        $group = $this->observers[$event];
        $all = $this->observers["*"];

        return array_merge($group, $all);
    }

    public function attach(SplObserver $observer, string $event = "*"): void
    {
        $this->initEventGroup($event);
        $this->observers[$event][] = $observer;
    }

    public function detach(SplObserver $observer, string $event = "*"): void
    {
        foreach ($this->getEventObservers($event) as $key => $s) {
            if ($s === $observer) {
                unset($this->observers[$event][$key]);
            }
        }
    }

    public function notify(string $event = "*", $data = null): void
    {
        echo "UserRepository: Broadcasting the '$event' event.<br>";
        foreach ($this->getEventObservers($event) as $observer) {
            $observer->update($this, $event, $data);
        }
    }

    // Here are the methods representing the business logic of the class.
    public function initialize($filename): void
    {
        echo "UserRepository: Loading user records from a file.<br>";
        // ...
        $this->notify("users:init", $filename);
    }

    public function createUser(array $data): User
    {
        echo "UserRepository: Creating a user.<br>";

        $user = new User();
        $user->update($data);

        $id = bin2hex(openssl_random_pseudo_bytes(16));
        $user->update(["id" => $id]);
        $this->users[$id] = $user;

        $this->notify("users:created", $user);

        return $user;
    }

    public function updateUser(User $user, array $data): ?User
    {
        echo "UserRepository: Updating a user.<br>";

        $id = $user->attributes["id"];

        if (!isset($this->users[$id])) {
            return null;
        }

        $user = $this->users[$id];
        $user->update($data);

        $this->notify("users:updated", $user);

        return $user;
    }

    public function deleteUser(User $user): void
    {
        echo "UserRepository: Deleting a user.<br>";

        $id = $user->attributes["id"];
        if (!isset($this->users[$id])) {
            return;
        }

        unset($this->users[$id]);

        $this->notify("users:deleted", $user);
    }
}