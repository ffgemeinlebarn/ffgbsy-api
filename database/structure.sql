-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 26, 2022 at 08:21 PM
-- Server version: 10.4.21-MariaDB
-- PHP Version: 8.0.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

--
-- Database: `ffgbsy`
--

-- --------------------------------------------------------

--
-- Table structure for table `constants`
--

CREATE TABLE `constants` (
  `name` varchar(50) NOT NULL,
  `value` varchar(300) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `aufnehmer`
--

CREATE TABLE `aufnehmer` (
  `id` int(11) NOT NULL,
  `vorname` varchar(50) DEFAULT NULL,
  `nachname` varchar(50) DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `aktiv` tinyint(1) NOT NULL DEFAULT 0,
  `zoom_level` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `bestellpositionen`
--

CREATE TABLE `bestellpositionen` (
  `id` int(11) NOT NULL,
  `anzahl` int(11) NOT NULL,
  `produkte_id` int(11) NOT NULL,
  `notiz` text DEFAULT NULL,
  `bestellungen_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `bestellpositionen_eigenschaften`
--

CREATE TABLE `bestellpositionen_eigenschaften` (
  `bestellpositionen_id` int(11) NOT NULL,
  `eigenschaften_id` int(11) NOT NULL,
  `in_produkt_enthalten` tinyint(1) DEFAULT NULL,
  `aktiv` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `bestellpositionen_storno`
--

CREATE TABLE `bestellpositionen_storno` (
  `id` int(11) NOT NULL,
  `bestellpositionen_id` int(11) DEFAULT NULL,
  `anzahl` int(11) NOT NULL DEFAULT 1,
  `timestamp` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `bestellungen`
--

CREATE TABLE `bestellungen` (
  `id` int(11) NOT NULL,
  `tische_id` int(11) NOT NULL,
  `timestamp_begonnen` datetime NOT NULL,
  `timestamp_beendet` datetime NOT NULL DEFAULT current_timestamp(),
  `timestamp_gedruckt` datetime DEFAULT NULL,
  `aufnehmer_id` int(11) NOT NULL,
  `ip` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `bons_druck`
--

CREATE TABLE `bons_druck` (
  `id` int(11) NOT NULL,
  `bestellungen_id` int(11) NOT NULL,
  `drucker_id` int(11) NOT NULL,
  `storno_id` int(11) DEFAULT NULL,
  `datum` date NOT NULL,
  `laufnummer` int(11) NOT NULL,
  `timestamp_gedruckt` datetime NOT NULL DEFAULT current_timestamp(),
  `result` tinyint(1) DEFAULT NULL,
  `result_message` varchar(300) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `drucker`
--

CREATE TABLE `drucker` (
  `id` int(11) NOT NULL,
  `name` varchar(30) NOT NULL,
  `ip` varchar(30) NOT NULL,
  `port` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `eigenschaften`
--

CREATE TABLE `eigenschaften` (
  `id` int(11) NOT NULL,
  `name` varchar(30) NOT NULL,
  `preis` decimal(19,2) NOT NULL DEFAULT 0.00,
  `sortierindex` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `grundprodukte`
--

CREATE TABLE `grundprodukte` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `bestand` int(11) DEFAULT NULL,
  `einheit` varchar(30) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `produktbereiche`
--

CREATE TABLE `produktbereiche` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `color` varchar(30) DEFAULT NULL,
  `drucker_id_level_0` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `produkte`
--

CREATE TABLE `produkte` (
  `id` int(11) NOT NULL,
  `name` varchar(30) NOT NULL,
  `formal_name` varchar(50) DEFAULT NULL,
  `einzahl` float DEFAULT NULL,
  `einheit` varchar(50) DEFAULT NULL,
  `preis` decimal(19,2) NOT NULL,
  `drucker_id_level_2` int(11) DEFAULT NULL,
  `aktiv` tinyint(1) NOT NULL DEFAULT 1,
  `sortierindex` int(11) DEFAULT NULL,
  `produkteinteilungen_id` int(11) NOT NULL,
  `grundprodukte_id` int(11) DEFAULT NULL,
  `grundprodukte_multiplikator` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `produkteinteilungen`
--

CREATE TABLE `produkteinteilungen` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `produktkategorien_id` int(11) NOT NULL,
  `sortierindex` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `produkte_eigenschaften`
--

CREATE TABLE `produkte_eigenschaften` (
  `produkte_id` int(11) NOT NULL,
  `eigenschaften_id` int(11) NOT NULL,
  `in_produkt_enthalten` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `produktkategorien`
--

CREATE TABLE `produktkategorien` (
  `id` int(11) NOT NULL,
  `name` varchar(30) NOT NULL,
  `color` varchar(30) NOT NULL,
  `produktbereiche_id` int(11) NOT NULL,
  `drucker_id_level_1` int(11) DEFAULT NULL,
  `sortierindex` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `produktkategorien_eigenschaften`
--

CREATE TABLE `produktkategorien_eigenschaften` (
  `produktkategorien_id` int(11) NOT NULL,
  `eigenschaften_id` int(11) NOT NULL,
  `in_produkt_enthalten` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `tische`
--

CREATE TABLE `tische` (
  `id` int(11) NOT NULL,
  `reihe` varchar(30) NOT NULL,
  `nummer` int(11) DEFAULT NULL,
  `tischkategorien_id` int(11) NOT NULL,
  `sortierindex` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `tischkategorien`
--

CREATE TABLE `tischkategorien` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `sortierindex` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `constants`
--
ALTER TABLE `constants`
  ADD PRIMARY KEY (`name`);

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
  ADD KEY `fk_produkte_id` (`produkte_id`),
  ADD KEY `fk_bestellungen_id` (`bestellungen_id`);

--
-- Indexes for table `bestellpositionen_eigenschaften`
--
ALTER TABLE `bestellpositionen_eigenschaften`
  ADD PRIMARY KEY (`bestellpositionen_id`,`eigenschaften_id`);

--
-- Indexes for table `bestellpositionen_storno`
--
ALTER TABLE `bestellpositionen_storno`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `bestellungen`
--
ALTER TABLE `bestellungen`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_tische_id` (`tische_id`),
  ADD KEY `fk_aufnehmer_id` (`aufnehmer_id`);

--
-- Indexes for table `bons_druck`
--
ALTER TABLE `bons_druck`
  ADD PRIMARY KEY (`id`);

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
-- Indexes for table `produktbereiche`
--
ALTER TABLE `produktbereiche`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_drucker_id` (`drucker_id_level_0`) USING BTREE;

--
-- Indexes for table `produkte`
--
ALTER TABLE `produkte`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_produkteinteilungen_id` (`produkteinteilungen_id`),
  ADD KEY `fk_drucker_id_level_2` (`drucker_id_level_2`),
  ADD KEY `fk_grundprodukte_id` (`grundprodukte_id`);

--
-- Indexes for table `produkteinteilungen`
--
ALTER TABLE `produkteinteilungen`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_produktkategorien_id` (`produktkategorien_id`);

--
-- Indexes for table `produkte_eigenschaften`
--
ALTER TABLE `produkte_eigenschaften`
  ADD PRIMARY KEY (`produkte_id`,`eigenschaften_id`);

--
-- Indexes for table `produktkategorien`
--
ALTER TABLE `produktkategorien`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_produktbereiche_id` (`produktbereiche_id`),
  ADD KEY `fk_drucker_id_level_1` (`drucker_id_level_1`);

--
-- Indexes for table `produktkategorien_eigenschaften`
--
ALTER TABLE `produktkategorien_eigenschaften`
  ADD PRIMARY KEY (`produktkategorien_id`,`eigenschaften_id`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `bestellpositionen`
--
ALTER TABLE `bestellpositionen`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `bestellpositionen_storno`
--
ALTER TABLE `bestellpositionen_storno`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `bestellungen`
--
ALTER TABLE `bestellungen`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `bons_druck`
--
ALTER TABLE `bons_druck`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `drucker`
--
ALTER TABLE `drucker`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `eigenschaften`
--
ALTER TABLE `eigenschaften`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `grundprodukte`
--
ALTER TABLE `grundprodukte`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `produktbereiche`
--
ALTER TABLE `produktbereiche`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `produkte`
--
ALTER TABLE `produkte`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `produkteinteilungen`
--
ALTER TABLE `produkteinteilungen`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `produktkategorien`
--
ALTER TABLE `produktkategorien`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `produktkategorien_eigenschaften`
--
ALTER TABLE `produktkategorien_eigenschaften`
  MODIFY `produktkategorien_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tische`
--
ALTER TABLE `tische`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tischkategorien`
--
ALTER TABLE `tischkategorien`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `produktkategorie`
--
ALTER TABLE `produktkategorien`
  ADD CONSTRAINT `fk_produktbereiche_id` FOREIGN KEY (`produktbereiche_id`) REFERENCES `produktbereiche` (`id`);
COMMIT;

--
-- Constraints for table `produkteinteilungen`
--
ALTER TABLE `produkteinteilungen`
  ADD CONSTRAINT `fk_produktkategorien_id` FOREIGN KEY (`produktkategorien_id`) REFERENCES `produktkategorien` (`id`);
COMMIT;

--
-- Constraints for table `produkte`
--
ALTER TABLE `produkte`
  ADD CONSTRAINT `fk_produkteinteilungen_id` FOREIGN KEY (`produkteinteilungen_id`) REFERENCES `produkteinteilungen` (`id`);
COMMIT;

--
-- Constraints for table `bestellungen`
--
ALTER TABLE `bestellungen`
  ADD CONSTRAINT `fk_aufnehmer_id` FOREIGN KEY (`aufnehmer_id`) REFERENCES `aufnehmer` (`id`),
  ADD CONSTRAINT `fk_tische_id` FOREIGN KEY (`tische_id`) REFERENCES `tische` (`id`);
COMMIT;
