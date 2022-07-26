-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 26, 2022 at 08:25 PM
-- Server version: 10.4.21-MariaDB
-- PHP Version: 8.0.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

--
-- Database: `ffgbsy`
--

--
-- Dumping data for table `aufnehmer`
--

INSERT INTO `aufnehmer` (`id`, `vorname`, `nachname`, `aktiv`, `zoom_level`) VALUES
(1, 'Jakob', 'Vesely', 1, 1),
(2, 'Matthias', 'Hintenberger', 1, 1),
(3, 'Michael', 'Redl', 1, 1),
(4, 'Anne', 'Pfiel', 1, 3),
(5, 'Andreas', 'Strohmayer', 1, 3),
(6, 'Elisabeth', 'Marik', 1, 3),
(7, 'Heinrich', 'Redl jun.', 1, 1),
(8, 'Heinrich', 'Redl sen.', 1, 2),
(9, 'Kathrin', 'Redl-Swift', 0, 0),
(10, 'Kathrin', 'Strohmayer', 0, 1),
(11, 'Kathrin', 'Bauer', 1, 2),
(12, 'Marlene', 'Kaller', 1, 3),
(13, 'Bernhard', 'Feuchtinger', 0, 1),
(14, 'Andreas', 'Pfiel', 1, 3),
(15, 'Marcel', 'Czech', 1, 1);

--
-- Dumping data for table `constants`
--

INSERT INTO `constants` (`name`, `value`) VALUES
('event_date', 'FF Fest Gemeinlebarn'),
('event_name', '13. bis 15. August 2022'),
('organisation_address', 'Ortsstraße 10, 3133 Gemeinlebarn'),
('organisation_email', 'gemeinlebarn@feuerwehr.gv.at'),
('organisation_name', 'Freiwillige Feuerwehr Gemeinlebarn');

--
-- Dumping data for table `drucker`
--

INSERT INTO `drucker` (`id`, `name`, `ip`, `port`) VALUES
(1, 'Schank', '192.168.0.101', 9100),
(2, 'Grillhütte I', '192.168.0.102', 9100),
(3, 'Grillhütte II', '192.168.0.103', 9100),
(4, 'Hendlhütte', '192.168.0.104', 9100),
(5, 'Mehlspeishütte', '192.168.0.105', 9100);

--
-- Dumping data for table `eigenschaften`
--

INSERT INTO `eigenschaften` (`id`, `name`, `preis`, `sortierindex`) VALUES
(1, 'Senf', '0.00', 10),
(2, 'Ketchup', '0.00', 20),
(3, 'Krautsalat', '0.00', 30),
(4, 'Pommes', '1.00', 40),
(5, 'Semmel', '0.70', 50),
(6, 'Zusätzlicher Teller', '0.00', 220),
(7, 'zum Mitnehmen', '0.00', 300),
(8, 'Strohalm', '0.00', 80),
(9, 'Zusätzliches 1/4l Wasserglas', '0.00', 90),
(10, 'Zusätzliches 1/8l Wasserglas', '0.00', 100),
(11, 'Zusätzliches 1/8l Weinglas', '0.00', 110),
(12, 'warm/von Herausen', '0.00', 120),
(13, 'Letscho', '0.00', 32),
(14, 'Milch', '0.00', 140),
(15, 'Zuckerpackung', '0.00', 150),
(16, 'Schinken', '0.00', 160),
(17, 'Käse', '0.00', 170),
(18, 'Semmelknödel', '1.00', 52),
(19, 'Portion Pommes', '2.00', 190),
(20, 'Portion Krautsalat', '1.60', 200),
(21, 'Portion Letscho', '1.60', 210),
(22, 'Zusätzliches Besteck', '0.00', 221),
(23, 'Zusätzliche Semmel', '0.70', 180);

--
-- Dumping data for table `grundprodukte`
--

INSERT INTO `grundprodukte` (`id`, `name`, `bestand`, `einheit`) VALUES
(1, 'Grillhendl', 0, 'Portion(en)'),
(2, 'Kotelett', NULL, 'Stück'),
(3, 'Spieß', 7, 'Portion(en)'),
(4, 'Grillwurst', 14, 'Portion(en)'),
(5, 'Champignonsauce', NULL, 'Portion(en)'),
(6, 'Krautfleisch', NULL, 'Portion(en)');

--
-- Dumping data for table `produktbereiche`
--

INSERT INTO `produktbereiche` (`id`, `name`, `farbe`, `drucker_id_level_0`) VALUES
(1, 'Getränke', '#FFB700', 1),
(2, 'Speisen', '#0772A1', NULL),
(3, 'Mehlspeisen & Kaffee', '#FF3100', 5);

