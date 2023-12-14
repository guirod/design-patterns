# Patrons de conception / Design Patterns
## Définition


Un design pattern est un modèle d’architecture logicielle, dont le périmètre est limité à la résolution d’une problématique d’architecture courante. 


Les designs patterns servent à documenter les bonnes pratiques d’architecture basées sur l’expérience. 
Les design patterns ont été formellement reconnus en 1994 à la suite de la parution du livre Design Patterns : Elements of Reusable Software, co-écrit par quatre auteurs : Gamma, Helm, Johnson et Vlissides (Gang of Four - GoF ; en français « la bande des quatre »).


Il existe 23 patrons de conception (rassurez-vous nous ne les verrons pas tous) classés dans 3 familles : 
- Les patrons « créateurs » : définissent comment faire de l’instanciation et configuration de classes et objets. 
- Les patrons « structuraux » : définissent comment organiser les classes d’un programme dans une structure plus large. 
- Les patrons « comportementaux » : définissent comment organiser les objets pour que ceux-ci interagissent entre eux. 


Dans ce cours, nous allons simplement aborder les patrons les plus couramment utilisés.   
A savoir, les recruteurs aiment beaucoup vous demander de lister et de savoir expliquer 2 ou 3 designs patterns. 

Des parties du cours et des exemples sont tirées du site <https://refactoring.guru>. C'est une mine d'information et vous y trouverez des exemples détaillés de chaque design pattern.  
Le code des exemples du cours est situé dans ce repository (<https://github.com/guirod/design-patterns>).  

Pensez à exécuter `composer dump-autoload` à la racine du projet pour générer les fichiers d'autoload. 

N’hésitez pas à utiliser le débugguer pas-à-pas pour bien comprendre le fonctionnement du design pattern, ça vous aidera à voir comment évoluent les objets au fil du temps. 

 
## Singleton


Nous avons déjà utilisé ce patron de conception. Le singleton garantit que l’instance d’une classe n’existe qu’en un seul exemplaire tout en fournissant un point d’accès global à cette instance.  
On utilise donc ce patron lorsque l’on veut contrôler l’accès à une ressource partagées (une base de données ou un fichier par exemple).

### Etapes de mise en place


- Empêcher l’accès aux méthodes permettant de créer une instance de la classe que l’on souhaite passer en singleton.  
    Pour ceci, nous allons simplement modifier la visibilité de ces méthodes à « private » ou d’empêcher simplement leur utilisation en les surchargeant.  
    La méthode la plus évidente est bien évidemment le constructeur de notre classe, mais il faut aussi penser aux autres méthodes susceptibles de créer de nouvelles instances, telles que notamment les méthodes de clonage ainsi que les méthodes susceptibles d'altérer l’instance existante.   
    En PHP, il faudra donc idéalement prendre en compte les magic méthods suivantes : **__construct**, **__clone**, **__unserialize**, **__wakeup**

- Mettre en place une méthode de création statique qui se comporte comme un constructeur. Cette méthode va en réalité vérifier si une instance de la classe existe déjà, la créer seulement si elle n’existe pas, puis la retourner. 
 
### Exemple d’implémentation (classe de connexion)
```php
<?php

namespace Guirod\DesignPatterns\Singleton;

use PDO;
use PDOException;

class Connexion
{
    const SERVER_NAME = "mysql8";
    const USERNAME = "root";
    const PASSWORD = "p@ssw0rd";
    const DB_NAME = 'mvc_tp';

    private static ?Connexion $instance = NULL;

    private ?PDO $conn = null;

    static public function getInstance(): ?Connexion
    {
        if (self::$instance === NULL) {
            try {
                self::$instance = new Connexion();
            } catch (PDOException $e) {
                echo $e->getMessage();
            }
        }

        return self::$instance;
    }

    public function getConn(): PDO
    {
        return $this->conn;
    }

    /*
     * Private Constructor
     */
    private function __construct()
    {
        $this->conn = new PDO("mysql:host=". self::SERVER_NAME .";dbname=".self::DB_NAME, self::USERNAME, self::PASSWORD);
        $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    }

    /**
     * Nous aurions simplement pu modifier la visibilité à private ici
     * @return void
     */
    public function __clone() {
        trigger_error('Cloning forbidden.', E_USER_ERROR);
    }

    // Ici, on pousse le concept du singleton jusqu'au bout. Dans un projet où il n'y a pas 50 devs qui travaillent dessus, il ne sera pas forcément utile de surcharger ces magic methods.
    public function __wakeup(): void
    {
        trigger_error('Wakeup forbidden.', E_USER_ERROR);
    }

    public function __unserialize(array $data): void
    {
        trigger_error('Unserialize forbidden.', E_USER_ERROR);
    }
}
```

On accèdera ensuite à l’instance de notre connexion de la façon suivante : 
```php
<?php
require_once 'vendor/autoload.php';

use Guirod\DesignPatterns\Singleton\Connexion;

$conn = Connexion::getInstance();
$pdo = $conn->getConn();
```

## Factory / Fabrique


La fabrique est un patron de conception de création qui définit une interface pour créer des objets dans une classe mère, tout en déléguant le choix des types d’objets à créer aux sous-classes.  
L’intérêt principal de ce patron est d’alléger aux maximum la dépendance du code par rapport aux objets créés. 

Imaginons une entreprise de transport qui livre ses colis par camion. Le code métier sera donc principalement dans la classe camion et nos entités utilisées seront des camions.  
Toutefois, si à terme nous ajoutons un nouveau moyen de transport (bateau, avion, …) il serait compliqué de switcher entre les différents moyens de transports du fait de la dépendance forte à la classe Camion.  
La Fabrique nous propose de remplacer les appels aux constructeurs par une méthode de création d’objet en fonction d’un paramètre : ici le type de transport.  
Nous pouvons donc imaginer avoir maintenant une classe mère « Logistics » qui proposera de créer le véhicule souhaité.

![Schema factory 1](https://raw.githubusercontent.com/guirod/readme-images/main/design-patterns/factory_1.png "schema factory 1")  
*Schema factory 1*

### Exemple d’implémentation 

***Classe LogisticsFactory.php***
```php
<?php

namespace Guirod\DesignPatterns\Factory\V1;

abstract class LogisticsFactory
{
    // Ici, nous avons 2 possibilités
    // - définir la classe et la méthode "createTransport" abstract. Auquel cas nous déléguons en totalité la création du transport aux sous-classes. Dans ce cas, le paramètre "type" sera inutile
    // - Utiliser un paramètre "type" pour permettre à la fabrique de créer l'objet correspondant.
    abstract public static function createTransport(): LogisticsFactory;

    // Ici, nous aurons les méthodes partagées par les différents moyens de transport.
}
```

***Classe SeaLogistics.php***
```php
<?php

namespace Guirod\DesignPatterns\Factory\V1;

class SeaLogistics extends LogisticsFactory
{
    public static function createTransport(): LogisticsFactory
    {
        return new SeaLogistics();
    }
}
```

***Classe RoadLogistics.php***
```php
<?php

namespace Guirod\DesignPatterns\Factory\V1;

class RoadLogistics extends LogisticsFactory
{
    public static function createTransport(): LogisticsFactory
    {
        return new RoadLogistics();
    }
}
```

***Fichier factory.php***
```php
<?php
require_once 'vendor/autoload.php';

use Guirod\DesignPatterns\Factory\V1\RoadLogistics as RoadLogisticsV1;
use Guirod\DesignPatterns\Factory\V1\SeaLogistics as SeaLogisticsV1;
use Guirod\DesignPatterns\Factory\V2\LogisticsFactory as LogisticsV2;

//Depending on the purpose, create Road or Sea logistics
$logistics = RoadLogisticsV1::createTransport();
$logistics = SeaLogisticsV1::createTransport();

//2nd type de factory
$logistics = LogisticsV2::createTransport(LogisticsV2::TYPE_ROAD);
$logistics = LogisticsV2::createTransport(LogisticsV2::TYPE_SEA);

```

## Decorator / Décorateur
Le design pattern Décorateur est un patron de conception structurel qui permet d’affecter de nouvelles fonctionnalités à un objet existant, sans le modifier lui-même.  
Pour ça, on placera, à la manière des poupées gigognes, l’objet dans un nouveau conteneur qui ajoutera des fonctionnalités.  
Le décorateur devra être du même type que l’objet qu’il décore (il devra donc implémenter la même interface). 

### Exemple d’implémentation 

Imaginons que nous ayons un composant « Notifier » qui soit chargé d’envoyer des notifications par email. Nous souhaitons que nos utilisateurs puissent aussi être notifiés par d’autre mediums (par exemple : SMS, Facebook Messenger et Slack).  
Nous allons créer des Décorateurs de notre composant qui seront chargé d’envoyer les notifications. 


***NotifierInterface.php***
```php
<?php
namespace Guirod\DesignPatterns\Decorator;

interface NotifierInterface
{
    public function sendNotification();
}
```

***Notifier.php (notifier original)***
```php
<?php
namespace Guirod\DesignPatterns\Decorator;

class Notifier implements NotifierInterface
{
    public function sendNotification()
    {
        echo "Send Mail<br/>";
    }
}
```

***NotifierDecorator.php (Décorateur parent qui doit implémenter l’interface de l’objet à décorer)***
```php
<?php
namespace Guirod\DesignPatterns\Decorator;

abstract class NotifierDecorator implements NotifierInterface
{
    // Composant que l’on décore
    protected NotifierInterface $component;

    public function __construct(NotifierInterface $component)
    {
        $this->component = $component;
    }

    public function sendNotification()
    {
        // On appelle la méthode du composant décoré (l'envoi par mail)
        $this->component->sendNotification();
    }
}
```


***FacebookNotifierDecorator.php (notifier Messenger)***
```php
<?php
namespace Guirod\DesignPatterns\Decorator;

class FacebookNotifierDecorator extends NotifierDecorator
{
    public function sendNotification()
    {
        // On appelle la méthode du parent
        parent::sendNotification();
        // Puis on implémente la méthode de notification propre à l'envoi par messenger
        echo "Send Messenger<br/>";
    }
}
```
 
***SlackNotifierDecorator.php (notifier Slack)***
```php
<?php
namespace Guirod\DesignPatterns\Decorator;

class SlackNotifierDecorator extends NotifierDecorator
{
    public function sendNotification()
    {
        // On appelle la méthode du parent
        parent::sendNotification();
        // Puis on implément la méthode de notification propre à l'envoi par slack
        // ici ...
        echo "Send Slack<br/>";
    }
}
```


***SMSNotifierDecorator.php (notifier SMS)***
```php
<?php
namespace Guirod\DesignPatterns\Decorator;

class SMSNotifierDecorator extends NotifierDecorator
{
    public function sendNotification()
    {
        // On appelle la méthode du parent
        parent::sendNotification();
        // Puis on implément la méthode de notification propre à l'envoi par sms
        // ici ...
        echo "Send SMS<br/>";
    }
}
```


***decorator.php (script de test)***
```php
<?php
require_once 'vendor/autoload.php';

use Guirod\DesignPatterns\Decorator\Notifier;
use Guirod\DesignPatterns\Decorator\FacebookNotifierDecorator;
use Guirod\DesignPatterns\Decorator\SMSNotifierDecorator;
use Guirod\DesignPatterns\Decorator\SlackNotifierDecorator;

// Création de notre notifier de base
$notifier = new Notifier();

// Ici bien sur on récupèrera plutôt la config de l’utilisateur dans son compte, mais pour le test on assigne directement les variables booléennes. 
$messengerNotifEnabled = true;
$smsNotifEnabled = true;
$slackEnabled = true;

if ($messengerNotifEnabled) {
    $notifier = new FacebookNotifierDecorator($notifier);
}

if ($smsNotifEnabled) {
    $notifier = new SMSNotifierDecorator($notifier);
}

if ($slackEnabled) {
    $notifier = new SlackNotifierDecorator($notifier);
}

// Envoi des messages
$notifier->sendNotification();
```

***Résultat***

![Decorator output](https://raw.githubusercontent.com/guirod/readme-images/main/design-patterns/display_decorator.png "Decorator output")  

## Observer / Observateur

L’Observateur est un patron de conception comportemental qui permet de mettre en place un mécanisme de souscription pour envoyer des notifications à plusieurs objets, au sujet d’événements concernant les objets qu’ils observent.

Les objets observés sont nommés « Subjects » et les objets écoutant les événements des « Observers ». 

Une classe Subject devra pouvoir gérer la liste des observers qui lui sont rattachés (méthodes pour ajouter et supprimer des observers). 

En PHP, il existe des interfaces natives pour ces types d’objets : SplObserver et SplSubject. 

Ce pattern est couramment utilisé dans les frameworks modernes, notamment pour gérer le logging ou les notifications à l’exécution de certains événements. 

### Exemple d’implémentation 
Dans l’exemple suivant, nous allons justement voir comment mettre en place un système de logging et notification lorsque une classe Repository effectue des modifications en base de données. 


***Classe UserRepository : notre Subject***
```php
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
        // On créé un groupe d’événements pour les observers souhaitant écouter tous les évents.
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

    // Méthodes « métier »
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
```

***Classe User : le model (très basique ici)***
```php
<?php

namespace Guirod\DesignPatterns\Observer;

/*
 * La classe User est très simple, car ce n'est pas l'objet de l'étude.
 */
class User
{
    public array $attributes = [];

    public function update($data): void
    {
        $this->attributes = array_merge($this->attributes, $data);
    }
}
```

 
***Classe Logger : un de nos observers***
```php
<?php

namespace Guirod\DesignPatterns\Observer;

use SplObserver;
use SplSubject;

class Logger implements SplObserver
{
    private string $filename;

    public function __construct(string $filename)
    {
        $this->filename = $filename;
        if (file_exists($this->filename)) {
            unlink($this->filename);
        }
    }
    public function update(SplSubject $subject, string $event = null, $data = null): void
    {
        $entry = date("Y-m-d H:i:s") . ": '$event' with data '" . json_encode($data) . "\n";
        file_put_contents($this->filename, $entry, FILE_APPEND);

        echo "Logger: I've written '$event' entry to the log.<br/>";
    }
}
```


***Classe OnboardingNotification : Un autre observer***
```php
<?php

namespace Guirod\DesignPatterns\Observer;

use SplObserver;
use SplSubject;

class OnboardingNotification implements SplObserver
{
    private string $adminEmail;

    public function __construct(string $adminEmail)
    {
        $this->adminEmail = $adminEmail;
    }

    public function update(SplSubject $subject, string $event = null, $data = null): void
    {
        // mail($this->adminEmail,
        //     "Onboarding required",
        //     "We have a new user. Here's his info: " .json_encode($data));

        echo "OnboardingNotification: The notification has been emailed!<br>";
    }
}
```


***Le script de test : observer.php***
```php
<?php
require_once 'vendor/autoload.php';

use Guirod\DesignPatterns\Observer\Logger;
use Guirod\DesignPatterns\Observer\OnboardingNotification;
use Guirod\DesignPatterns\Observer\UserRepository;

$repository = new UserRepository();
// On inscrit le logger à tous les events du repository
$repository->attach(new Logger(__DIR__ . "/log.txt"), "*");

//On inscrit le notifier seulement aux events user:created et users:updates
$repository->attach(new OnboardingNotification("1@example.com"), "users:created");
$repository->attach(new OnboardingNotification("1@example.com"), "users:updated");

$repository->initialize(__DIR__ . "/users.csv");

$user = $repository->createUser([
    "name" => "John Smith",
    "email" => "john99@example.com",
]);

$repository->updateUser($user, [
    "name" => "John Doe",
    "email" => "john@doe.com",
]);

$repository->deleteUser($user);
```

## Inversion Of Control (IOC) / Injection de dépendances

L’inversion de contrôle peut également être considérée comme un patron de conception.  
L’IOC est un terme générique, et sa représentation la plus connue est l’Injection de dépendance. 

Le but premier de l’injection de dépendance est de découpler au maximum les dépendances entre les différentes classes d’un projet afin d’améliorer ses possibilités d’évolution et sa maintenabilité.   
Ainsi, plutôt que laisser la classe ayant des dépendances avec des services ou composants initialiser elle-même ces composants, les composants seront injectés dynamiquement à l’exécution. 

Imaginons par exemple un fonctionnement classique, sans injection de dépendances : 
- L’application nécessite une classe Foo (par exemple un contrôleur)
    - L’application créé une instance de Foo
    - L’application appelle Foo
    - Foo nécessite une classe Bar (par exemple un service)
        - Foo créé une instance de Bar
        - Foo appelle Bar
        - Bar nécessite une classe Bim
            - Bar créé Bim
            - Bar appelle Bim

En utilisant l’injection de dépendances, nous procéderons de la façon suivante : 
- L’application nécessite Foo, qui nécessite Bar, qui nécessite Bim
- L’application créé Bim
- L’application créé Bar et lui injecte Bim
- L’application créé Foo et lui injecte Bar
- L’application appelle Foo
    - Foo appelle Bar
        - Bar appelle Bim

Ce fonctionnement permet de remplacer les dépendances de façon plus souple et évolutive (par exemple, dans le cas d’un changement de driver de bases de données, changement de logger). 

 De plus, les frameworks modernes, tels que Symfony, fonctionnent avec un système de Container.  
 C’est le container qui sera chargé de fournir les différents services à l’application.  
 Ainsi, l’application n’aura plus à gérer elle-même l’instanciation des différents services, elle les demandera simplement au Container qui s’occupera de la création ou de la récupération des instances.  
