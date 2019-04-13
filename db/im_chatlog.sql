/*
Navicat MySQL Data Transfer
Date: 2019-04-13 16:47:22
*/
SET FOREIGN_KEY_CHECKS=0;
-- ----------------------------
-- Table structure for im_chatlog
-- ----------------------------
DROP TABLE IF EXISTS `im_chatlog`;
CREATE TABLE `im_chatlog` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `room_id` int(11) NOT NULL,
  `from_uid` int(11) NOT NULL,
  `to_uid` int(11) NOT NULL,
  `content` text NOT NULL,
  `addtime` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
