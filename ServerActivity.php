<?php
/**
 * Created by Brennan and Reid
 * Date: 7/20/2018
 * Time: 10:01 AM
 */

class Event {

    private $timeStamp;
    private $line;
    private $eventType;
    function __construct($line, $eventType)
    {
        $this->line = $line;
        $this->eventType = $eventType;
        $this->timeStamp = substr($line, 0, 10);
    }

    public function getEventType(){
        return $this->eventType;
    }

    public function  __toString()
    {
        return $this->timeStamp." ".$this->eventType;
    }
}
class User {
    private $username;
    private $events;

    function __construct($username) {
        $this->events = array();
        $this->username = $username;
    }

    public function addEvent($event) {
        $this->events[] = $event;
    }

    public function getUserName(){
        return $this->username;
    }

    public function getStatus(){
        switch(end($this->events)->getEventType()){
            case "logout":
                return "logged out";
                break;

            case "login":
                return "logged in";
                break;
        }

    }

    public function  __toString()
    {
        $returnString = "";
        foreach ($this->events as $event) {
            $returnString .= $this->username." ".$event."<br>";
        }
        return $returnString;
    }

}

function getUsername($line, $login){
    $beginUser = substr($line, 78);
    return substr($beginUser, 0, strpos($beginUser,($login ? "[" : " ")));
}

function recordEvent(&$users, $line, $eventType) {
    //echo $users;
    if($eventType == "logout" || $eventType == "login") {
        $username = getUsername($line, $eventType == "login");
        if(!$users[$username]) {
           $users[$username] = new User($username);
        }
        $event = new Event($line, $eventType);
        $users[$username]->addEvent($event);

    }
}

$myfile = fopen("latest.log", "r") or die("Unable to open file!");
$loggedIn = array();
$loggedOut = array();
$users = array();
while(!feof($myfile)) {

        $line = fgets($myfile);

        if (strpos($line, '[Server thread/INFO] [net.minecraft.network.NetHandlerPlayServer]')) {
            $loggedOut[] = $line;
            recordEvent($users, $line, "logout");
            //echo $line."<br>";


        }
        if(strpos($line, '[net.minecraft.server.management.PlayerList]')) {
            $loggedIn[] = $line;
            recordEvent($users, $line, "login");
            //echo $line."<br>";
        }
}
fclose($myfile);
foreach ($users as $user) {
    echo $user->getUserName().": ".$user->getStatus()."<br>";
}
?>