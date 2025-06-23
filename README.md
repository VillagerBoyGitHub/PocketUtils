# PocketUtils - Once was only SecureOP

PocketUtils is a PocketMine Genisys 2.0.0 API focused on improving user-friendliness and adding features that haven't been created before. So far, PocketUtils comes with these built-in features:

## **SecureOP**
This feature is actually how the project was started. I saw many people who have a hacked client that allows them to become server operators without an admin executing the command. That's why I thought of adding a password to the OP command. This password is stored in the default server.properties and can be changed. The OP command has been re-written with the following syntax: "/op \<op password> \<player>".

## **Built-in prefix**
The built-in prefix feature can be useful for server admins and developers, because it adds a property in the server.properties named "server-prefix". This property is a prefix that can be used for plugin messages instead of having to copy and paste the prefix over and over again between each plugin. The property can be called by simply calling the "getServerPrefix()" that is stored in the "pocketmine\Server" class. You can call it the same way that you'd call a method like getOnlinePlayers() for example. It's simple but saves a lot of time!