--
-- Dumping data for table `produkteinteilungen`
--

INSERT INTO `produkteinteilungen` (`id`, `name`, `sortierindex`) VALUES
(1, 'Krügerl', 10),
(2, 'Seidel', 20),
(3, 'Radler und Alkoholfrei', 30),
(4, 'Gespritzer', 40),
(5, 'Kaisergespritzer', 50),
(6, 'Wein pur', 60),
(7, 'Cola mit Wein', 70),
(8, 'Almdudler mit Wein', 80),
(9, 'Frucade mit Wein', 90),
(10, 'Apfelsaft', 100),
(11, 'Traubensaft', 110),
(12, 'Hollersaft', 120),
(13, 'Limonaden in Flaschen', 130),
(14, 'Soda', 140),
(15, 'Mineral', 150),
(16, 'Leitungswasser', 160),
(17, 'Almdudler', 170),
(18, 'Frucade', 180),
(19, 'Cola', 190),
(20, 'Kotelett', 200),
(21, 'Putenspieß', 210),
(22, 'Grillwürste', 220),
(23, 'Pommes', 265),
(24, 'Extra Portionen', 267),
(25, 'Grillhendl', 250),
(26, 'Champignonsauce und Krautfleisch', 260),
(27, 'Kaffee', 270),
(28, 'Mehlspeisen', 280),
(29, 'Toasts', 290);

--
-- Dumping data for table `produktkategorien`
--

INSERT INTO `produktkategorien` (`id`, `name`, `color`, `produktbereiche_id`, `drucker_id_level_1`, `sortierindex`) VALUES
(1, 'Bier', '#5693fc', 1, 1, 10),
(2, 'Wein weiß', '#5693fc', 1, 1, 20),
(3, 'Wein rot', '#5693fc', 1, 1, 30),
(4, 'Säfte', '#5693fc', 1, 1, 40),
(5, 'Limo & Wasser', '#5693fc', 1, 1, 50),
(6, 'Limo gespritzt', '#5693fc', 1, 1, 60),
(7, 'Kaffee & Toast', '#176bff', 3, 5, 120),
(8, 'Kotelett, Spieß & Würstel', '#3880ff', 2, 2, 70),
(10, 'Hendl & Champignon', '#2675ff', 2, 4, 100);

--
-- Dumping data for table `produkte`
--

