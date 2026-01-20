-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sty 18, 2026 at 08:24 PM
-- Wersja serwera: 10.4.32-MariaDB
-- Wersja PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `dziennik_szkolny`
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
(3, 'Ułamki', '2026-01-08', 2);

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
(3, 20);

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
(3, 2, 0, 'Nieobecny', NULL);

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
(8, '6', 5, '2026-01-08', 'Odpowiedź ustna (Mnozenie)', 3, 2);

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
(2, 2);

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
(4, 2, 2, 2, '5_6');

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
(2, 16);

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
(7, 1, '2026-01-28', 'Sprawdzian', 'spr');

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
(3, 2, 19, 2);

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
(1, 'admin', '$2y$10$uysowCcq7dARcuNRuKIT9ONFRSR.jninGDU4H3gVwLWn5gzJcvMkC', 'Jan', 'Administrator', 'admin@szkola.pl', 'TAK', 'admin', NULL),
(4, 'jkowal', '$2y$10$2hZti9DoerAnBgAexpy0yue8GZg8ITpEBax4VotNVKVqEB9cT/t5m', 'Jan', 'Kowal', 'eee@gmail.com', 'TAK', 'nauczyciel', '44444444'),
(5, 'jkowal88', '$2y$10$LdTq8Bz2hNd/.xI16fbiReMYLzU5hS36rGLzkRe4USu.0gbNFHr2m', 'jan', 'kowal', 'jkowal88@szkola.pl', 'TAK', 'uczen', NULL),
(7, 'jan.nowak60@szkola.pl', '$2y$10$Kgqs1aXjOJhWqy0gGXUD/.ulZWVxIgyyHNbLGgyP1lc/M.hkpBoZ2', 'Jan', 'Nowak', 'jan.nowak60@szkola.pl', 'TAK', 'uczen', NULL),
(10, 'jan.dab952@szkola.pl', '$2y$10$Kgqs1aXjOJhWqy0gGXUD/.ulZWVxIgyyHNbLGgyP1lc/M.hkpBoZ2', 'Jan', 'Dąb', 'jan.dab952@szkola.pl', 'TAK', 'rodzic', NULL),
(13, 'karol.kos317@szkola.pl', '$2y$10$Kgqs1aXjOJhWqy0gGXUD/.ulZWVxIgyyHNbLGgyP1lc/M.hkpBoZ2', 'Karol', 'Kos', 'karol.kos317@szkola.pl', 'TAK', 'rodzic', NULL),
(16, 'andrzej.kaczor215@szkola.pl', '$2y$10$Kgqs1aXjOJhWqy0gGXUD/.ulZWVxIgyyHNbLGgyP1lc/M.hkpBoZ2', 'Andrzej', 'Kaczor', 'andrzej.kaczor215@szkola.pl', 'TAK', 'rodzic', NULL),
(18, 'annakowal941@szkola.pl', '$2y$10$Kgqs1aXjOJhWqy0gGXUD/.ulZWVxIgyyHNbLGgyP1lc/M.hkpBoZ2', 'Anna', 'Kowal', 'annakowal941@szkola.pl', 'TAK', 'uczen', NULL),
(19, 'piotrzielinski148@szkola.pl', '$2y$10$Kgqs1aXjOJhWqy0gGXUD/.ulZWVxIgyyHNbLGgyP1lc/M.hkpBoZ2', 'Piotr', 'Zieliński', 'piotrzielinski148@szkola.pl', 'TAK', 'uczen', NULL),
(20, 'abąk', '$2y$10$2hZti9DoerAnBgAexpy0yue8GZg8ITpEBax4VotNVKVqEB9cT/t5m', 'Agata', 'Bąk', 'abak@szkola.com', 'TAK', 'nauczyciel', NULL),
(21, 'ajastrząb', '$2y$10$2hZti9DoerAnBgAexpy0yue8GZg8ITpEBax4VotNVKVqEB9cT/t5m', 'Aneta', 'Jastrząb', 'aneta.jastrzab@szkola.pl', 'TAK', 'sekretariat', NULL);

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
  MODIFY `id_lekcji` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `nauczyciel`
--
ALTER TABLE `nauczyciel`
  MODIFY `id_nauczyciela` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `ocena`
--
ALTER TABLE `ocena`
  MODIFY `id_oceny` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `przedmiot`
--
ALTER TABLE `przedmiot`
  MODIFY `id_przedmiotu` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `przedmiot_w_klasie`
--
ALTER TABLE `przedmiot_w_klasie`
  MODIFY `id_przedmiot_w_klasie` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `rodzic`
--
ALTER TABLE `rodzic`
  MODIFY `id_rodzica` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `sprawdzian`
--
ALTER TABLE `sprawdzian`
  MODIFY `id_sprawdzianu` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `uczen`
--
ALTER TABLE `uczen`
  MODIFY `id_ucznia` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `uzytkownik`
--
ALTER TABLE `uzytkownik`
  MODIFY `id_uzytkownika` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

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
