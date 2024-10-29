-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Хост: 127.0.0.1:3306
-- Время создания: Окт 29 2024 г., 19:21
-- Версия сервера: 8.0.30
-- Версия PHP: 8.1.9

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `nevatrip-test-1`
--

-- --------------------------------------------------------

--
-- Структура таблицы `nevatrip_order`
--

CREATE TABLE `nevatrip_order` (
  `id` int NOT NULL,
  `event_id` int NOT NULL,
  `event_date` varchar(10) NOT NULL,
  `ticket_adult_price` int NOT NULL,
  `ticket_adult_quantity` int NOT NULL,
  `ticket_kid_price` int NOT NULL,
  `ticket_kid_quantity` int NOT NULL,
  `barcode` varchar(120) NOT NULL,
  `equal_price` int NOT NULL,
  `created` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Дамп данных таблицы `nevatrip_order`
--

INSERT INTO `nevatrip_order` (`id`, `event_id`, `event_date`, `ticket_adult_price`, `ticket_adult_quantity`, `ticket_kid_price`, `ticket_kid_quantity`, `barcode`, `equal_price`, `created`) VALUES
(1, 11, '12-20-2001', 500, 2, 300, 3, '65133283', 1900, '2024-10-29 18:51:14'),
(7, 10, '2024-08-21', 500, 2, 300, 3, '62951760', 1900, '2024-10-29 19:07:58'),
(8, 1, '2024-08-21', 500, 2, 300, 3, '12794572', 1900, '2024-10-29 19:08:31');

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `nevatrip_order`
--
ALTER TABLE `nevatrip_order`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `event_id` (`event_id`),
  ADD UNIQUE KEY `barcode` (`barcode`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `nevatrip_order`
--
ALTER TABLE `nevatrip_order`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
