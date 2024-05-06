/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
/**
 * Author:  azhar.waris
 * Created: Mar 28, 2019
 */

CREATE TABLE `app_routes` (
  `id` int(11) NOT NULL,
  `url` varchar(500) NOT NULL,
  `altURL` varchar(500) DEFAULT NULL,
  `responseCode` int(11) DEFAULT NULL,
  `route` text,
  `title` varchar(255) DEFAULT NULL,
  `keywords` varchar(255) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `addedOn` datetime DEFAULT NULL,
  `addedBy` varchar(50) DEFAULT NULL,
  `type` varchar(100) DEFAULT NULL,
  `typeId` int(11) DEFAULT NULL,
  `active` enum('Y','N') DEFAULT 'Y'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;


INSERT INTO `app_routes` (`id`, `url`, `altURL`, `responseCode`, `route`, `title`, `keywords`, `description`, `addedOn`, `addedBy`, `type`, `typeId`, `active`) VALUES
(1, 'nrsp-microfinance-bank-limited-head-islamic-microfinance-division-islamabad-jobs-431028.php', NULL, NULL, 'Job@detail@get', NULL, NULL, NULL, '2019-03-22 12:05:34', 'system', 'job', 431028, 'Y');

ALTER TABLE `app_routes`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `app_routes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;