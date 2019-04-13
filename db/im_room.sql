/*
Navicat MySQL Data Transfer
Target Server Type    : MYSQL
Date: 2019-04-13 16:47:16
*/
SET FOREIGN_KEY_CHECKS=0;
-- ----------------------------
-- Table structure for im_room
-- ----------------------------
DROP TABLE IF EXISTS `im_room`;
CREATE TABLE `im_room` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid_lawyerid` varchar(20) NOT NULL,
  `addtime` int(11) NOT NULL,
  `lastime` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uid_lawyerid` (`uid_lawyerid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
