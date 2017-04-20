-- phpMyAdmin SQL Dump
-- version 4.5.1
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Erstellungszeit: 31. Aug 2016 um 20:34
-- Server-Version: 10.1.13-MariaDB
-- PHP-Version: 7.0.8

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Datenbank: `speechday`
--
CREATE DATABASE IF NOT EXISTS `speechday` DEFAULT CHARACTER SET utf8 COLLATE utf8_bin;
USE `speechday`;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `accessdata`
--
CREATE TABLE `accessdata` (
  `id` int(11) NOT NULL,
  `userName` varchar(255) COLLATE utf8_bin NOT NULL,
  `password` varchar(255) COLLATE utf8_bin NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Tabellenstruktur für Tabelle `event`
--
CREATE TABLE `event` (
  `id` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8_bin NOT NULL,
  `dateFrom` int(11) NOT NULL,
  `dateTo` int(11) NOT NULL,
  `slotTimeMin` int(11) NOT NULL DEFAULT '5',
  `isActive` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Tabellenstruktur für Tabelle `log`
--
CREATE TABLE `log` (
  `id` int(11) NOT NULL,
  `userId` int(11) NOT NULL,
  `action` int(11) NOT NULL COMMENT '1 = logIn, 2 = logOut, 3 = bookSlot, 4 = deleteSlot',
  `info` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  `date` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Tabellenstruktur für Tabelle `room`
--
CREATE TABLE `room` (
  `id` int(11) NOT NULL,
  `roomNumber` varchar(255) COLLATE utf8_bin NOT NULL,
  `name` varchar(255) COLLATE utf8_bin NOT NULL,
  `teacherId` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Tabellenstruktur für Tabelle `slot`
--
CREATE TABLE `slot` (
  `id` int(11) NOT NULL,
  `eventId` int(11) NOT NULL,
  `teacherId` int(11) NOT NULL,
  `studentId` int(11) DEFAULT NULL,
  `dateFrom` int(11) NOT NULL,
  `dateTo` int(11) NOT NULL,
  `type` int(1) NOT NULL DEFAULT '1' COMMENT '1 = normal, 2 = break',
  `available` int(11) NOT NULL DEFAULT '1' COMMENT '1 = available, 0 = not available'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Tabellenstruktur für Tabelle `user`
--
CREATE TABLE `user` (
  `id` int(11) NOT NULL,
  `userName` varchar(255) COLLATE utf8_bin NOT NULL,
  `passwordHash` varchar(255) COLLATE utf8_bin NOT NULL,
  `firstName` varchar(255) COLLATE utf8_bin NOT NULL,
  `lastName` varchar(255) COLLATE utf8_bin NOT NULL,
  `class` varchar(255) COLLATE utf8_bin DEFAULT NULL,
  `role` enum('student','teacher','admin') COLLATE utf8_bin NOT NULL DEFAULT 'student',
  `title` varchar(255) COLLATE utf8_bin DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Daten für Tabelle `user`
-- Standardpasswort für den Administrator: admin
--

INSERT INTO `user` (`id`, `userName`, `passwordHash`, `firstName`, `lastName`, `class`, `role`) VALUES
(1, 'admin', '$2y$10$rxHdBYx/Lq2Od6etxBIj7OfMhVwEQpJn4bD.4tCAD/4g7VyTrPAum', 'AdminVN', 'AdminNN', NULL, 'admin');

-- --------------------------------------------------------

--
-- Indizes der exportierten Tabellen
--

--
-- Indizes für die Tabelle `accessdata`
--
ALTER TABLE `accessdata`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `userName` (`userName`);

--
-- Indizes für die Tabelle `event`
--
ALTER TABLE `event`
  ADD PRIMARY KEY (`id`);

--
-- Indizes für die Tabelle `log`
--
ALTER TABLE `log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_log_user` (`userId`);

--
-- Indizes für die Tabelle `room`
--
ALTER TABLE `room`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `teacherId` (`teacherId`),
  ADD KEY `fk_room_user` (`teacherId`);

--
-- Indizes für die Tabelle `slot`
--
ALTER TABLE `slot`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_slot_event` (`eventId`),
  ADD KEY `fk_slot_teacher` (`teacherId`),
  ADD KEY `fk_slot_student` (`studentId`);

--
-- Indizes für die Tabelle `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `userName` (`userName`);

-- --------------------------------------------------------
	
--
-- AUTO_INCREMENT für exportierte Tabellen
--

--
-- AUTO_INCREMENT für Tabelle `accessdata`
--
ALTER TABLE `accessdata`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT für Tabelle `event`
--
ALTER TABLE `event`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT für Tabelle `log`
--
ALTER TABLE `log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT für Tabelle `room`
--
ALTER TABLE `room`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT für Tabelle `slot`
--
ALTER TABLE `slot`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT für Tabelle `user`
--
ALTER TABLE `user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
	
-- --------------------------------------------------------

--
-- Constraints der exportierten Tabellen
--

--
-- Constraints der Tabelle `accessdata`
--
ALTER TABLE `accessdata`
  ADD CONSTRAINT `fk_accessdata_username` FOREIGN KEY (`userName`) REFERENCES `user` (`userName`) ON DELETE CASCADE;

--
-- Constraints der Tabelle `log`
--
ALTER TABLE `log`
  ADD CONSTRAINT `fk_log_user` FOREIGN KEY (`userId`) REFERENCES `user` (`id`) ON DELETE CASCADE;

--
-- Constraints der Tabelle `room`
--
ALTER TABLE `room`
  ADD CONSTRAINT `fk_room_user` FOREIGN KEY (`teacherId`) REFERENCES `user` (`id`) ON DELETE CASCADE;

--
-- Constraints der Tabelle `slot`
--
ALTER TABLE `slot`
  ADD CONSTRAINT `fk_slot_event` FOREIGN KEY (`eventId`) REFERENCES `event` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_slot_student` FOREIGN KEY (`studentId`) REFERENCES `user` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_slot_teacher` FOREIGN KEY (`teacherId`) REFERENCES `user` (`id`) ON DELETE CASCADE;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
