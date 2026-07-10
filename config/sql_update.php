ALTER TABLE `transactions` ADD `type` SET('in','out') NOT NULL AFTER `status`;