INSERT INTO `produkte` (`id`, `name`, `formal_name`, `einzahl`, `einheit`, `preis`, `produktkategorien_id`, `drucker_id_level_2`, `aktiv`, `sortierindex`, `produkteinteilungen_id`, `grundprodukte_id`, `grundprodukte_multiplikator`) VALUES
(1, 'Seidel Bier', '0,33l Bier', NULL, NULL, '2.80', 1, NULL, 1, 10, 2, NULL, NULL),
(2, 'Seidel gemischtes Bier', '0,33l gemischtes Bier', NULL, NULL, '2.80', 1, NULL, 1, 20, 2, NULL, NULL),
(3, 'Krügerl Bier', '0,5l Bier', NULL, NULL, '3.30', 1, NULL, 1, 30, 1, NULL, NULL),
(4, 'Krügerl gemischtes Bier', '0,5l gemischtes Bier', NULL, NULL, '3.30', 1, NULL, 1, 40, 1, NULL, NULL),
(5, 'Flasche Radler', '0,5l Radler in Flasche', NULL, NULL, '3.30', 1, NULL, 1, 50, 3, NULL, NULL),
(6, 'Gespritzter weiß', '1/4l Gespritzter weiß', NULL, NULL, '1.60', 2, NULL, 1, 60, 4, NULL, NULL),
(7, 'Großer Gespritzter weiß', '1/2l Gespritzter weiß', NULL, NULL, '3.20', 2, NULL, 1, 70, 4, NULL, NULL),
(8, '(Großer) Wüstenspritzer', '1/2l Soda mit 1/8 weiß', NULL, NULL, '2.30', 2, NULL, 1, 80, 4, NULL, NULL),
(9, 'Kaisergespritzter', '1/4l Gespritzter weiß mit Hollersaft', NULL, NULL, '1.80', 2, NULL, 1, 90, 5, NULL, NULL),
(10, 'Großer Kaisergespritzter', '1/2l Gespritzter weiß mit Hollersaft', NULL, NULL, '3.60', 2, NULL, 1, 100, 5, NULL, NULL),
(11, '1/8l Wein weiß', NULL, NULL, NULL, '1.30', 2, NULL, 1, 110, 6, NULL, NULL),
(12, '1/4l Wein weiß', NULL, NULL, NULL, '2.60', 2, NULL, 1, 120, 6, NULL, NULL),
(13, '1/2l Wein weiß', NULL, NULL, NULL, '5.20', 2, NULL, 1, 130, 6, NULL, NULL),
(14, '1l Wein weiß', NULL, NULL, NULL, '10.40', 2, NULL, 1, 140, 6, NULL, NULL),
(15, 'Kleines Cola weiß', '1/4l Cola mit Wein weiß', NULL, NULL, '1.90', 2, NULL, 1, 150, 7, NULL, NULL),
(16, 'Großes Cola weiß', '1/2l Cola mit Wein weiß', NULL, NULL, '3.80', 2, NULL, 1, 160, 7, NULL, NULL),
(17, 'Kleines Alm weiß', '1/4l Almdudler mit Wein weiß', NULL, NULL, '1.90', 2, NULL, 1, 170, 8, NULL, NULL),
(18, 'Großes Alm weiß', '1/2l Almdudler mit Wein weiß', NULL, NULL, '3.80', 2, NULL, 1, 180, 8, NULL, NULL),
(19, 'Großer Almdudler + 1/8l weiß', '1/2l Almdudler + 1/8l weiß', NULL, NULL, '3.10', 2, NULL, 1, 190, 8, NULL, NULL),
(20, 'Kleines Frucade weiß', '1/4l Frucade mit Wein weiß', NULL, NULL, '1.90', 2, NULL, 1, 200, 9, NULL, NULL),
(21, 'Großes Frucade weiß', '1/2l Frucade mit Wein weiß', NULL, NULL, '3.80', 2, NULL, 1, 210, 9, NULL, NULL),
(22, 'Großes Frucade + 1/8l weiß', '1/2l Frucade + 1/8l weiß', NULL, NULL, '3.10', 2, NULL, 1, 220, 9, NULL, NULL),
(23, 'Gespritzter rot', '1/4l Gespritzer rot', NULL, NULL, '1.60', 3, NULL, 1, 230, 4, NULL, NULL),
(24, 'Großer Gespritzter rot', '1/2l Gespritzer rot', NULL, NULL, '3.20', 3, NULL, 1, 240, 4, NULL, NULL),
(25, '(Großer) Wüstenspritzer rot', '1/2l Soda mit 1/8 rot', NULL, NULL, '2.30', 3, NULL, 1, 250, 4, NULL, NULL),
(26, '1/8l Wein rot', NULL, NULL, NULL, '1.30', 3, NULL, 1, 260, 6, NULL, NULL),
(27, '1/4l Wein rot', NULL, NULL, NULL, '2.60', 3, NULL, 1, 270, 6, NULL, NULL),
(28, '1/2l Wein rot', NULL, NULL, NULL, '5.20', 3, NULL, 1, 280, 6, NULL, NULL),
(29, '1l Wein rot', NULL, NULL, NULL, '10.40', 3, NULL, 1, 290, 6, NULL, NULL),
(30, 'Kleines Cola rot', '1/4l Cola rot', NULL, NULL, '1.90', 3, NULL, 1, 300, 7, NULL, NULL),
(31, 'Großes Cola rot', '1/2l Cola rot', NULL, NULL, '3.80', 3, NULL, 1, 310, 7, NULL, NULL),
(32, 'Kleines Alm rot', '1/4l Almudler rot', NULL, NULL, '1.90', 3, NULL, 1, 320, 8, NULL, NULL),
(33, 'Großes Alm rot', '1/2l Almudler rot', NULL, NULL, '3.80', 3, NULL, 1, 330, 8, NULL, NULL),
(34, 'Großer Almdudler mit 1/8l rot', '1/2l Almudler + 1/8l rot', NULL, NULL, '3.10', 3, NULL, 1, 340, 8, NULL, NULL),
(35, 'Kleines Frucade rot', '1/4l Frucade rot', NULL, NULL, '1.90', 3, NULL, 1, 350, 9, NULL, NULL),
(36, 'Großes Frucade rot', '1/2l Frucade rot', NULL, NULL, '3.80', 3, NULL, 1, 360, 9, NULL, NULL),
(37, 'Großes Frucade + 1/8l rot', '1/2l Frucade + 1/8l rot', NULL, NULL, '3.10', 3, NULL, 1, 370, 9, NULL, NULL),
(38, 'Kleiner Apfelsaft mit LW', '1/4l Apfelsaft mit Leitungswasser', NULL, NULL, '0.90', 4, NULL, 1, 380, 10, NULL, NULL),
(39, 'Großer Apfelsaft mit LW', '1/2l Apfelsaft mit Leitungswasser', NULL, NULL, '1.80', 4, NULL, 1, 390, 10, NULL, NULL),
(40, 'Kleiner Apfelsaft gespritzt', '1/4l Apfelsaft mit Soda', NULL, NULL, '1.40', 4, NULL, 1, 400, 10, NULL, NULL),
(41, 'Großer Apfelsaft gespritzt', '1/2l Apfelsaft mit Soda', NULL, NULL, '2.70', 4, NULL, 1, 410, 10, NULL, NULL),
(42, 'Großes LW + 1/8 Apfelsaft', '1/2l Leitungswasser + 1/8l Apfelsaft', NULL, NULL, '0.90', 4, NULL, 1, 420, 10, NULL, NULL),
(43, 'Großes Soda + 1/8 Apfelsaft', '1/2l Soda + 1/8l Apfelsaft', NULL, NULL, '2.40', 4, NULL, 1, 430, 10, NULL, NULL),
(44, 'Kleiner Traubensaft mit LW', '1/4l Traubensaft mit LW', NULL, NULL, '0.90', 4, NULL, 1, 440, 11, NULL, NULL),
(45, 'Großer Traubensaft mit LW', '1/2l Traubensaft mit LW', NULL, NULL, '1.80', 4, NULL, 1, 450, 11, NULL, NULL),
(46, 'Kleiner Traubensaft gespritzt', '1/4l Traubensaft mit Soda', NULL, NULL, '1.40', 4, NULL, 1, 460, 11, NULL, NULL),
(47, 'Großer Traubensaft gespritzt', '1/2l Traubensaft mit Soda', NULL, NULL, '2.70', 4, NULL, 1, 470, 11, NULL, NULL),
(48, 'Großes LW + 1/8 Traubensaft', '1/2l Leitungswasser + 1/8l Traubensaft', NULL, NULL, '0.90', 4, NULL, 1, 480, 11, NULL, NULL),
(49, 'Großes Soda + 1/8 Traubensaft', '1/2l Soda + 1/8 Traubensaft', NULL, NULL, '2.40', 4, NULL, 1, 490, 11, NULL, NULL),
(50, 'Kleiner Hollersaft gespritzt', '1/4l Hollersaft mit Soda', NULL, NULL, '1.40', 4, NULL, 1, 500, 12, NULL, NULL),
(51, 'Großer Hollersaft gespritzt', '1/2l Hollersaft mit Soda', NULL, NULL, '2.70', 4, NULL, 1, 510, 12, NULL, NULL),
(52, 'Kleiner Hollersaft mit LW', '1/4l Holler mit LW', NULL, NULL, '0.90', 4, NULL, 1, 520, 12, NULL, NULL),
(53, 'Großer Hollersaft mit LW', '1/2l Holler mit LW', NULL, NULL, '1.80', 4, NULL, 1, 530, 12, NULL, NULL),
(54, '1/4l Apfelsaft pur', '1/4l Apfelsaft pur', NULL, NULL, '1.80', 4, NULL, 1, 540, 10, NULL, NULL),
(55, '1/4l Traubensaft pur', '1/4l Traubensaft pur', NULL, NULL, '1.80', 4, NULL, 1, 550, 11, NULL, NULL),
(56, 'Flasche Almdudler', 'Flasche Almdudler', NULL, NULL, '1.90', 5, NULL, 1, 560, 13, NULL, NULL),
(57, 'Flasche Frucade', 'Flasche Frucade', NULL, NULL, '1.90', 5, NULL, 1, 570, 13, NULL, NULL),
(58, 'Flasche Cola', 'Flasche Cola', NULL, NULL, '1.90', 5, NULL, 1, 580, 13, NULL, NULL),
(59, 'Kleines Soda', '1/4l Soda', NULL, NULL, '1.00', 5, NULL, 1, 590, 14, NULL, NULL),
(60, 'Großes Soda', '1/2l Soda', NULL, NULL, '2.00', 5, NULL, 1, 600, 14, NULL, NULL),
(61, '1l Soda', '1l Soda', NULL, NULL, '4.00', 5, NULL, 1, 610, 14, NULL, NULL),
(62, '1l Flasche Mineral (groß)', '1l Flasche Mineral (groß)', NULL, NULL, '2.70', 5, NULL, 1, 620, 15, NULL, NULL),
(63, '0,33l Flasche Mineral (klein)', '0,33l Flasche Mineral (klein)', NULL, NULL, '1.50', 5, NULL, 1, 630, 15, NULL, NULL),
(64, 'Kleines Leitungswasser', '1/4l Leitungswasser', NULL, NULL, '0.00', 5, NULL, 1, 640, 16, NULL, NULL),
(65, 'Großes Leitungswasser', '1/2l Leitungswasser', NULL, NULL, '0.00', 5, NULL, 1, 650, 16, NULL, NULL),
(66, '1l Leitungswasser', '1l Leitungswasser', NULL, NULL, '0.00', 5, NULL, 1, 660, 16, NULL, NULL),
(67, 'Kleiner Almdudler gespritzt', '1/4l Almdudler mit Soda', NULL, NULL, '1.40', 6, NULL, 1, 670, 17, NULL, NULL),
(68, 'Großer Almdudler gespritzt', '1/2l Almdudler mit Soda', NULL, NULL, '2.70', 6, NULL, 1, 680, 17, NULL, NULL),
(69, 'Kleiner Almdudler mit LW', '1/4l Almdudler mit LW', NULL, NULL, '1.00', 6, NULL, 1, 690, 17, NULL, NULL),
(70, 'Großer Almdudler mit LW', '1/2l Almdudler mit LW', NULL, NULL, '1.90', 6, NULL, 1, 700, 17, NULL, NULL),
(71, 'Großes LW + 1/8 Almdudler', '1/2l + 1/8 Almdudler LW', NULL, NULL, '1.00', 6, NULL, 1, 710, 17, NULL, NULL),
(72, 'Großes Soda + 1/8 Almdudler', '1/2l + 1/8 Almdudler Soda', NULL, NULL, '2.40', 6, NULL, 1, 720, 17, NULL, NULL),
(73, 'Kleines Frucade gespritzt', '1/4l Frucade mit Soda', NULL, NULL, '1.40', 6, NULL, 1, 730, 18, 0, NULL),
(74, 'Großes Frucade gespritzt', '1/2l Frucade mit Soda', NULL, NULL, '2.70', 6, NULL, 1, 740, 18, NULL, NULL),
(75, 'Kleines Frucade mit LW', '1/4l Frucade mit LW', NULL, NULL, '1.00', 6, NULL, 1, 750, 18, NULL, NULL),
(76, 'Großes Frucade mit LW', '1/2l Frucade mit LW', NULL, NULL, '1.90', 6, NULL, 1, 760, 18, NULL, NULL),
(77, 'Großes LW + 1/8l Frucade', '1/2l + 1/8 Frucade LW', NULL, NULL, '1.00', 6, NULL, 1, 770, 18, NULL, NULL),
(78, 'Großes Soda + 1/8 Frucade', '1/2l + 1/8 Frucade Soda', NULL, NULL, '2.40', 6, NULL, 1, 780, 18, NULL, NULL),
(79, 'Kleines Cola gespritzt', '1/4l Cola mit Soda', NULL, NULL, '1.40', 6, NULL, 1, 790, 19, NULL, NULL),
(80, 'Großes Cola gespritzt', '1/2l Cola mit Soda', NULL, NULL, '2.70', 6, NULL, 1, 800, 19, NULL, NULL),
(81, 'Kleines Cola mit LW', '1/4l Cola mit LW', NULL, NULL, '1.00', 6, NULL, 1, 810, 19, NULL, NULL),
(82, 'Großes Cola mit LW', '1/2l Cola mit LW', NULL, NULL, '1.90', 6, NULL, 1, 820, 19, NULL, NULL),
(83, 'Großes LW + 1/8l Cola', '1/2l + 1/8 Cola LW', NULL, NULL, '1.00', 6, NULL, 1, 830, 19, NULL, NULL),
(84, 'Großes Soda + 1/8l Cola', '1/2l + 1/8 Cola Soda', NULL, NULL, '2.40', 6, NULL, 1, 840, 19, NULL, NULL),
(85, 'Kaffee mit Milch', 'Kaffee mit Milch mit Zuckerpackung als Beilage', NULL, NULL, '2.20', 7, NULL, 1, 850, 27, NULL, NULL),
(86, 'Kaffee ohne Milch', 'Kaffee ohne Milch mit Zuckerpackung als Beilage', NULL, NULL, '2.20', 7, NULL, 1, 860, 27, NULL, NULL),
(87, 'Mehlspeise', 'Portion Mehlspeise', NULL, NULL, '2.20', 7, NULL, 1, 870, 28, NULL, NULL),
(88, 'Gemischter Toast', NULL, NULL, NULL, '2.60', 7, NULL, 1, 880, 29, NULL, NULL),
(92, 'Grillkotelett', 'Grillkotelett mit Pommes und Salat', NULL, NULL, '7.10', 8, 3, 1, 920, 20, 2, 2),
(93, 'Zigeunerkotelett', 'Zigeunerkotelett mit Pommes und Letscho', NULL, NULL, '7.10', 8, 3, 1, 930, 20, 2, 2),
(94, 'Grillwürstel mit Gebäck', 'Grillwürstel mit Gebäck und Senf', NULL, NULL, '3.50', 8, 3, 1, 940, 22, 4, 1),
(95, 'Grillwürstel mit Pommes', 'Grillwürstel mit einer Portion (!) Pommes', NULL, NULL, '4.80', 8, 3, 1, 950, 22, 4, 1),
(96, 'Portion Pommes', 'Portion Pommes mit Ketchup', NULL, NULL, '2.00', 8, NULL, 1, 960, 23, NULL, NULL),
(98, 'Kotelettsemmel', 'Kotelettsemmel mit Krautsalat, Ketchup und Senf', NULL, NULL, '3.30', 8, 3, 1, 980, 20, 2, 1),
(108, 'Putenspieß', 'Putenspieß mit Pommes und Krautsalat', NULL, NULL, '7.10', 8, NULL, 1, 1080, 21, 3, 1),
(109, 'Räuberspieß', 'Putenspieß mit Pommes und Letscho', NULL, NULL, '7.10', 8, NULL, 1, 1090, 21, 3, 1),
(114, 'Grillhendl mit Gebäck ', 'Grillhendl mit Gebäck', NULL, NULL, '6.50', 10, NULL, 1, 1140, 25, 1, 1),
(115, 'Grillhendl mit Pommes ', 'Grillhendl mit Pommes ', NULL, NULL, '7.10', 10, NULL, 1, 1150, 25, 1, 1),
(117, 'Champignonsauce mit Knödel', NULL, NULL, NULL, '6.20', 10, NULL, 1, 1170, 26, 5, 1),
(133, 'Portion Senf', NULL, NULL, NULL, '0.00', 8, NULL, 1, 1370, 24, NULL, NULL),
(134, 'Portion Ketchup', NULL, NULL, NULL, '0.00', 8, NULL, 1, 1380, 24, NULL, NULL),
(135, 'Portion Letscho', NULL, NULL, NULL, '1.60', 8, NULL, 1, 1390, 24, NULL, NULL),
(136, 'Portion Krautsalat', NULL, NULL, NULL, '1.60', 8, NULL, 1, 1400, 24, NULL, NULL),
(143, '1/8l Apfelsaft pur', '1/8l Apfelsaft pur', NULL, NULL, '0.90', 4, NULL, 1, 539, 10, NULL, NULL),
(144, '1/8l Traubensaft pur', '1/8l Traubensaft pur', NULL, NULL, '0.90', 4, NULL, 1, 549, 11, NULL, NULL),
(146, 'Flasche alkoholfreies Bier', '0,5l alkoholfreies Bier', NULL, NULL, '3.30', 1, NULL, 1, 51, 3, NULL, NULL),
(147, 'Krautfleisch mit Gebäck', 'Krautfleisch mit Gebäck', NULL, NULL, '6.20', 10, NULL, 1, 1175, 26, 6, 1),
(148, 'Großes Cola mit 1/8l weiß', '1/2l Cola + 1/8l weiß', NULL, NULL, '3.10', 2, NULL, 1, 162, 7, NULL, NULL),
(149, 'Großes Cola mit 1/8l rot', '1/2l Cola mit 1/8l rot', NULL, NULL, '3.10', 3, NULL, 1, 312, 7, NULL, NULL),
(150, '0,33l Flasche STILLES Mineral', '0,33l Flasche STILLES Mineral', NULL, NULL, '1.50', 5, NULL, 1, 632, 15, NULL, NULL),
(151, 'Semmel', NULL, NULL, NULL, '0.70', 8, NULL, 1, 2000, 24, NULL, NULL);

