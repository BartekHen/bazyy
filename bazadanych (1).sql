-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sty 22, 2026 at 01:35 AM
-- Wersja serwera: 10.4.32-MariaDB
-- Wersja PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `bazadanych`
--

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `klasa`
--

CREATE TABLE `klasa` (
  `id_klasy` int(11) NOT NULL,
  `stopien` int(11) NOT NULL,
  `nazwa` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `klasa`
--

INSERT INTO `klasa` (`id_klasy`, `stopien`, `nazwa`) VALUES
(1, 1, '1A'),
(2, 2, '2C'),
(3, 3, '3B');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `lekcja`
--

CREATE TABLE `lekcja` (
  `id_lekcji` int(11) NOT NULL,
  `temat` varchar(255) NOT NULL,
  `data` date NOT NULL,
  `id_przedmiot_w_klasie` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lekcja`
--

INSERT INTO `lekcja` (`id_lekcji`, `temat`, `data`, `id_przedmiot_w_klasie`) VALUES
(1, 'ułamki', '2026-01-01', 1),
(2, 'Dodawanie', '2026-01-06', 2),
(3, 'Ułamki', '2026-01-08', 2),
(4, 'Części mowy – powtórzenie', '2026-01-12', 5),
(5, 'Ruch jednostajny', '2026-01-13', 6),
(6, 'Present Simple – ćwiczenia', '2026-01-14', 7),
(7, 'Ułamki dziesiętne', '2026-01-12', 2),
(8, 'Lektura – analiza', '2026-01-13', 4),
(9, 'Electricity – basics', '2026-01-14', 8),
(10, 'Równania liniowe', '2026-01-12', 9),
(11, 'Budowa zdania', '2026-01-13', 10),
(12, 'Siły – wprowadzenie', '2026-01-14', 11),
(13, 'Past Simple – ćwiczenia', '2026-01-15', 12);

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `nauczyciel`
--

CREATE TABLE `nauczyciel` (
  `id_nauczyciela` int(11) NOT NULL,
  `id_uzytkownika` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `nauczyciel`
--

INSERT INTO `nauczyciel` (`id_nauczyciela`, `id_uzytkownika`) VALUES
(2, 4),
(3, 20),
(4, 22),
(5, 23),
(6, 24),
(7, 25);

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `nieobecnosc`
--

CREATE TABLE `nieobecnosc` (
  `id_ucznia` int(11) NOT NULL,
  `id_lekcji` int(11) NOT NULL,
  `usprawiedliwiona` smallint(1) NOT NULL DEFAULT 0,
  `powod` varchar(255) DEFAULT NULL,
  `data_usprawiedliwienia` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `nieobecnosc`
--

INSERT INTO `nieobecnosc` (`id_ucznia`, `id_lekcji`, `usprawiedliwiona`, `powod`, `data_usprawiedliwienia`) VALUES
(1, 1, 0, 'Nieobecny', NULL),
(2, 3, 0, 'Nieobecny', NULL),
(3, 2, 0, 'Nieobecny', NULL),
(4, 4, 0, 'Nieobecny', NULL),
(10, 9, 1, 'Wizyta lekarska', '2026-01-15'),
(14, 12, 0, 'Nieobecny', NULL);

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `ocena`
--

CREATE TABLE `ocena` (
  `id_oceny` int(11) NOT NULL,
  `wartosc` varchar(6) NOT NULL,
  `waga` int(11) NOT NULL,
  `data_wystawienia` date NOT NULL,
  `typ` varchar(255) DEFAULT NULL,
  `id_ucznia` int(11) NOT NULL,
  `id_przedmiot_w_klasie` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ocena`
--

INSERT INTO `ocena` (`id_oceny`, `wartosc`, `waga`, `data_wystawienia`, `typ`, `id_ucznia`, `id_przedmiot_w_klasie`) VALUES
(1, '4', 3, '2026-01-01', 'Kartkówka', 1, 1),
(2, '5', 3, '2026-01-01', 'Kartkówka', 1, 1),
(3, '2', 5, '2026-01-06', 'Sprawdzian (Dodawanie)', 2, 2),
(4, '4', 3, '2026-01-06', 'Kartkówka', 3, 2),
(5, '4', 3, '2026-01-08', 'Kartkówka (Odejmowanie)', 2, 2),
(6, '5', 3, '2026-01-08', 'Aktywność (Mnozenie)', 2, 2),
(7, '4', 5, '2026-01-08', 'Odpowiedź ustna (Mnozenie)', 2, 2),
(8, '6', 5, '2026-01-08', 'Odpowiedź ustna (Mnozenie)', 3, 2),
(9, '5', 3, '2026-01-12', 'Kartkówka (Części mowy)', 1, 5),
(10, '4', 3, '2026-01-12', 'Kartkówka (Części mowy)', 4, 5),
(11, '3', 3, '2026-01-12', 'Kartkówka (Części mowy)', 5, 5),
(12, '6', 5, '2026-01-13', 'Sprawdzian (Ruch)', 6, 6),
(13, '4', 3, '2026-01-14', 'Aktywność (Present)', 7, 7),
(14, '5', 3, '2026-01-12', 'Kartkówka (Ułamki)', 2, 2),
(15, '4', 3, '2026-01-12', 'Kartkówka (Ułamki)', 8, 2),
(16, '3', 3, '2026-01-13', 'Odpowiedź ustna (Lektura)', 9, 4),
(17, '5', 3, '2026-01-14', 'Kartkówka (English)', 10, 8),
(18, '2', 5, '2026-01-14', 'Sprawdzian (English)', 11, 8),
(19, '4', 3, '2026-01-12', 'Kartkówka (Równania)', 12, 9),
(20, '5', 3, '2026-01-13', 'Kartkówka (Zdanie)', 13, 10),
(21, '3', 3, '2026-01-14', 'Aktywność (Siły)', 14, 11),
(22, '6', 5, '2026-01-15', 'Sprawdzian (Past Simple)', 15, 12);

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `opieka`
--

CREATE TABLE `opieka` (
  `id_ucznia` int(11) NOT NULL,
  `id_rodzica` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `opieka`
--

INSERT INTO `opieka` (`id_ucznia`, `id_rodzica`) VALUES
(1, 2),
(2, 2),
(4, 3),
(4, 4),
(5, 3),
(5, 4),
(6, 5),
(6, 6),
(7, 5),
(7, 6),
(8, 7),
(8, 8),
(9, 7),
(9, 8),
(10, 9),
(10, 10),
(11, 9),
(11, 10),
(12, 11),
(12, 12),
(13, 11),
(13, 12),
(14, 13),
(14, 14),
(15, 13),
(15, 14);

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `przedmiot`
--

CREATE TABLE `przedmiot` (
  `id_przedmiotu` int(11) NOT NULL,
  `nazwa` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `przedmiot`
--

INSERT INTO `przedmiot` (`id_przedmiotu`, `nazwa`) VALUES
(1, 'Matematyka'),
(2, 'Język Polski'),
(3, 'Fizyka'),
(4, 'Język Angielski');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `przedmiot_w_klasie`
--

CREATE TABLE `przedmiot_w_klasie` (
  `id_przedmiot_w_klasie` int(11) NOT NULL,
  `id_klasy` int(11) NOT NULL,
  `id_przedmiotu` int(11) NOT NULL,
  `id_nauczyciela` int(11) NOT NULL,
  `terminy` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `przedmiot_w_klasie`
--

INSERT INTO `przedmiot_w_klasie` (`id_przedmiot_w_klasie`, `id_klasy`, `id_przedmiotu`, `id_nauczyciela`, `terminy`) VALUES
(1, 1, 1, 2, '1_3,2_5,4_6'),
(2, 2, 1, 2, '2_5,3_4,4_6'),
(3, 2, 3, 3, '1_3,2_1,2_7,3_6'),
(4, 2, 2, 2, '5_6'),
(5, 1, 2, 5, '1_2,3_4'),
(6, 1, 3, 6, '2_3,4_5'),
(7, 1, 4, 7, '1_6,3_6'),
(8, 2, 4, 4, '2_2,4_2'),
(9, 3, 1, 4, '1_1,3_1'),
(10, 3, 2, 5, '2_4,4_4'),
(11, 3, 3, 6, '1_5,3_5'),
(12, 3, 4, 7, '2_6,4_6');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `rodzic`
--

CREATE TABLE `rodzic` (
  `id_rodzica` int(11) NOT NULL,
  `id_uzytkownika` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rodzic`
--

INSERT INTO `rodzic` (`id_rodzica`, `id_uzytkownika`) VALUES
(1, 13),
(2, 16),
(3, 26),
(4, 27),
(5, 28),
(6, 29),
(7, 30),
(8, 31),
(9, 32),
(10, 33),
(11, 34),
(12, 35),
(13, 36),
(14, 37);

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `sprawdzian`
--

CREATE TABLE `sprawdzian` (
  `id_sprawdzianu` int(11) NOT NULL,
  `id_przedmiot_w_klasie` int(11) NOT NULL,
  `data` date NOT NULL,
  `typ` varchar(20) NOT NULL,
  `opis` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sprawdzian`
--

INSERT INTO `sprawdzian` (`id_sprawdzianu`, `id_przedmiot_w_klasie`, `data`, `typ`, `opis`) VALUES
(5, 1, '2026-01-19', 'Sprawdzian', 'spr1'),
(6, 1, '2026-01-20', 'Sprawdzian', 'spr2'),
(7, 1, '2026-01-28', 'Sprawdzian', 'spr'),
(8, 2, '2026-01-15', 'Sprawdzian', 'Sprawdzian: Ułamki dziesiętne'),
(9, 2, '2026-01-16', 'Sprawdzian', 'Sprawdzian: Działania na ułamkach'),
(10, 12, '2026-01-15', 'Sprawdzian', 'Sprawdzian: Past Simple'),
(11, 12, '2026-01-16', 'Sprawdzian', 'Kartkówka: Irregular verbs');

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `uczen`
--

CREATE TABLE `uczen` (
  `id_ucznia` int(11) NOT NULL,
  `nr_dziennika` int(11) NOT NULL,
  `id_uzytkownika` int(11) NOT NULL,
  `id_klasy` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `uczen`
--

INSERT INTO `uczen` (`id_ucznia`, `nr_dziennika`, `id_uzytkownika`, `id_klasy`) VALUES
(1, 1, 5, 1),
(2, 1, 18, 2),
(3, 2, 19, 2),
(4, 2, 38, 1),
(5, 3, 39, 1),
(6, 4, 40, 1),
(7, 5, 41, 1),
(8, 3, 42, 2),
(9, 4, 43, 2),
(10, 5, 44, 2),
(11, 6, 45, 2),
(12, 1, 46, 3),
(13, 2, 47, 3),
(14, 3, 48, 3),
(15, 4, 49, 3);

-- --------------------------------------------------------

--
-- Struktura tabeli dla tabeli `uzytkownik`
--

CREATE TABLE `uzytkownik` (
  `id_uzytkownika` int(11) NOT NULL,
  `login` varchar(100) NOT NULL,
  `haslo` varchar(255) NOT NULL,
  `imie` varchar(40) NOT NULL,
  `nazwisko` varchar(60) NOT NULL,
  `email` varchar(50) NOT NULL,
  `czy_aktywny` varchar(3) NOT NULL DEFAULT 'TAK',
  `rola` varchar(30) NOT NULL,
  `telefon` varchar(12) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `uzytkownik`
--

INSERT INTO `uzytkownik` (`id_uzytkownika`, `login`, `haslo`, `imie`, `nazwisko`, `email`, `czy_aktywny`, `rola`, `telefon`) VALUES
(1, 'admin', 'admin123', 'Jan', 'Administrator', 'admin@szkola.pl', 'TAK', 'admin', NULL),
(4, 'jkowal', 'szkola123', 'Jan', 'Kowal', 'eee@gmail.com', 'TAK', 'nauczyciel', '44444444'),
(5, 'jkowal88', 'uczen123', 'jan', 'kowal', 'jkowal88@szkola.pl', 'TAK', 'uczen', NULL),
(7, 'jan.nowak60@szkola.pl', 'start123', 'Jan', 'Nowak', 'jan.nowak60@szkola.pl', 'TAK', 'uczen', NULL),
(10, 'jan.dab952@szkola.pl', 'start123', 'Jan', 'Dąb', 'jan.dab952@szkola.pl', 'TAK', 'rodzic', NULL),
(13, 'karol.kos317@szkola.pl', 'start123', 'Karol', 'Kos', 'karol.kos317@szkola.pl', 'TAK', 'rodzic', NULL),
(16, 'andrzej.kaczor215@szkola.pl', 'start123', 'Andrzej', 'Kaczor', 'andrzej.kaczor215@szkola.pl', 'TAK', 'rodzic', NULL),
(18, 'annakowal941@szkola.pl', 'start123', 'Anna', 'Kowal', 'annakowal941@szkola.pl', 'TAK', 'uczen', NULL),
(19, 'piotrzielinski148@szkola.pl', 'start123', 'Piotr', 'Zieliński', 'piotrzielinski148@szkola.pl', 'TAK', 'uczen', NULL),
(20, 'abąk', 'szkola123', 'Agata', 'Bąk', 'abak@szkola.com', 'TAK', 'nauczyciel', NULL),
(21, 'ajastrząb', 'szkola123', 'Aneta', 'Jastrząb', 'aneta.jastrzab@szkola.pl', 'TAK', 'sekretariat', NULL),
(22, 't.wisniewski', '$2y$10$Kgqs1aXjOJhWqy0gGXUD/.ulZWVxIgyyHNbLGgyP1lc/M.hkpBoZ2', 'Tomasz', 'Wiśniewski', 'tomasz.wisniewski@szkola.pl', 'TAK', 'nauczyciel', '501234567'),
(23, 'k.nowicka', '$2y$10$Kgqs1aXjOJhWqy0gGXUD/.ulZWVxIgyyHNbLGgyP1lc/M.hkpBoZ2', 'Katarzyna', 'Nowicka', 'katarzyna.nowicka@szkola.pl', 'TAK', 'nauczyciel', '502234567'),
(24, 'p.lewandowski', '$2y$10$Kgqs1aXjOJhWqy0gGXUD/.ulZWVxIgyyHNbLGgyP1lc/M.hkpBoZ2', 'Piotr', 'Lewandowski', 'piotr.lewandowski@szkola.pl', 'TAK', 'nauczyciel', '503234567'),
(25, 'm.mazur', '$2y$10$Kgqs1aXjOJhWqy0gGXUD/.ulZWVxIgyyHNbLGgyP1lc/M.hkpBoZ2', 'Monika', 'Mazur', 'monika.mazur@szkola.pl', 'TAK', 'nauczyciel', '504234567'),
(26, 'anna.maj@szkola.pl', '$2y$10$Kgqs1aXjOJhWqy0gGXUD/.ulZWVxIgyyHNbLGgyP1lc/M.hkpBoZ2', 'Anna', 'Maj', 'anna.maj@szkola.pl', 'TAK', 'rodzic', '511111111'),
(27, 'pawel.maj@szkola.pl', '$2y$10$Kgqs1aXjOJhWqy0gGXUD/.ulZWVxIgyyHNbLGgyP1lc/M.hkpBoZ2', 'Paweł', 'Maj', 'pawel.maj@szkola.pl', 'TAK', 'rodzic', '512111111'),
(28, 'ewa.krawczyk@szkola.pl', '$2y$10$Kgqs1aXjOJhWqy0gGXUD/.ulZWVxIgyyHNbLGgyP1lc/M.hkpBoZ2', 'Ewa', 'Krawczyk', 'ewa.krawczyk@szkola.pl', 'TAK', 'rodzic', '513111111'),
(29, 'marek.krawczyk@szkola.pl', '$2y$10$Kgqs1aXjOJhWqy0gGXUD/.ulZWVxIgyyHNbLGgyP1lc/M.hkpBoZ2', 'Marek', 'Krawczyk', 'marek.krawczyk@szkola.pl', 'TAK', 'rodzic', '514111111'),
(30, 'justyna.wrona@szkola.pl', '$2y$10$Kgqs1aXjOJhWqy0gGXUD/.ulZWVxIgyyHNbLGgyP1lc/M.hkpBoZ2', 'Justyna', 'Wrona', 'justyna.wrona@szkola.pl', 'TAK', 'rodzic', '515111111'),
(31, 'krzysztof.wrona@szkola.pl', '$2y$10$Kgqs1aXjOJhWqy0gGXUD/.ulZWVxIgyyHNbLGgyP1lc/M.hkpBoZ2', 'Krzysztof', 'Wrona', 'krzysztof.wrona@szkola.pl', 'TAK', 'rodzic', '516111111'),
(32, 'monika.sikora@szkola.pl', '$2y$10$Kgqs1aXjOJhWqy0gGXUD/.ulZWVxIgyyHNbLGgyP1lc/M.hkpBoZ2', 'Monika', 'Sikora', 'monika.sikora@szkola.pl', 'TAK', 'rodzic', '517111111'),
(33, 'adam.sikora@szkola.pl', '$2y$10$Kgqs1aXjOJhWqy0gGXUD/.ulZWVxIgyyHNbLGgyP1lc/M.hkpBoZ2', 'Adam', 'Sikora', 'adam.sikora@szkola.pl', 'TAK', 'rodzic', '518111111'),
(34, 'joanna.urban@szkola.pl', '$2y$10$Kgqs1aXjOJhWqy0gGXUD/.ulZWVxIgyyHNbLGgyP1lc/M.hkpBoZ2', 'Joanna', 'Urban', 'joanna.urban@szkola.pl', 'TAK', 'rodzic', '519111111'),
(35, 'piotr.urban@szkola.pl', '$2y$10$Kgqs1aXjOJhWqy0gGXUD/.ulZWVxIgyyHNbLGgyP1lc/M.hkpBoZ2', 'Piotr', 'Urban', 'piotr.urban@szkola.pl', 'TAK', 'rodzic', '520111111'),
(36, 'alicja.lis@szkola.pl', '$2y$10$Kgqs1aXjOJhWqy0gGXUD/.ulZWVxIgyyHNbLGgyP1lc/M.hkpBoZ2', 'Alicja', 'Lis', 'alicja.lis@szkola.pl', 'TAK', 'rodzic', '521111111'),
(37, 'robert.lis@szkola.pl', '$2y$10$Kgqs1aXjOJhWqy0gGXUD/.ulZWVxIgyyHNbLGgyP1lc/M.hkpBoZ2', 'Robert', 'Lis', 'robert.lis@szkola.pl', 'TAK', 'rodzic', '522111111'),
(38, 'julia.maj@szkola.pl', '$2y$10$Kgqs1aXjOJhWqy0gGXUD/.ulZWVxIgyyHNbLGgyP1lc/M.hkpBoZ2', 'Julia', 'Maj', 'julia.maj@szkola.pl', 'TAK', 'uczen', NULL),
(39, 'kacper.maj@szkola.pl', '$2y$10$Kgqs1aXjOJhWqy0gGXUD/.ulZWVxIgyyHNbLGgyP1lc/M.hkpBoZ2', 'Kacper', 'Maj', 'kacper.maj@szkola.pl', 'TAK', 'uczen', NULL),
(40, 'zuzanna.krawczyk@szkola.pl', '$2y$10$Kgqs1aXjOJhWqy0gGXUD/.ulZWVxIgyyHNbLGgyP1lc/M.hkpBoZ2', 'Zuzanna', 'Krawczyk', 'zuzanna.krawczyk@szkola.pl', 'TAK', 'uczen', NULL),
(41, 'szymon.krawczyk@szkola.pl', '$2y$10$Kgqs1aXjOJhWqy0gGXUD/.ulZWVxIgyyHNbLGgyP1lc/M.hkpBoZ2', 'Szymon', 'Krawczyk', 'szymon.krawczyk@szkola.pl', 'TAK', 'uczen', NULL),
(42, 'oliwia.wrona@szkola.pl', '$2y$10$Kgqs1aXjOJhWqy0gGXUD/.ulZWVxIgyyHNbLGgyP1lc/M.hkpBoZ2', 'Oliwia', 'Wrona', 'oliwia.wrona@szkola.pl', 'TAK', 'uczen', NULL),
(43, 'jakub.wrona@szkola.pl', '$2y$10$Kgqs1aXjOJhWqy0gGXUD/.ulZWVxIgyyHNbLGgyP1lc/M.hkpBoZ2', 'Jakub', 'Wrona', 'jakub.wrona@szkola.pl', 'TAK', 'uczen', NULL),
(44, 'natalia.sikora@szkola.pl', '$2y$10$Kgqs1aXjOJhWqy0gGXUD/.ulZWVxIgyyHNbLGgyP1lc/M.hkpBoZ2', 'Natalia', 'Sikora', 'natalia.sikora@szkola.pl', 'TAK', 'uczen', NULL),
(45, 'michal.sikora@szkola.pl', '$2y$10$Kgqs1aXjOJhWqy0gGXUD/.ulZWVxIgyyHNbLGgyP1lc/M.hkpBoZ2', 'Michał', 'Sikora', 'michal.sikora@szkola.pl', 'TAK', 'uczen', NULL),
(46, 'lena.urban@szkola.pl', '$2y$10$Kgqs1aXjOJhWqy0gGXUD/.ulZWVxIgyyHNbLGgyP1lc/M.hkpBoZ2', 'Lena', 'Urban', 'lena.urban@szkola.pl', 'TAK', 'uczen', NULL),
(47, 'mateusz.urban@szkola.pl', '$2y$10$Kgqs1aXjOJhWqy0gGXUD/.ulZWVxIgyyHNbLGgyP1lc/M.hkpBoZ2', 'Mateusz', 'Urban', 'mateusz.urban@szkola.pl', 'TAK', 'uczen', NULL),
(48, 'amelia.lis@szkola.pl', '$2y$10$Kgqs1aXjOJhWqy0gGXUD/.ulZWVxIgyyHNbLGgyP1lc/M.hkpBoZ2', 'Amelia', 'Lis', 'amelia.lis@szkola.pl', 'TAK', 'uczen', NULL),
(49, 'filip.lis@szkola.pl', '$2y$10$Kgqs1aXjOJhWqy0gGXUD/.ulZWVxIgyyHNbLGgyP1lc/M.hkpBoZ2', 'Filip', 'Lis', 'filip.lis@szkola.pl', 'TAK', 'uczen', NULL);

--
-- Indeksy dla zrzutów tabel
--

--
-- Indeksy dla tabeli `klasa`
--
ALTER TABLE `klasa`
  ADD PRIMARY KEY (`id_klasy`);

--
-- Indeksy dla tabeli `lekcja`
--
ALTER TABLE `lekcja`
  ADD PRIMARY KEY (`id_lekcji`),
  ADD KEY `id_przedmiot_w_klasie` (`id_przedmiot_w_klasie`);

--
-- Indeksy dla tabeli `nauczyciel`
--
ALTER TABLE `nauczyciel`
  ADD PRIMARY KEY (`id_nauczyciela`),
  ADD KEY `id_uzytkownika` (`id_uzytkownika`);

--
-- Indeksy dla tabeli `nieobecnosc`
--
ALTER TABLE `nieobecnosc`
  ADD PRIMARY KEY (`id_ucznia`,`id_lekcji`),
  ADD KEY `id_lekcji` (`id_lekcji`);

--
-- Indeksy dla tabeli `ocena`
--
ALTER TABLE `ocena`
  ADD PRIMARY KEY (`id_oceny`),
  ADD KEY `id_ucznia` (`id_ucznia`),
  ADD KEY `id_przedmiot_w_klasie` (`id_przedmiot_w_klasie`);

--
-- Indeksy dla tabeli `opieka`
--
ALTER TABLE `opieka`
  ADD PRIMARY KEY (`id_ucznia`,`id_rodzica`),
  ADD KEY `id_rodzica` (`id_rodzica`);

--
-- Indeksy dla tabeli `przedmiot`
--
ALTER TABLE `przedmiot`
  ADD PRIMARY KEY (`id_przedmiotu`);

--
-- Indeksy dla tabeli `przedmiot_w_klasie`
--
ALTER TABLE `przedmiot_w_klasie`
  ADD PRIMARY KEY (`id_przedmiot_w_klasie`),
  ADD UNIQUE KEY `id_klasy` (`id_klasy`,`id_przedmiotu`),
  ADD KEY `id_przedmiotu` (`id_przedmiotu`),
  ADD KEY `id_nauczyciela` (`id_nauczyciela`);

--
-- Indeksy dla tabeli `rodzic`
--
ALTER TABLE `rodzic`
  ADD PRIMARY KEY (`id_rodzica`),
  ADD KEY `id_uzytkownika` (`id_uzytkownika`);

--
-- Indeksy dla tabeli `sprawdzian`
--
ALTER TABLE `sprawdzian`
  ADD PRIMARY KEY (`id_sprawdzianu`),
  ADD KEY `id_przedmiot_w_klasie` (`id_przedmiot_w_klasie`);

--
-- Indeksy dla tabeli `uczen`
--
ALTER TABLE `uczen`
  ADD PRIMARY KEY (`id_ucznia`),
  ADD UNIQUE KEY `id_klasy` (`id_klasy`,`nr_dziennika`),
  ADD KEY `id_uzytkownika` (`id_uzytkownika`);

--
-- Indeksy dla tabeli `uzytkownik`
--
ALTER TABLE `uzytkownik`
  ADD PRIMARY KEY (`id_uzytkownika`),
  ADD UNIQUE KEY `login` (`login`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `klasa`
--
ALTER TABLE `klasa`
  MODIFY `id_klasy` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `lekcja`
--
ALTER TABLE `lekcja`
  MODIFY `id_lekcji` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `nauczyciel`
--
ALTER TABLE `nauczyciel`
  MODIFY `id_nauczyciela` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `ocena`
--
ALTER TABLE `ocena`
  MODIFY `id_oceny` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `przedmiot`
--
ALTER TABLE `przedmiot`
  MODIFY `id_przedmiotu` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `przedmiot_w_klasie`
--
ALTER TABLE `przedmiot_w_klasie`
  MODIFY `id_przedmiot_w_klasie` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `rodzic`
--
ALTER TABLE `rodzic`
  MODIFY `id_rodzica` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `sprawdzian`
--
ALTER TABLE `sprawdzian`
  MODIFY `id_sprawdzianu` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `uczen`
--
ALTER TABLE `uczen`
  MODIFY `id_ucznia` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `uzytkownik`
--
ALTER TABLE `uzytkownik`
  MODIFY `id_uzytkownika` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `lekcja`
--
ALTER TABLE `lekcja`
  ADD CONSTRAINT `lekcja_ibfk_1` FOREIGN KEY (`id_przedmiot_w_klasie`) REFERENCES `przedmiot_w_klasie` (`id_przedmiot_w_klasie`);

--
-- Constraints for table `nauczyciel`
--
ALTER TABLE `nauczyciel`
  ADD CONSTRAINT `nauczyciel_ibfk_1` FOREIGN KEY (`id_uzytkownika`) REFERENCES `uzytkownik` (`id_uzytkownika`) ON DELETE CASCADE;

--
-- Constraints for table `nieobecnosc`
--
ALTER TABLE `nieobecnosc`
  ADD CONSTRAINT `nieobecnosc_ibfk_1` FOREIGN KEY (`id_ucznia`) REFERENCES `uczen` (`id_ucznia`) ON DELETE CASCADE,
  ADD CONSTRAINT `nieobecnosc_ibfk_2` FOREIGN KEY (`id_lekcji`) REFERENCES `lekcja` (`id_lekcji`) ON DELETE CASCADE;

--
-- Constraints for table `ocena`
--
ALTER TABLE `ocena`
  ADD CONSTRAINT `ocena_ibfk_1` FOREIGN KEY (`id_ucznia`) REFERENCES `uczen` (`id_ucznia`) ON DELETE CASCADE,
  ADD CONSTRAINT `ocena_ibfk_2` FOREIGN KEY (`id_przedmiot_w_klasie`) REFERENCES `przedmiot_w_klasie` (`id_przedmiot_w_klasie`);

--
-- Constraints for table `opieka`
--
ALTER TABLE `opieka`
  ADD CONSTRAINT `opieka_ibfk_1` FOREIGN KEY (`id_ucznia`) REFERENCES `uczen` (`id_ucznia`) ON DELETE CASCADE,
  ADD CONSTRAINT `opieka_ibfk_2` FOREIGN KEY (`id_rodzica`) REFERENCES `rodzic` (`id_rodzica`) ON DELETE CASCADE;

--
-- Constraints for table `przedmiot_w_klasie`
--
ALTER TABLE `przedmiot_w_klasie`
  ADD CONSTRAINT `przedmiot_w_klasie_ibfk_1` FOREIGN KEY (`id_klasy`) REFERENCES `klasa` (`id_klasy`) ON DELETE CASCADE,
  ADD CONSTRAINT `przedmiot_w_klasie_ibfk_2` FOREIGN KEY (`id_przedmiotu`) REFERENCES `przedmiot` (`id_przedmiotu`) ON DELETE CASCADE,
  ADD CONSTRAINT `przedmiot_w_klasie_ibfk_3` FOREIGN KEY (`id_nauczyciela`) REFERENCES `nauczyciel` (`id_nauczyciela`);

--
-- Constraints for table `rodzic`
--
ALTER TABLE `rodzic`
  ADD CONSTRAINT `rodzic_ibfk_1` FOREIGN KEY (`id_uzytkownika`) REFERENCES `uzytkownik` (`id_uzytkownika`) ON DELETE CASCADE;

--
-- Constraints for table `sprawdzian`
--
ALTER TABLE `sprawdzian`
  ADD CONSTRAINT `sprawdzian_ibfk_1` FOREIGN KEY (`id_przedmiot_w_klasie`) REFERENCES `przedmiot_w_klasie` (`id_przedmiot_w_klasie`) ON DELETE CASCADE;

--
-- Constraints for table `uczen`
--
ALTER TABLE `uczen`
  ADD CONSTRAINT `uczen_ibfk_1` FOREIGN KEY (`id_uzytkownika`) REFERENCES `uzytkownik` (`id_uzytkownika`) ON DELETE CASCADE,
  ADD CONSTRAINT `uczen_ibfk_2` FOREIGN KEY (`id_klasy`) REFERENCES `klasa` (`id_klasy`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
