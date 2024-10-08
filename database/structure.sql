-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: database
-- Generation Time: Aug 12, 2024 at 06:13 AM
-- Server version: 8.4.1
-- PHP Version: 8.2.21

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+02:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ffgbsy`
--

-- --------------------------------------------------------

--
-- Table structure for table `aufnehmer`
--

CREATE TABLE `aufnehmer` (
  `id` int NOT NULL,
  `vorname` varchar(50) DEFAULT NULL,
  `nachname` varchar(50) DEFAULT NULL,
  `aktiv` tinyint(1) NOT NULL DEFAULT '0',
  `zoom_level` int NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `bestellpositionen`
--

CREATE TABLE `bestellpositionen` (
  `id` int NOT NULL,
  `anzahl` int NOT NULL,
  `produkte_id` int NOT NULL,
  `notiz` text,
  `bestellungen_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `bestellpositionen_eigenschaften`
--

CREATE TABLE `bestellpositionen_eigenschaften` (
  `bestellpositionen_id` int NOT NULL,
  `eigenschaften_id` int NOT NULL,
  `in_produkt_enthalten` tinyint(1) DEFAULT NULL,
  `aktiv` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `bestellungen`
--

CREATE TABLE `bestellungen` (
  `id` int NOT NULL,
  `tische_id` int NOT NULL,
  `timestamp_begonnen` datetime NOT NULL,
  `timestamp_beendet` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `aufnehmer_id` int NOT NULL,
  `device_name` varchar(50) NOT NULL,
  `device_ip` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `bons`
--

CREATE TABLE `bons` (
  `id` int NOT NULL,
  `type` enum('bestellung','storno') CHARACTER SET utf8mb3 COLLATE utf8mb3_general_ci NOT NULL,
  `bestellungen_id` int NOT NULL,
  `drucker_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `bons_bestellpositionen`
--

CREATE TABLE `bons_bestellpositionen` (
  `id` int NOT NULL,
  `bons_id` int NOT NULL,
  `bestellpositionen_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `bons_druck`
--

CREATE TABLE `bons_druck` (
  `id` int NOT NULL,
  `bons_id` int NOT NULL,
  `datum` date NOT NULL,
  `laufnummer` int NOT NULL,
  `timestamp` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `success` tinyint(1) DEFAULT NULL,
  `message` varchar(300) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `constants`
--

CREATE TABLE `constants` (
  `name` varchar(50) NOT NULL,
  `value` varchar(300) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `drucker`
--

CREATE TABLE `drucker` (
  `id` int NOT NULL,
  `name` varchar(30) NOT NULL,
  `ip` varchar(30) NOT NULL,
  `port` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `eigenschaften`
--

CREATE TABLE `eigenschaften` (
  `id` int NOT NULL,
  `name` varchar(30) NOT NULL,
  `preis` decimal(19,2) NOT NULL DEFAULT '0.00',
  `sortierindex` int NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `grundprodukte`
--

CREATE TABLE `grundprodukte` (
  `id` int NOT NULL,
  `name` varchar(50) NOT NULL,
  `bestand` int DEFAULT NULL,
  `einheit` varchar(30) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `historical_statistics_days`
--

CREATE TABLE `historical_statistics_days` (
  `id` int NOT NULL,
  `historical_statistics_events_id` int NOT NULL,
  `datum` date NOT NULL,
  `umsatz` int NOT NULL DEFAULT '0',
  `bestellungen_anzahl` int NOT NULL DEFAULT '0',
  `produkte_anzahl` int NOT NULL DEFAULT '0',
  `hauptspeisen_anzahl` int NOT NULL DEFAULT '0',
  `speisen_anzahl` int NOT NULL DEFAULT '0',
  `getraenke_anzahl` int NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `historical_statistics_events`
--

CREATE TABLE `historical_statistics_events` (
  `id` int NOT NULL,
  `name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `logs`
--

CREATE TABLE `logs` (
  `id` int NOT NULL,
  `level` int NOT NULL,
  `message` varchar(500) NOT NULL,
  `additional` varchar(300) DEFAULT NULL,
  `timestamp` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `device_name` varchar(50) DEFAULT NULL,
  `device_ip` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int NOT NULL,
  `title` varchar(50) NOT NULL,
  `message` varchar(300) DEFAULT NULL,
  `author` varchar(50) DEFAULT NULL,
  `timestamp` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `produktbereiche`
--

CREATE TABLE `produktbereiche` (
  `id` int NOT NULL,
  `name` varchar(50) NOT NULL,
  `color` varchar(30) DEFAULT NULL,
  `drucker_id_level_0` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `produkte`
--

CREATE TABLE `produkte` (
  `id` int NOT NULL,
  `name` varchar(30) NOT NULL,
  `formal_name` varchar(50) DEFAULT NULL,
  `preis` decimal(19,2) NOT NULL,
  `drucker_id_level_2` int DEFAULT NULL,
  `aktiv` tinyint(1) NOT NULL DEFAULT '1',
  `sortierindex` int DEFAULT NULL,
  `produkteinteilungen_id` int NOT NULL,
  `grundprodukte_id` int DEFAULT NULL,
  `grundprodukte_multiplikator` int DEFAULT NULL,
  `celebration_active` tinyint(1) NOT NULL DEFAULT '0',
  `celebration_last` int NOT NULL DEFAULT '0',
  `celebration_prefix` varchar(30) DEFAULT NULL,
  `celebration_suffix` varchar(30) DEFAULT NULL,
  `hauptspeise` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `produkteinteilungen`
--

CREATE TABLE `produkteinteilungen` (
  `id` int NOT NULL,
  `name` varchar(50) NOT NULL,
  `produktkategorien_id` int NOT NULL,
  `sortierindex` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `produkte_eigenschaften`
--

CREATE TABLE `produkte_eigenschaften` (
  `produkte_id` int NOT NULL,
  `eigenschaften_id` int NOT NULL,
  `in_produkt_enthalten` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `produktkategorien`
--

CREATE TABLE `produktkategorien` (
  `id` int NOT NULL,
  `name` varchar(30) NOT NULL,
  `color` varchar(30) NOT NULL,
  `produktbereiche_id` int NOT NULL,
  `drucker_id_level_1` int DEFAULT NULL,
  `sortierindex` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `produktkategorien_eigenschaften`
--

CREATE TABLE `produktkategorien_eigenschaften` (
  `produktkategorien_id` int NOT NULL,
  `eigenschaften_id` int NOT NULL,
  `in_produkt_enthalten` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `tische`
--

CREATE TABLE `tische` (
  `id` int NOT NULL,
  `reihe` varchar(30) NOT NULL,
  `nummer` int DEFAULT NULL,
  `tischkategorien_id` int NOT NULL,
  `aktiv` tinyint(1) NOT NULL DEFAULT '1',
  `sortierindex` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `tischkategorien`
--

CREATE TABLE `tischkategorien` (
  `id` int NOT NULL,
  `name` varchar(50) NOT NULL,
  `aktiv` tinyint(1) NOT NULL DEFAULT '1',
  `sortierindex` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `aufnehmer`
--
ALTER TABLE `aufnehmer`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `bestellpositionen`
--
ALTER TABLE `bestellpositionen`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_bestellpositionen_produkte_id` (`produkte_id`),
  ADD KEY `fk_bestellpositionen_bestellungen_id` (`bestellungen_id`);

--
-- Indexes for table `bestellpositionen_eigenschaften`
--
ALTER TABLE `bestellpositionen_eigenschaften`
  ADD PRIMARY KEY (`bestellpositionen_id`,`eigenschaften_id`);

--
-- Indexes for table `bestellungen`
--
ALTER TABLE `bestellungen`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_bestellungen_aufnehmer_id` (`aufnehmer_id`),
  ADD KEY `fk_bestellungen_tische_id` (`tische_id`);

--
-- Indexes for table `bons`
--
ALTER TABLE `bons`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_bons_bestellungen_id` (`bestellungen_id`),
  ADD KEY `fk_bons_drucker_id` (`drucker_id`);

--
-- Indexes for table `bons_bestellpositionen`
--
ALTER TABLE `bons_bestellpositionen`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_bons_bestellpositionen_bons_id` (`bons_id`),
  ADD KEY `fk_bons_bestellpositionen_bestellpositionen_id` (`bestellpositionen_id`);

--
-- Indexes for table `bons_druck`
--
ALTER TABLE `bons_druck`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_bons_druck_bons_id` (`bons_id`);

--
-- Indexes for table `constants`
--
ALTER TABLE `constants`
  ADD PRIMARY KEY (`name`);

--
-- Indexes for table `drucker`
--
ALTER TABLE `drucker`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `eigenschaften`
--
ALTER TABLE `eigenschaften`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `grundprodukte`
--
ALTER TABLE `grundprodukte`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `historical_statistics_days`
--
ALTER TABLE `historical_statistics_days`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_historical_tatistics_days_historical_statistics_events` (`historical_statistics_events_id`);

--
-- Indexes for table `historical_statistics_events`
--
ALTER TABLE `historical_statistics_events`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `logs`
--
ALTER TABLE `logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `produktbereiche`
--
ALTER TABLE `produktbereiche`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_produktbereiche_drucker_id_level_0` (`drucker_id_level_0`);

--
-- Indexes for table `produkte`
--
ALTER TABLE `produkte`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_produkte_produkteinteilungen_id` (`produkteinteilungen_id`),
  ADD KEY `fk_produkte_drucker_id_level_2` (`drucker_id_level_2`),
  ADD KEY `fk_produkte_grundprodukte_id` (`grundprodukte_id`);

--
-- Indexes for table `produkteinteilungen`
--
ALTER TABLE `produkteinteilungen`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_produkteinteilungen_produktkategorien_id` (`produktkategorien_id`);

--
-- Indexes for table `produkte_eigenschaften`
--
ALTER TABLE `produkte_eigenschaften`
  ADD PRIMARY KEY (`produkte_id`,`eigenschaften_id`),
  ADD KEY `fk_produkte_eigenschaften_eigenschaften_id` (`eigenschaften_id`);

--
-- Indexes for table `produktkategorien`
--
ALTER TABLE `produktkategorien`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_produktkategorien_produktbereiche_id` (`produktbereiche_id`),
  ADD KEY `fk_produktkategorien_drucker_id_level_1` (`drucker_id_level_1`);

--
-- Indexes for table `produktkategorien_eigenschaften`
--
ALTER TABLE `produktkategorien_eigenschaften`
  ADD PRIMARY KEY (`produktkategorien_id`,`eigenschaften_id`),
  ADD KEY `fk_produktkategorien_eigenschaften_eigenschaften_id` (`eigenschaften_id`);

--
-- Indexes for table `tische`
--
ALTER TABLE `tische`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tischkategorien`
--
ALTER TABLE `tischkategorien`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `aufnehmer`
--
ALTER TABLE `aufnehmer`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `bestellpositionen`
--
ALTER TABLE `bestellpositionen`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `bestellungen`
--
ALTER TABLE `bestellungen`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `bons`
--
ALTER TABLE `bons`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `bons_bestellpositionen`
--
ALTER TABLE `bons_bestellpositionen`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `bons_druck`
--
ALTER TABLE `bons_druck`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `drucker`
--
ALTER TABLE `drucker`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `eigenschaften`
--
ALTER TABLE `eigenschaften`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `grundprodukte`
--
ALTER TABLE `grundprodukte`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `historical_statistics_days`
--
ALTER TABLE `historical_statistics_days`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `historical_statistics_events`
--
ALTER TABLE `historical_statistics_events`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `logs`
--
ALTER TABLE `logs`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `produktbereiche`
--
ALTER TABLE `produktbereiche`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `produkte`
--
ALTER TABLE `produkte`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `produkteinteilungen`
--
ALTER TABLE `produkteinteilungen`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `produktkategorien`
--
ALTER TABLE `produktkategorien`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tische`
--
ALTER TABLE `tische`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tischkategorien`
--
ALTER TABLE `tischkategorien`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bestellpositionen`
--
ALTER TABLE `bestellpositionen`
  ADD CONSTRAINT `fk_bestellpositionen_bestellungen_id` FOREIGN KEY (`bestellungen_id`) REFERENCES `bestellungen` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_bestellpositionen_produkte_id` FOREIGN KEY (`produkte_id`) REFERENCES `produkte` (`id`);

--
-- Constraints for table `bestellungen`
--
ALTER TABLE `bestellungen`
  ADD CONSTRAINT `fk_bestellungen_aufnehmer_id` FOREIGN KEY (`aufnehmer_id`) REFERENCES `aufnehmer` (`id`),
  ADD CONSTRAINT `fk_bestellungen_tische_id` FOREIGN KEY (`tische_id`) REFERENCES `tische` (`id`);

--
-- Constraints for table `bons`
--
ALTER TABLE `bons`
  ADD CONSTRAINT `fk_bons_bestellungen_id` FOREIGN KEY (`bestellungen_id`) REFERENCES `bestellungen` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_bons_drucker_id` FOREIGN KEY (`drucker_id`) REFERENCES `drucker` (`id`);

--
-- Constraints for table `bons_bestellpositionen`
--
ALTER TABLE `bons_bestellpositionen`
  ADD CONSTRAINT `fk_bons_bestellpositionen_bestellpositionen_id` FOREIGN KEY (`bestellpositionen_id`) REFERENCES `bestellpositionen` (`id`),
  ADD CONSTRAINT `fk_bons_bestellpositionen_bons_id` FOREIGN KEY (`bons_id`) REFERENCES `bons` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `bons_druck`
--
ALTER TABLE `bons_druck`
  ADD CONSTRAINT `fk_bons_druck_bons_id` FOREIGN KEY (`bons_id`) REFERENCES `bons` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `historical_statistics_days`
--
ALTER TABLE `historical_statistics_days`
  ADD CONSTRAINT `fk_historical_tatistics_days_historical_statistics_events` FOREIGN KEY (`historical_statistics_events_id`) REFERENCES `historical_statistics_events` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;

--
-- Constraints for table `produktbereiche`
--
ALTER TABLE `produktbereiche`
  ADD CONSTRAINT `fk_produktbereiche_drucker_id_level_0` FOREIGN KEY (`drucker_id_level_0`) REFERENCES `drucker` (`id`);

--
-- Constraints for table `produkte`
--
ALTER TABLE `produkte`
  ADD CONSTRAINT `fk_produkte_drucker_id_level_2` FOREIGN KEY (`drucker_id_level_2`) REFERENCES `drucker` (`id`),
  ADD CONSTRAINT `fk_produkte_grundprodukte_id` FOREIGN KEY (`grundprodukte_id`) REFERENCES `grundprodukte` (`id`),
  ADD CONSTRAINT `fk_produkte_produkteinteilungen_id` FOREIGN KEY (`produkteinteilungen_id`) REFERENCES `produkteinteilungen` (`id`);

--
-- Constraints for table `produkteinteilungen`
--
ALTER TABLE `produkteinteilungen`
  ADD CONSTRAINT `fk_produkteinteilungen_produktkategorien_id` FOREIGN KEY (`produktkategorien_id`) REFERENCES `produktkategorien` (`id`);

--
-- Constraints for table `produkte_eigenschaften`
--
ALTER TABLE `produkte_eigenschaften`
  ADD CONSTRAINT `fk_produkte_eigenschaften_eigenschaften_id` FOREIGN KEY (`eigenschaften_id`) REFERENCES `eigenschaften` (`id`),
  ADD CONSTRAINT `fk_produkte_eigenschaften_produkte_id` FOREIGN KEY (`produkte_id`) REFERENCES `produkte` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `produktkategorien`
--
ALTER TABLE `produktkategorien`
  ADD CONSTRAINT `fk_produktkategorien_drucker_id_level_1` FOREIGN KEY (`drucker_id_level_1`) REFERENCES `drucker` (`id`),
  ADD CONSTRAINT `fk_produktkategorien_produktbereiche_id` FOREIGN KEY (`produktbereiche_id`) REFERENCES `produktbereiche` (`id`);

--
-- Constraints for table `produktkategorien_eigenschaften`
--
ALTER TABLE `produktkategorien_eigenschaften`
  ADD CONSTRAINT `fk_produktkategorien_eigenschaften_eigenschaften_id` FOREIGN KEY (`eigenschaften_id`) REFERENCES `eigenschaften` (`id`),
  ADD CONSTRAINT `fk_produktkategorien_eigenschaften_produktkategorien_id` FOREIGN KEY (`produktkategorien_id`) REFERENCES `produktkategorien` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