--
-- Dumping data for table `produkte_eigenschaften`
--

INSERT INTO `produkte_eigenschaften` (`produkte_id`, `eigenschaften_id`, `in_produkt_enthalten`) VALUES
(85, 14, 1),
(85, 15, 1),
(86, 14, 0),
(86, 15, 1),
(87, 6, 0),
(87, 7, 0),
(88, 1, 0),
(88, 2, 0),
(88, 3, 0),
(88, 6, 0),
(88, 7, 0),
(88, 16, 1),
(88, 17, 1),
(88, 22, 0),
(92, 1, 0),
(92, 2, 1),
(92, 3, 1),
(92, 4, 1),
(93, 1, 0),
(93, 2, 0),
(93, 4, 1),
(93, 13, 1),
(94, 1, 1),
(94, 2, 0),
(94, 5, 1),
(95, 1, 1),
(95, 2, 1),
(95, 19, 1),
(96, 1, 0),
(96, 2, 0),
(96, 3, 0),
(96, 13, 0),
(98, 1, 0),
(98, 2, 0),
(98, 3, 0),
(108, 2, 1),
(108, 3, 1),
(108, 4, 1),
(109, 1, 0),
(109, 2, 0),
(109, 4, 1),
(109, 13, 1),
(114, 1, 0),
(114, 2, 0),
(114, 5, 1),
(115, 1, 0),
(115, 2, 1),
(115, 4, 1),
(117, 1, 0),
(117, 2, 0),
(117, 4, 0),
(117, 5, 0),
(117, 18, 1),
(147, 1, 0),
(147, 2, 0),
(147, 3, 0),
(147, 5, 1),
(147, 13, 0);

