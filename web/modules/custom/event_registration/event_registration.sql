-- --------------------------------------------------------
-- Table structure for table `event_registration_event`
-- --------------------------------------------------------

CREATE TABLE `event_registration_event` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `event_name` varchar(255) NOT NULL,
  `category` varchar(100) NOT NULL,
  `registration_start_date` int(11) NOT NULL,
  `registration_end_date` int(11) NOT NULL,
  `event_date` int(11) NOT NULL,
  `created` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `category` (`category`),
  KEY `event_date` (`event_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Stores event configuration details';

-- --------------------------------------------------------
-- Table structure for table `event_registration_signup`
-- --------------------------------------------------------

CREATE TABLE `event_registration_signup` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `event_id` int(10) unsigned NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `college_name` varchar(255) NOT NULL,
  `department` varchar(255) NOT NULL,
  `created` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `email` (`email`),
  KEY `event_id` (`event_id`),
  CONSTRAINT `fk_event_registration_event`
    FOREIGN KEY (`event_id`)
    REFERENCES `event_registration_event` (`id`)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Stores event registration data';
