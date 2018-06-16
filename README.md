# InventoryGUI
InventoryGUI is a Altay and PocketMine-MP plugin that eases creating fake inventories!

### Creating a GUI
First you choose inventory. InventoryGUI has two inventories, but you can create your own gui types.
```php
use Enes5519\InventoryGUI\inventory\ChestInventory;
use Enes5519\InventoryGUI\inventory\DoubleChestInventory;
``` 

Let's say you chose DoubleChestInventory.
```php
$inv = new DoubleChestInventory();
```
Your fake double chest inventory now ready. Now, let's fill this with some items.
```php
$inv->setContents([
	ItemFactory::get(Item::APPLE),
	ItemFactory::get(Item::COMPASS)
]);
```
Let's send it to the player now.
```php
$player->addWindow($inv);
``` 
Yup, that's it. It's that simple.

### Specifying a custom name to the GUI
```php
$inv->setCustomName("Custom Name");
```