--
-- Dumping data for table `produktkategorien_eigenschaften`
--

INSERT INTO `produktkategorien_eigenschaften` (`produktkategorien_id`, `eigenschaften_id`, `in_produkt_enthalten`) VALUES
(1, 9, 0),
(1, 10, 0),
(1, 11, 0),
(1, 12, 0),
(2, 9, 0),
(2, 10, 0),
(2, 11, 0),
(2, 12, 0),
(3, 9, 0),
(3, 10, 0),
(3, 11, 0),
(3, 12, 0),
(4, 9, 0),
(4, 10, 0),
(4, 11, 0),
(4, 12, 0),
(5, 9, 0),
(5, 10, 0),
(5, 11, 0),
(5, 12, 0),
(6, 9, 0),
(6, 10, 0),
(6, 11, 0),
(6, 12, 0),
(8, 6, 0),
(8, 7, 0),
(8, 19, 0),
(8, 20, 0),
(8, 21, 0),
(8, 22, 0),
(8, 23, 0),
(10, 6, 0),
(10, 7, 0),
(10, 19, 0),
(10, 20, 0),
(10, 21, 0),
(10, 22, 0),
(10, 23, 0);

--
-- Dumping data for table `tischkategorien`
--

INSERT INTO `tischkategorien` (`id`, `name`, `sortierindex`) VALUES
(1, 'Reihe A', 10),
(2, 'Reihe B', 20),
(3, 'Reihe C', 30),
(4, 'Reihe D', 40),
(5, 'Reihe E', 50),
(6, 'Reihe F', 60),
(7, 'Reihe G', 70),
(8, 'Reihe H', 80),
(9, 'Reihe I', 90),
(10, 'Reihe K', 100),
(11, 'FF-Haus', 110),
(12, 'Sonstige', 120);
COMMIT;

--
-- Dumping data for table `tische`
--

INSERT INTO `tische` (`id`, `reihe`, `nummer`, `tischkategorien_id`, `sortierindex`) VALUES
(1, 'A', 1, 1, 10),
(2, 'A', 2, 1, 20),
(3, 'A', 3, 1, 30),
(4, 'A', 4, 1, 40),
(5, 'A', 5, 1, 50),
(6, 'A', 6, 1, 60),
(7, 'A', 7, 1, 70),
(8, 'A', 8, 1, 80),
(9, 'A', 9, 1, 90),
(10, 'A', 10, 1, 100),
(11, 'A', 11, 1, 110),
(12, 'A', 12, 1, 120),
(13, 'B', 1, 2, 130),
(14, 'B', 2, 2, 140),
(15, 'B', 3, 2, 150),
(16, 'B', 4, 2, 160),
(17, 'B', 5, 2, 170),
(18, 'B', 7, 2, 180),
(19, 'B', 8, 2, 190),
(20, 'B', 9, 2, 200),
(21, 'B', 10, 2, 210),
(22, 'B', 11, 2, 220),
(23, 'B', 12, 2, 230),
(24, 'C', 1, 3, 240),
(25, 'C', 2, 3, 250),
(26, 'C', 3, 3, 260),
(27, 'C', 4, 3, 270),
(28, 'C', 5, 3, 280),
(29, 'C', 7, 3, 290),
(30, 'C', 8, 3, 300),
(31, 'C', 9, 3, 310),
(32, 'C', 10, 3, 320),
(33, 'C', 11, 3, 330),
(34, 'C', 12, 3, 340),
(35, 'D', 1, 4, 350),
(36, 'D', 2, 4, 360),
(37, 'D', 3, 4, 370),
(38, 'D', 4, 4, 380),
(39, 'D', 5, 4, 390),
(40, 'E', 1, 5, 400),
(41, 'E', 2, 5, 410),
(42, 'E', 3, 5, 420),
(43, 'E', 4, 5, 430),
(44, 'E', 5, 5, 440),
(45, 'F', 1, 6, 450),
(46, 'F', 2, 6, 460),
(47, 'F', 3, 6, 470),
(48, 'F', 4, 6, 480),
(49, 'F', 5, 6, 490),
(50, 'G', 1, 7, 500),
(51, 'G', 2, 7, 510),
(52, 'G', 3, 7, 520),
(53, 'G', 4, 7, 530),
(54, 'G', 5, 7, 540),
(55, 'G', 6, 7, 550),
(56, 'G', 8, 7, 560),
(57, 'G', 9, 7, 570),
(58, 'G', 10, 7, 580),
(59, 'G', 11, 7, 590),
(60, 'H', 1, 8, 600),
(61, 'H', 2, 8, 610),
(62, 'H', 3, 8, 620),
(63, 'H', 4, 8, 630),
(64, 'H', 5, 8, 640),
(65, 'H', 6, 8, 650),
(66, 'H', 8, 8, 660),
(67, 'H', 9, 8, 670),
(68, 'H', 10, 8, 680),
(69, 'H', 11, 8, 690),
(70, 'H', 20, 8, 700),
(71, 'H', 21, 8, 710),
(72, 'H', 22, 8, 720),
(73, 'H', 23, 8, 730),
(74, 'H', 24, 8, 740),
(76, 'i', 1, 9, 760),
(77, 'i', 2, 9, 770),
(78, 'i', 3, 9, 780),
(79, 'i', 4, 9, 790),
(80, 'i', 5, 9, 800),
(81, 'i', 6, 9, 810),
(82, 'i', 7, 9, 820),
(83, 'i', 8, 9, 830),
(84, 'i', 9, 9, 840),
(85, 'i', 10, 9, 850),
(86, 'i', 11, 9, 860),
(87, 'i', 12, 9, 870),
(88, 'i', 13, 9, 880),
(89, 'i', 14, 9, 890),
(90, 'i', 15, 9, 900),
(91, 'i', 16, 9, 910),
(92, 'i', 17, 9, 920),
(93, 'i', 18, 9, 930),
(94, 'i', 19, 9, 940),
(95, 'i', 20, 9, 950),
(96, 'i', 21, 9, 960),
(97, 'i', 22, 9, 970),
(98, 'i', 23, 9, 980),
(102, 'K', 1, 10, 1020),
(103, 'K', 2, 10, 1030),
(104, 'K', 3, 10, 1040),
(105, 'K', 4, 10, 1050),
(106, 'K', 5, 10, 1060),
(107, 'K', 6, 10, 1070),
(108, 'K', 7, 10, 1080),
(109, 'K', 8, 10, 1090),
(110, 'K', 9, 10, 1100),
(111, 'K', 10, 10, 1110),
(112, 'FA', 1, 11, 1120),
(113, 'FA', 2, 11, 1130),
(114, 'FA', 3, 11, 1140),
(115, 'FA', 4, 11, 1150),
(116, 'FA', 5, 11, 1160),
(117, 'FB', 1, 11, 1170),
(118, 'FB', 2, 11, 1180),
(119, 'FB', 3, 11, 1190),
(120, 'FB', 4, 11, 1200),
(121, 'FB', 5, 11, 1210),
(122, 'FC', 1, 11, 1220),
(123, 'FC', 2, 11, 1230),
(124, 'FC', 3, 11, 1240),
(125, 'FC', 4, 11, 1250),
(126, 'FC', 5, 11, 1260),
(127, 'Schankbereich', NULL, 11, 1270),
(128, 'Mehlspeishütte', NULL, 12, 1280),
(129, 'Essensausgabebereich', NULL, 12, 1290),
(130, 'Achtlbar', NULL, 12, 1300),
(131, 'Bar', NULL, 12, 1310),
(132, 'H', 19, 8, 695),
(133, 'Bühne (Musik)', NULL, 12, 1320),
(134, 'G', 7, 7, 555),
(135, 'H', 7, 8, 655);
