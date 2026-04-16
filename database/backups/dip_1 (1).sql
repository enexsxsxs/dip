-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Хост: 127.0.0.1
-- Время создания: Апр 16 2026 г., 07:45
-- Версия сервера: 10.4.32-MariaDB
-- Версия PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `dip_1`
--

-- --------------------------------------------------------

--
-- Структура таблицы `activity_logs`
--

CREATE TABLE `activity_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `action` varchar(40) NOT NULL,
  `entity_type` varchar(120) NOT NULL,
  `entity_id` bigint(20) UNSIGNED DEFAULT NULL,
  `field_name` varchar(150) DEFAULT NULL,
  `old_value` longtext DEFAULT NULL,
  `new_value` longtext DEFAULT NULL,
  `title` varchar(500) DEFAULT NULL,
  `details` longtext DEFAULT NULL,
  `snapshot` longtext DEFAULT NULL,
  `occurred_at` datetime NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `activity_logs`
--

INSERT INTO `activity_logs` (`id`, `action`, `entity_type`, `entity_id`, `field_name`, `old_value`, `new_value`, `title`, `details`, `snapshot`, `occurred_at`, `user_id`) VALUES
(1, 'updated', 'App\\Models\\User', 1, NULL, NULL, NULL, 'Лиханова Елена Юрьевна (admin@example.com)', 'Изменены данные пользователя.', NULL, '2026-04-13 08:55:47', 1),
(2, 'updated', 'App\\Models\\Equipment', 1, NULL, NULL, NULL, '№1 — Valberg', 'Загружен документ: Акт ввода в эксплуатацию.', '{\"equipment\":{\"id\":1,\"number\":1,\"equipment_type_id\":1,\"name\":\"Valberg\",\"serial_number\":\"53011212\",\"production_date\":null,\"year_of_manufacture\":\"2010\",\"date_accepted_to_accounting\":null,\"inventory_number\":null,\"department_id\":1,\"cabinet_id\":1,\"group_id\":null,\"equipment_condition_id\":null,\"ru_number\":null,\"ru_date\":null,\"grsi\":null,\"registration_certificate\":null,\"date_of_registration\":null,\"valid_until\":null,\"valid_to\":null,\"verification_period\":null,\"last_verification_date\":null,\"supplier_id\":null,\"service_organization_id\":null,\"writeoff_state_id\":1,\"utilization_state_id\":1,\"deleted_at\":null},\"images\":[],\"documents\":[]}', '2026-04-13 09:01:51', 1),
(3, 'updated', 'App\\Models\\Equipment', 1, NULL, NULL, NULL, '№1 — Valberg', 'Загружен документ: Инструкция на русском языке.', '{\"equipment\":{\"id\":1,\"number\":1,\"equipment_type_id\":1,\"name\":\"Valberg\",\"serial_number\":\"53011212\",\"production_date\":null,\"year_of_manufacture\":\"2010\",\"date_accepted_to_accounting\":null,\"inventory_number\":null,\"department_id\":1,\"cabinet_id\":1,\"group_id\":null,\"equipment_condition_id\":null,\"ru_number\":null,\"ru_date\":null,\"grsi\":null,\"registration_certificate\":null,\"date_of_registration\":null,\"valid_until\":null,\"valid_to\":null,\"verification_period\":null,\"last_verification_date\":null,\"supplier_id\":null,\"service_organization_id\":null,\"writeoff_state_id\":1,\"utilization_state_id\":1,\"deleted_at\":null},\"images\":[],\"documents\":[{\"id\":1,\"path\":\"equipment_documents\\/W4kJRv3GIjnAxdR4LdRRolh31I3Jyk50bXfytwD6.pdf\",\"document_type_id\":3,\"type\":\"commissioning_act\",\"name\":\"Акт ввода в эксплуатацию\",\"uploaded_at\":\"2026-04-13 09:01:51\"}]}', '2026-04-13 09:02:00', 1),
(4, 'updated', 'App\\Models\\Equipment', 1, NULL, NULL, NULL, '№1 — Valberg', 'Загружен документ: Регистрационное удостоверение.', '{\"equipment\":{\"id\":1,\"number\":1,\"equipment_type_id\":1,\"name\":\"Valberg\",\"serial_number\":\"53011212\",\"production_date\":null,\"year_of_manufacture\":\"2010\",\"date_accepted_to_accounting\":null,\"inventory_number\":null,\"department_id\":1,\"cabinet_id\":1,\"group_id\":null,\"equipment_condition_id\":null,\"ru_number\":null,\"ru_date\":null,\"grsi\":null,\"registration_certificate\":null,\"date_of_registration\":null,\"valid_until\":null,\"valid_to\":null,\"verification_period\":null,\"last_verification_date\":null,\"supplier_id\":null,\"service_organization_id\":null,\"writeoff_state_id\":1,\"utilization_state_id\":1,\"deleted_at\":null},\"images\":[],\"documents\":[{\"id\":1,\"path\":\"equipment_documents\\/W4kJRv3GIjnAxdR4LdRRolh31I3Jyk50bXfytwD6.pdf\",\"document_type_id\":3,\"type\":\"commissioning_act\",\"name\":\"Акт ввода в эксплуатацию\",\"uploaded_at\":\"2026-04-13 09:01:51\"},{\"id\":2,\"path\":\"equipment_documents\\/dwic58bYQvUhkc591qQo6f1QiVPtK9ZjyHTP6EVc.pdf\",\"document_type_id\":1,\"type\":\"instruction\",\"name\":\"Инструкция на русском языке\",\"uploaded_at\":\"2026-04-13 09:02:00\"}]}', '2026-04-13 09:02:18', 1),
(5, 'updated', 'App\\Models\\Equipment', 1, NULL, NULL, NULL, '№1 — Valberg', 'Изменение карточки пользователем Лиханова Елена Юрьевна:\nproduction_date: null → \"2010-12-02\"\ndate_accepted_to_accounting: null → \"2014-12-02\"\nru_number: null → \"РД-4118\\/14096\"\nru_date: null → \"2014-06-10\"\nДобавлено новых фото: 1', '{\"equipment\":{\"id\":1,\"number\":1,\"equipment_type_id\":1,\"name\":\"Valberg\",\"serial_number\":\"53011212\",\"production_date\":null,\"year_of_manufacture\":\"2010\",\"date_accepted_to_accounting\":null,\"inventory_number\":null,\"department_id\":1,\"cabinet_id\":1,\"group_id\":null,\"equipment_condition_id\":null,\"ru_number\":null,\"ru_date\":null,\"grsi\":null,\"registration_certificate\":null,\"date_of_registration\":null,\"valid_until\":null,\"valid_to\":null,\"verification_period\":null,\"last_verification_date\":null,\"supplier_id\":null,\"service_organization_id\":null,\"writeoff_state_id\":1,\"utilization_state_id\":1,\"deleted_at\":null},\"images\":[],\"documents\":[{\"id\":1,\"path\":\"equipment_documents\\/W4kJRv3GIjnAxdR4LdRRolh31I3Jyk50bXfytwD6.pdf\",\"document_type_id\":3,\"type\":\"commissioning_act\",\"name\":\"Акт ввода в эксплуатацию\",\"uploaded_at\":\"2026-04-13 09:01:51\"},{\"id\":2,\"path\":\"equipment_documents\\/dwic58bYQvUhkc591qQo6f1QiVPtK9ZjyHTP6EVc.pdf\",\"document_type_id\":1,\"type\":\"instruction\",\"name\":\"Инструкция на русском языке\",\"uploaded_at\":\"2026-04-13 09:02:00\"},{\"id\":3,\"path\":\"equipment_documents\\/aRupKNj4KzLGMvjviCA4BrmsXNBkKMwF2869sXdO.pdf\",\"document_type_id\":2,\"type\":\"registration_certificate\",\"name\":\"Регистрационное удостоверение\",\"uploaded_at\":\"2026-04-13 09:02:18\"}]}', '2026-04-13 09:09:20', 1),
(6, 'updated', 'App\\Models\\Equipment', 2, NULL, NULL, NULL, '№2 — Pozis Paracels', 'Загружен документ: Регистрационное удостоверение.', '{\"equipment\":{\"id\":2,\"number\":2,\"equipment_type_id\":2,\"name\":\"Pozis Paracels\",\"serial_number\":\"204CV20011858\",\"production_date\":null,\"year_of_manufacture\":\"2010\",\"date_accepted_to_accounting\":null,\"inventory_number\":null,\"department_id\":1,\"cabinet_id\":2,\"group_id\":null,\"equipment_condition_id\":null,\"ru_number\":null,\"ru_date\":null,\"grsi\":null,\"registration_certificate\":null,\"date_of_registration\":null,\"valid_until\":null,\"valid_to\":null,\"verification_period\":null,\"last_verification_date\":null,\"supplier_id\":null,\"service_organization_id\":null,\"writeoff_state_id\":1,\"utilization_state_id\":1,\"deleted_at\":null},\"images\":[],\"documents\":[]}', '2026-04-13 09:18:46', 1),
(7, 'updated', 'App\\Models\\Equipment', 2, NULL, NULL, NULL, '№2 — Pozis Paracels', 'Загружен документ: Инструкция на русском языке.', '{\"equipment\":{\"id\":2,\"number\":2,\"equipment_type_id\":2,\"name\":\"Pozis Paracels\",\"serial_number\":\"204CV20011858\",\"production_date\":null,\"year_of_manufacture\":\"2010\",\"date_accepted_to_accounting\":null,\"inventory_number\":null,\"department_id\":1,\"cabinet_id\":2,\"group_id\":null,\"equipment_condition_id\":null,\"ru_number\":null,\"ru_date\":null,\"grsi\":null,\"registration_certificate\":null,\"date_of_registration\":null,\"valid_until\":null,\"valid_to\":null,\"verification_period\":null,\"last_verification_date\":null,\"supplier_id\":null,\"service_organization_id\":null,\"writeoff_state_id\":1,\"utilization_state_id\":1,\"deleted_at\":null},\"images\":[],\"documents\":[{\"id\":4,\"path\":\"equipment_documents\\/s8L44uZyXDdgGTrl5sC46GT3RWq2zwhrGJLv0kIj.pdf\",\"document_type_id\":2,\"type\":\"registration_certificate\",\"name\":\"Регистрационное удостоверение\",\"uploaded_at\":\"2026-04-13 09:18:46\"}]}', '2026-04-13 09:19:00', 1),
(8, 'updated', 'App\\Models\\Equipment', 2, NULL, NULL, NULL, '№2 — Pozis Paracels', 'Загружен документ: Акт ввода в эксплуатацию.', '{\"equipment\":{\"id\":2,\"number\":2,\"equipment_type_id\":2,\"name\":\"Pozis Paracels\",\"serial_number\":\"204CV20011858\",\"production_date\":null,\"year_of_manufacture\":\"2010\",\"date_accepted_to_accounting\":null,\"inventory_number\":null,\"department_id\":1,\"cabinet_id\":2,\"group_id\":null,\"equipment_condition_id\":null,\"ru_number\":null,\"ru_date\":null,\"grsi\":null,\"registration_certificate\":null,\"date_of_registration\":null,\"valid_until\":null,\"valid_to\":null,\"verification_period\":null,\"last_verification_date\":null,\"supplier_id\":null,\"service_organization_id\":null,\"writeoff_state_id\":1,\"utilization_state_id\":1,\"deleted_at\":null},\"images\":[],\"documents\":[{\"id\":4,\"path\":\"equipment_documents\\/s8L44uZyXDdgGTrl5sC46GT3RWq2zwhrGJLv0kIj.pdf\",\"document_type_id\":2,\"type\":\"registration_certificate\",\"name\":\"Регистрационное удостоверение\",\"uploaded_at\":\"2026-04-13 09:18:46\"},{\"id\":5,\"path\":\"equipment_documents\\/0srOKFxRNx9M8Q2WT7PEVbx2sj7aP0C5Mc6PfzYB.pdf\",\"document_type_id\":1,\"type\":\"instruction\",\"name\":\"Инструкция на русском языке\",\"uploaded_at\":\"2026-04-13 09:19:00\"}]}', '2026-04-13 09:19:09', 1),
(9, 'updated', 'App\\Models\\Equipment', 2, NULL, NULL, NULL, '№2 — Pozis Paracels', 'Изменение карточки пользователем Лиханова Елена Юрьевна:\nДобавлено новых фото: 1', '{\"equipment\":{\"id\":2,\"number\":2,\"equipment_type_id\":2,\"name\":\"Pozis Paracels\",\"serial_number\":\"204CV20011858\",\"production_date\":null,\"year_of_manufacture\":\"2010\",\"date_accepted_to_accounting\":null,\"inventory_number\":null,\"department_id\":1,\"cabinet_id\":2,\"group_id\":null,\"equipment_condition_id\":null,\"ru_number\":null,\"ru_date\":null,\"grsi\":null,\"registration_certificate\":null,\"date_of_registration\":null,\"valid_until\":null,\"valid_to\":null,\"verification_period\":null,\"last_verification_date\":null,\"supplier_id\":null,\"service_organization_id\":null,\"writeoff_state_id\":1,\"utilization_state_id\":1,\"deleted_at\":null},\"images\":[],\"documents\":[{\"id\":4,\"path\":\"equipment_documents\\/s8L44uZyXDdgGTrl5sC46GT3RWq2zwhrGJLv0kIj.pdf\",\"document_type_id\":2,\"type\":\"registration_certificate\",\"name\":\"Регистрационное удостоверение\",\"uploaded_at\":\"2026-04-13 09:18:46\"},{\"id\":5,\"path\":\"equipment_documents\\/0srOKFxRNx9M8Q2WT7PEVbx2sj7aP0C5Mc6PfzYB.pdf\",\"document_type_id\":1,\"type\":\"instruction\",\"name\":\"Инструкция на русском языке\",\"uploaded_at\":\"2026-04-13 09:19:00\"},{\"id\":6,\"path\":\"equipment_documents\\/JZUYDzPaVHUy0VbrqpJvWU4kcv0cudMEzSoBKPDI.pdf\",\"document_type_id\":3,\"type\":\"commissioning_act\",\"name\":\"Акт ввода в эксплуатацию\",\"uploaded_at\":\"2026-04-13 09:19:09\"}]}', '2026-04-13 09:20:29', 1),
(10, 'updated', 'App\\Models\\Equipment', 3, NULL, NULL, NULL, '№3 — Polair ШХФ-0,5', 'Изменение карточки пользователем Лиханова Елена Юрьевна:\nДобавлено новых фото: 2', '{\"equipment\":{\"id\":3,\"number\":3,\"equipment_type_id\":2,\"name\":\"Polair ШХФ-0,5\",\"serial_number\":\"A122030916\",\"production_date\":null,\"year_of_manufacture\":\"2016\",\"date_accepted_to_accounting\":null,\"inventory_number\":null,\"department_id\":1,\"cabinet_id\":2,\"group_id\":null,\"equipment_condition_id\":null,\"ru_number\":null,\"ru_date\":null,\"grsi\":null,\"registration_certificate\":null,\"date_of_registration\":null,\"valid_until\":null,\"valid_to\":null,\"verification_period\":null,\"last_verification_date\":null,\"supplier_id\":null,\"service_organization_id\":null,\"writeoff_state_id\":1,\"utilization_state_id\":1,\"deleted_at\":null},\"images\":[],\"documents\":[]}', '2026-04-13 09:24:10', 1),
(11, 'updated', 'App\\Models\\Equipment', 3, NULL, NULL, NULL, '№3 — Polair ШХФ-0,5', 'Загружен документ: Регистрационное удостоверение.', '{\"equipment\":{\"id\":3,\"number\":3,\"equipment_type_id\":2,\"name\":\"Polair ШХФ-0,5\",\"serial_number\":\"A122030916\",\"production_date\":null,\"year_of_manufacture\":\"2016\",\"date_accepted_to_accounting\":null,\"inventory_number\":null,\"department_id\":1,\"cabinet_id\":2,\"group_id\":null,\"equipment_condition_id\":null,\"ru_number\":null,\"ru_date\":null,\"grsi\":null,\"registration_certificate\":null,\"date_of_registration\":null,\"valid_until\":null,\"valid_to\":null,\"verification_period\":null,\"last_verification_date\":null,\"supplier_id\":null,\"service_organization_id\":null,\"writeoff_state_id\":1,\"utilization_state_id\":1,\"deleted_at\":null},\"images\":[{\"id\":3,\"path\":\"equipment\\/thDh0oY66zdR3fCfpz4JQOcc0RTPzsz150MpqDyg.jpg\"},{\"id\":4,\"path\":\"equipment\\/QF4a17NO0klnQcSk4Rn9NVR7dWpE0aoqctaSpHQw.jpg\"}],\"documents\":[]}', '2026-04-13 09:25:09', 1),
(12, 'updated', 'App\\Models\\Equipment', 3, NULL, NULL, NULL, '№3 — Polair ШХФ-0,5', 'Загружен документ: Инструкция на русском языке.', '{\"equipment\":{\"id\":3,\"number\":3,\"equipment_type_id\":2,\"name\":\"Polair ШХФ-0,5\",\"serial_number\":\"A122030916\",\"production_date\":null,\"year_of_manufacture\":\"2016\",\"date_accepted_to_accounting\":null,\"inventory_number\":null,\"department_id\":1,\"cabinet_id\":2,\"group_id\":null,\"equipment_condition_id\":null,\"ru_number\":null,\"ru_date\":null,\"grsi\":null,\"registration_certificate\":null,\"date_of_registration\":null,\"valid_until\":null,\"valid_to\":null,\"verification_period\":null,\"last_verification_date\":null,\"supplier_id\":null,\"service_organization_id\":null,\"writeoff_state_id\":1,\"utilization_state_id\":1,\"deleted_at\":null},\"images\":[{\"id\":3,\"path\":\"equipment\\/thDh0oY66zdR3fCfpz4JQOcc0RTPzsz150MpqDyg.jpg\"},{\"id\":4,\"path\":\"equipment\\/QF4a17NO0klnQcSk4Rn9NVR7dWpE0aoqctaSpHQw.jpg\"}],\"documents\":[{\"id\":7,\"path\":\"equipment_documents\\/3MfwU5AosVeTnKKLQFXJOGSK4RvpHf0wTkXt14p1.pdf\",\"document_type_id\":2,\"type\":\"registration_certificate\",\"name\":\"Регистрационное удостоверение\",\"uploaded_at\":\"2026-04-13 09:25:09\"}]}', '2026-04-13 09:29:35', 1),
(13, 'updated', 'App\\Models\\Equipment', 3, NULL, NULL, NULL, '№3 — Polair ШХФ-0,5', 'Загружен документ: Акт ввода в эксплуатацию.', '{\"equipment\":{\"id\":3,\"number\":3,\"equipment_type_id\":2,\"name\":\"Polair ШХФ-0,5\",\"serial_number\":\"A122030916\",\"production_date\":null,\"year_of_manufacture\":\"2016\",\"date_accepted_to_accounting\":null,\"inventory_number\":null,\"department_id\":1,\"cabinet_id\":2,\"group_id\":null,\"equipment_condition_id\":null,\"ru_number\":null,\"ru_date\":null,\"grsi\":null,\"registration_certificate\":null,\"date_of_registration\":null,\"valid_until\":null,\"valid_to\":null,\"verification_period\":null,\"last_verification_date\":null,\"supplier_id\":null,\"service_organization_id\":null,\"writeoff_state_id\":1,\"utilization_state_id\":1,\"deleted_at\":null},\"images\":[{\"id\":3,\"path\":\"equipment\\/thDh0oY66zdR3fCfpz4JQOcc0RTPzsz150MpqDyg.jpg\"},{\"id\":4,\"path\":\"equipment\\/QF4a17NO0klnQcSk4Rn9NVR7dWpE0aoqctaSpHQw.jpg\"}],\"documents\":[{\"id\":7,\"path\":\"equipment_documents\\/3MfwU5AosVeTnKKLQFXJOGSK4RvpHf0wTkXt14p1.pdf\",\"document_type_id\":2,\"type\":\"registration_certificate\",\"name\":\"Регистрационное удостоверение\",\"uploaded_at\":\"2026-04-13 09:25:09\"},{\"id\":8,\"path\":\"equipment_documents\\/J6YPf0p4hV22gD6RXUTfoslwbDcTJKrrQ4wIwa6j.pdf\",\"document_type_id\":1,\"type\":\"instruction\",\"name\":\"Инструкция на русском языке\",\"uploaded_at\":\"2026-04-13 09:29:35\"}]}', '2026-04-13 09:29:42', 1),
(14, 'updated', 'App\\Models\\Equipment', 4, NULL, NULL, NULL, '№4 — Polair', 'Изменение карточки пользователем Лиханова Елена Юрьевна:\nДобавлено новых фото: 2\nЗагружен документ: document_registration_certificate\nЗагружен документ: document_instruction\nЗагружен документ: document_commissioning_act', '{\"equipment\":{\"id\":4,\"number\":4,\"equipment_type_id\":2,\"name\":\"Polair\",\"serial_number\":\"A12203\",\"production_date\":null,\"year_of_manufacture\":\"2016\",\"date_accepted_to_accounting\":null,\"inventory_number\":null,\"department_id\":1,\"cabinet_id\":2,\"group_id\":null,\"equipment_condition_id\":null,\"ru_number\":null,\"ru_date\":null,\"grsi\":null,\"registration_certificate\":null,\"date_of_registration\":null,\"valid_until\":null,\"valid_to\":null,\"verification_period\":null,\"last_verification_date\":null,\"supplier_id\":null,\"service_organization_id\":null,\"writeoff_state_id\":1,\"utilization_state_id\":1,\"deleted_at\":null},\"images\":[],\"documents\":[]}', '2026-04-13 09:37:22', 1),
(15, 'updated', 'App\\Models\\Equipment', 5, NULL, NULL, NULL, '№5 — Polair', 'Изменение карточки пользователем Лиханова Елена Юрьевна:\nДобавлено новых фото: 1', '{\"equipment\":{\"id\":5,\"number\":5,\"equipment_type_id\":2,\"name\":\"Polair\",\"serial_number\":\"8108558\",\"production_date\":null,\"year_of_manufacture\":\"2008\",\"date_accepted_to_accounting\":null,\"inventory_number\":null,\"department_id\":1,\"cabinet_id\":2,\"group_id\":null,\"equipment_condition_id\":null,\"ru_number\":null,\"ru_date\":null,\"grsi\":null,\"registration_certificate\":null,\"date_of_registration\":null,\"valid_until\":null,\"valid_to\":null,\"verification_period\":null,\"last_verification_date\":null,\"supplier_id\":null,\"service_organization_id\":null,\"writeoff_state_id\":1,\"utilization_state_id\":1,\"deleted_at\":null},\"images\":[],\"documents\":[]}', '2026-04-13 09:43:35', 1),
(16, 'created', 'App\\Models\\RequestLayout', 1, NULL, NULL, NULL, 'АКТ_Контроля', 'Создан макет заявки (PDF).', NULL, '2026-04-13 09:57:47', 1),
(17, 'created', 'App\\Models\\RequestRecord', 1, NULL, NULL, NULL, 'Заявка №1 — АКТ_Контроля', 'Создана заявка по макету (PDF).', NULL, '2026-04-13 10:00:34', 1),
(18, 'updated', 'App\\Models\\RequestRecord', 1, NULL, NULL, NULL, 'Заявка №1 — АКТ_Контроля', 'Изменена заявка по макету (PDF).', NULL, '2026-04-13 10:01:30', 1),
(19, 'updated', 'App\\Models\\RequestLayout', 1, NULL, NULL, NULL, 'АКТ_Контроля', 'Изменён макет заявки (PDF).', NULL, '2026-04-13 10:01:54', 1),
(20, 'updated', 'App\\Models\\RequestRecord', 1, NULL, NULL, NULL, 'Заявка №1 — АКТ_Контроля', 'Изменена заявка по макету (PDF).', NULL, '2026-04-13 10:02:03', 1),
(21, 'created', 'App\\Models\\RequestLayout', 2, NULL, NULL, NULL, 'Рапорт_на_списания_оборудования', 'Создан макет заявки (PDF).', NULL, '2026-04-13 10:20:01', 1),
(22, 'created', 'App\\Models\\EquipmentRequest', 1, NULL, NULL, NULL, 'Заявка на списание: Valberg', 'Автор: Медсестра Старшая. Комментарий: аываываываыва', NULL, '2026-04-13 10:20:19', 3),
(23, 'created', 'App\\Models\\EquipmentRequest', 2, NULL, NULL, NULL, 'Заявка на перемещение: Valberg', 'Автор: Медсестра Старшая. В отдел: Глазная. Комментарий: ыавыаываыа', NULL, '2026-04-13 10:20:26', 3),
(24, 'created', 'App\\Models\\EquipmentRequest', 3, NULL, NULL, NULL, 'Заявка на перемещение: Polair ШХФ-0,5', 'Автор: Медсестра Старшая. В отдел: Глазная. Комментарий: аыавыаыва', NULL, '2026-04-13 10:20:41', 3),
(25, 'created', 'App\\Models\\EquipmentRequest', 4, NULL, NULL, NULL, 'Заявка на списание: Polair', 'Автор: Медсестра Старшая. Комментарий: аываываываыува', NULL, '2026-04-13 10:20:48', 3),
(26, 'writeoff_approved', 'App\\Models\\Equipment', 5, 'writeoff_status', 'requested', 'approved', '№5 — Polair', 'Списание подтверждено администратором.', NULL, '2026-04-13 10:21:28', 1),
(27, 'updated', 'App\\Models\\RequestLayout', 2, NULL, NULL, NULL, 'Рапорт_на_списания_оборудования', 'Изменён макет заявки (PDF).', NULL, '2026-04-13 10:24:56', 1),
(28, 'created', 'App\\Models\\RequestRecord', 2, NULL, NULL, NULL, 'Заявка №2 — Рапорт_на_списания_оборудования', 'Создана заявка по макету (PDF).', NULL, '2026-04-13 10:25:29', 1),
(29, 'updated', 'App\\Models\\RequestLayout', 2, NULL, NULL, NULL, 'Рапорт_на_списания_оборудования', 'Изменён макет заявки (PDF).', NULL, '2026-04-13 10:36:40', 1),
(30, 'deleted', 'App\\Models\\RequestRecord', 2, NULL, NULL, NULL, 'Заявка №2 — Рапорт_на_списания_оборудования', 'Заявка по макету скрыта из списка; восстановить можно в разделе «Архив и журнал».', NULL, '2026-04-13 10:37:06', 1),
(31, 'created', 'App\\Models\\RequestRecord', 3, NULL, NULL, NULL, 'Заявка №3 — Рапорт_на_списания_оборудования', 'Создана заявка по макету (PDF).', NULL, '2026-04-13 10:37:19', 1),
(32, 'updated', 'App\\Models\\RequestLayout', 2, NULL, NULL, NULL, 'Рапорт_на_списания_оборудования', 'Изменён макет заявки (PDF).', NULL, '2026-04-13 10:42:04', 1),
(33, 'deleted', 'App\\Models\\RequestRecord', 3, NULL, NULL, NULL, 'Заявка №3 — Рапорт_на_списания_оборудования', 'Заявка по макету скрыта из списка; восстановить можно в разделе «Архив и журнал».', NULL, '2026-04-13 10:42:10', 1),
(34, 'created', 'App\\Models\\RequestRecord', 4, NULL, NULL, NULL, 'Заявка №4 — Рапорт_на_списания_оборудования', 'Создана заявка по макету (PDF).', NULL, '2026-04-13 10:42:23', 1),
(35, 'created', 'App\\Models\\RequestLayout', 3, NULL, NULL, NULL, 'Рапорт_на_перемещения_оборудования', 'Создан макет заявки (PDF).', NULL, '2026-04-13 10:55:09', 1),
(36, 'created', 'App\\Models\\RequestRecord', 5, NULL, NULL, NULL, 'Заявка №5 — Рапорт_на_перемещения_оборудования', 'Создана заявка по макету (PDF).', NULL, '2026-04-13 10:55:28', 1),
(37, 'updated', 'App\\Models\\RequestLayout', 2, NULL, NULL, NULL, 'Рапорт_на_списания_оборудования', 'Изменён макет заявки (PDF).', NULL, '2026-04-13 11:02:33', 1),
(38, 'updated', 'App\\Models\\RequestLayout', 3, NULL, NULL, NULL, 'Рапорт_на_перемещения_оборудования', 'Изменён макет заявки (PDF).', NULL, '2026-04-13 11:13:13', 1),
(39, 'updated', 'App\\Models\\RequestLayout', 2, NULL, NULL, NULL, 'Рапорт_на_списания_оборудования', 'Изменён макет заявки (PDF).', NULL, '2026-04-13 11:13:25', 1),
(40, 'updated', 'App\\Models\\RequestLayout', 2, NULL, NULL, NULL, 'Рапорт_на_списания_оборудования', 'Изменён макет заявки (PDF).', NULL, '2026-04-13 11:17:23', 1);

-- --------------------------------------------------------

--
-- Структура таблицы `cabinets`
--

CREATE TABLE `cabinets` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `number` varchar(55) NOT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `cabinets`
--

INSERT INTO `cabinets` (`id`, `number`, `deleted_at`) VALUES
(1, 'СДП', NULL),
(2, 'Коридор', NULL),
(3, 'КДП 4', NULL),
(4, 'Опер блок', NULL);

-- --------------------------------------------------------

--
-- Структура таблицы `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `cache`
--

INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES
('laravel-cache-delivery@example.com|127.0.0.1', 'i:1;', 1776266657),
('laravel-cache-delivery@example.com|127.0.0.1:timer', 'i:1776266657;', 1776266657),
('laravel-cache-manager@example.com|127.0.0.1', 'i:1;', 1776266649),
('laravel-cache-manager@example.com|127.0.0.1:timer', 'i:1776266649;', 1776266649);

-- --------------------------------------------------------

--
-- Структура таблицы `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `departments`
--

CREATE TABLE `departments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(155) NOT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `departments`
--

INSERT INTO `departments` (`id`, `name`, `deleted_at`) VALUES
(1, 'Аптека', NULL),
(2, 'Глазная', NULL);

-- --------------------------------------------------------

--
-- Структура таблицы `equipment`
--

CREATE TABLE `equipment` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `number` bigint(20) UNSIGNED NOT NULL,
  `equipment_type_id` bigint(20) UNSIGNED DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `serial_number` varchar(100) DEFAULT NULL,
  `production_date` date DEFAULT NULL,
  `year_of_manufacture` varchar(55) DEFAULT NULL,
  `date_accepted_to_accounting` date DEFAULT NULL,
  `inventory_number` varchar(100) DEFAULT NULL,
  `department_id` bigint(20) UNSIGNED DEFAULT NULL,
  `cabinet_id` bigint(20) UNSIGNED DEFAULT NULL,
  `group_id` bigint(20) UNSIGNED DEFAULT NULL,
  `equipment_condition_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ru_number` varchar(100) DEFAULT NULL,
  `ru_date` date DEFAULT NULL,
  `grsi` varchar(255) DEFAULT NULL,
  `registration_certificate` varchar(100) DEFAULT NULL,
  `date_of_registration` varchar(20) DEFAULT NULL,
  `valid_until` varchar(20) DEFAULT NULL,
  `valid_to` varchar(20) DEFAULT NULL,
  `verification_period` varchar(55) DEFAULT NULL,
  `last_verification_date` varchar(20) DEFAULT NULL,
  `supplier_id` bigint(20) UNSIGNED DEFAULT NULL,
  `service_organization_id` bigint(20) UNSIGNED DEFAULT NULL,
  `writeoff_state_id` bigint(20) UNSIGNED NOT NULL DEFAULT 1,
  `utilization_state_id` bigint(20) UNSIGNED NOT NULL DEFAULT 1,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `equipment`
--

INSERT INTO `equipment` (`id`, `number`, `equipment_type_id`, `name`, `serial_number`, `production_date`, `year_of_manufacture`, `date_accepted_to_accounting`, `inventory_number`, `department_id`, `cabinet_id`, `group_id`, `equipment_condition_id`, `ru_number`, `ru_date`, `grsi`, `registration_certificate`, `date_of_registration`, `valid_until`, `valid_to`, `verification_period`, `last_verification_date`, `supplier_id`, `service_organization_id`, `writeoff_state_id`, `utilization_state_id`, `deleted_at`) VALUES
(1, 1, 1, 'Valberg', '53011212', '2010-12-02', '2010', '2014-12-02', NULL, 1, 1, NULL, NULL, 'РД-4118/14096', '2014-06-10', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 2, 1, NULL),
(2, 2, 2, 'Pozis Paracels', '204CV20011858', NULL, '2010', NULL, NULL, 1, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 1, NULL),
(3, 3, 2, 'Polair ШХФ-0,5', 'A122030916', NULL, '2016', NULL, NULL, 1, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 1, NULL),
(4, 4, 2, 'Polair', 'A12203', NULL, '2016', NULL, NULL, 1, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 1, NULL),
(5, 5, 2, 'Polair', '8108558', NULL, '2008', NULL, NULL, 1, 2, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 3, 1, NULL);

-- --------------------------------------------------------

--
-- Структура таблицы `equipment_conditions`
--

CREATE TABLE `equipment_conditions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `equipment_documents`
--

CREATE TABLE `equipment_documents` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `document` varchar(100) NOT NULL,
  `name` varchar(255) NOT NULL,
  `document_type_id` bigint(20) UNSIGNED NOT NULL,
  `uploaded_at` datetime NOT NULL,
  `equipment_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `equipment_documents`
--

INSERT INTO `equipment_documents` (`id`, `document`, `name`, `document_type_id`, `uploaded_at`, `equipment_id`) VALUES
(1, 'equipment_documents/W4kJRv3GIjnAxdR4LdRRolh31I3Jyk50bXfytwD6.pdf', 'Акт ввода в эксплуатацию', 3, '2026-04-13 09:01:51', 1),
(2, 'equipment_documents/dwic58bYQvUhkc591qQo6f1QiVPtK9ZjyHTP6EVc.pdf', 'Инструкция на русском языке', 1, '2026-04-13 09:02:00', 1),
(3, 'equipment_documents/aRupKNj4KzLGMvjviCA4BrmsXNBkKMwF2869sXdO.pdf', 'Регистрационное удостоверение', 2, '2026-04-13 09:02:18', 1),
(4, 'equipment_documents/s8L44uZyXDdgGTrl5sC46GT3RWq2zwhrGJLv0kIj.pdf', 'Регистрационное удостоверение', 2, '2026-04-13 09:18:46', 2),
(5, 'equipment_documents/0srOKFxRNx9M8Q2WT7PEVbx2sj7aP0C5Mc6PfzYB.pdf', 'Инструкция на русском языке', 1, '2026-04-13 09:19:00', 2),
(6, 'equipment_documents/JZUYDzPaVHUy0VbrqpJvWU4kcv0cudMEzSoBKPDI.pdf', 'Акт ввода в эксплуатацию', 3, '2026-04-13 09:19:09', 2),
(7, 'equipment_documents/3MfwU5AosVeTnKKLQFXJOGSK4RvpHf0wTkXt14p1.pdf', 'Регистрационное удостоверение', 2, '2026-04-13 09:25:09', 3),
(8, 'equipment_documents/J6YPf0p4hV22gD6RXUTfoslwbDcTJKrrQ4wIwa6j.pdf', 'Инструкция на русском языке', 1, '2026-04-13 09:29:35', 3),
(9, 'equipment_documents/oQ36vdWxUA8FO6WXdqS8rxmjYsWuWWFo2RHRyI8c.docx', 'Акт ввода в эксплуатацию', 3, '2026-04-13 09:29:42', 3),
(10, 'equipment_documents/2ukMOLfNe51zDNMtMibhrjk4ybeFY87oY6VBuD2k.pdf', 'Регистрационное удостоверение', 2, '2026-04-13 09:37:22', 4),
(11, 'equipment_documents/BOKLfxYbQcUJttpyRXQFLSfKqPuB1lQgr4ueyYvc.pdf', 'Инструкция на русском языке', 1, '2026-04-13 09:37:22', 4),
(12, 'equipment_documents/gkCdwZKbW7uvTf4ZItHg4bcFGnuuMS0PLhlG7KQl.pdf', 'Акт ввода в эксплуатацию', 3, '2026-04-13 09:37:22', 4);

-- --------------------------------------------------------

--
-- Структура таблицы `equipment_document_types`
--

CREATE TABLE `equipment_document_types` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `code` varchar(32) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `equipment_document_types`
--

INSERT INTO `equipment_document_types` (`id`, `code`) VALUES
(3, 'commissioning_act'),
(1, 'instruction'),
(2, 'registration_certificate'),
(4, 'ru_scan'),
(5, 'utilization_act');

-- --------------------------------------------------------

--
-- Структура таблицы `equipment_images`
--

CREATE TABLE `equipment_images` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `image` varchar(255) NOT NULL,
  `equipment_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `equipment_images`
--

INSERT INTO `equipment_images` (`id`, `image`, `equipment_id`) VALUES
(1, 'equipment/o1y2uIF3yckUyLlzNmBtOtgP5dZJrzJWQVpNuvpx.webp', 1),
(2, 'equipment/m2U4WMVHy6ohaWwozepWgxE1VhB2hDzs44snOFKC.png', 2),
(3, 'equipment/thDh0oY66zdR3fCfpz4JQOcc0RTPzsz150MpqDyg.jpg', 3),
(4, 'equipment/QF4a17NO0klnQcSk4Rn9NVR7dWpE0aoqctaSpHQw.jpg', 3),
(5, 'equipment/FJSP7mEoMCOrJAGDZeUwt5Pw8jx2kwTZQTOUKCN6.webp', 4),
(6, 'equipment/Zuv8IT5qhREWNBYRUypyPF0Qf3oTWPmJEqdHqeA7.webp', 4),
(7, 'equipment/XtjXQRlvWSlPAfQoaXfD72vRiuIyLWuXOpp5Hz8v.webp', 5);

-- --------------------------------------------------------

--
-- Структура таблицы `equipment_requests`
--

CREATE TABLE `equipment_requests` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `equipment_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `request_type_id` bigint(20) UNSIGNED NOT NULL,
  `request_status_id` bigint(20) UNSIGNED NOT NULL DEFAULT 1,
  `from_department_id` bigint(20) UNSIGNED DEFAULT NULL,
  `to_department_id` bigint(20) UNSIGNED DEFAULT NULL,
  `comment` text DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `equipment_requests`
--

INSERT INTO `equipment_requests` (`id`, `equipment_id`, `user_id`, `request_type_id`, `request_status_id`, `from_department_id`, `to_department_id`, `comment`, `photo`, `created_at`, `updated_at`) VALUES
(1, 1, 3, 1, 1, 1, NULL, 'аываываываыва', NULL, '2026-04-13 02:20:19', '2026-04-13 02:20:19'),
(2, 1, 3, 2, 1, 1, 2, 'ыавыаываыа', NULL, '2026-04-13 02:20:26', '2026-04-13 02:20:26'),
(3, 3, 3, 2, 1, 1, 2, 'аыавыаыва', NULL, '2026-04-13 02:20:41', '2026-04-13 02:20:41'),
(4, 5, 3, 1, 2, 1, NULL, 'аываываываыува', NULL, '2026-04-13 02:20:48', '2026-04-13 02:21:28');

-- --------------------------------------------------------

--
-- Структура таблицы `equipment_request_statuses`
--

CREATE TABLE `equipment_request_statuses` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `code` varchar(32) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `equipment_request_statuses`
--

INSERT INTO `equipment_request_statuses` (`id`, `code`) VALUES
(2, 'approved'),
(1, 'pending'),
(3, 'rejected');

-- --------------------------------------------------------

--
-- Структура таблицы `equipment_request_types`
--

CREATE TABLE `equipment_request_types` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `code` varchar(32) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `equipment_request_types`
--

INSERT INTO `equipment_request_types` (`id`, `code`) VALUES
(2, 'move'),
(1, 'writeoff');

-- --------------------------------------------------------

--
-- Структура таблицы `equipment_types`
--

CREATE TABLE `equipment_types` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `equipment_types`
--

INSERT INTO `equipment_types` (`id`, `name`, `deleted_at`) VALUES
(1, 'Холодильник-сейф', NULL),
(2, 'Шкаф холодильный фармацефтический', NULL),
(3, 'Стерилизатор ИК', NULL),
(4, 'Стерилизатор паровой (автоклав)', NULL),
(5, 'Стерилизатор паровой кассетный', NULL),
(6, 'Стерилизатор озоновый', NULL);

-- --------------------------------------------------------

--
-- Структура таблицы `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `groups`
--

CREATE TABLE `groups` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(150) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `group_user`
--

CREATE TABLE `group_user` (
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `group_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_roles_users_cache_and_queue_tables', 1),
(2, '0001_01_01_000001_create_reference_catalogs_and_equipment_table', 1),
(3, '0001_01_01_000002_create_equipment_documents_and_images_tables', 1),
(4, '0001_01_01_000003_create_groups_users_link_requests_reports_and_activity_logs', 1),
(5, '0001_01_01_000004_create_report_layout_and_request_tables', 1),
(6, '0001_01_01_000005_add_registry_number_to_requests_table', 2);

-- --------------------------------------------------------

--
-- Структура таблицы `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `reports`
--

CREATE TABLE `reports` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `body` text DEFAULT NULL,
  `report_date` date DEFAULT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `requests`
--

CREATE TABLE `requests` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `registry_number` int(10) UNSIGNED NOT NULL,
  `data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`data`)),
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `checked_by` bigint(20) UNSIGNED DEFAULT NULL,
  `request_layout_id` bigint(20) UNSIGNED NOT NULL,
  `scores` decimal(4,2) DEFAULT NULL,
  `refusal` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `requests`
--

INSERT INTO `requests` (`id`, `registry_number`, `data`, `created_by`, `checked_by`, `request_layout_id`, `scores`, `refusal`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, '{\"field_text_1\":\"<p style=\\\"text-align: center;\\\">\\u0441\\u0442\\u0435\\u0440\\u0438\\u043b\\u0438\\u0437\\u0430\\u0442\\u043e\\u0440 \\u043f\\u0430\\u0440\\u043e\\u0432\\u043e\\u0439 \\u0413\\u041a-100-3, \\u0418\\u043d\\u0432 \\u2116 \\u041a08104053, \\u041f\\u0440\\u043e\\u0438\\u0437\\u0432\\u043e\\u0434\\u0441\\u0442\\u0432\\u043e: 2012 \\u0433.<\\/p>\",\"field_num_1\":\"<p style=\\\"text-align: center;\\\">\\u0424\\u0413\\u0411\\u041e\\u0423 \\u0412\\u041e \\u0418\\u0413\\u041c\\u0423 \\u041c\\u0438\\u043d\\u0437\\u0434\\u0440\\u0430\\u0432\\u0430 \\u0420\\u043e\\u0441\\u0441\\u0438\\u0438<\\/p>\",\"field_select_1\":\"<p>\\u043c\\u0435\\u0434\\u0438\\u0446\\u0438\\u0441\\u043a\\u043e\\u0435 \\u0438\\u0437\\u0434\\u0435\\u043b\\u0438\\u0435 \\u043d\\u0435\\u0438\\u0441\\u043f\\u0440\\u0430\\u0432\\u043d\\u043e, \\u043d\\u0430\\u0445\\u043e\\u0434\\u0438\\u0442\\u0441\\u044f \\u0432 \\u0438\\u0437\\u043d\\u043e\\u0448\\u0435\\u043d\\u043e\\u043c \\u0441\\u043e\\u0441\\u0442\\u043e\\u044f\\u043d\\u0438\\u0438, \\u0440\\u0430\\u0437\\u0440\\u0443\\u0448\\u0435\\u043d\\u0438\\u0435 \\u043f\\u043b\\u0430\\u0441\\u0442\\u0438\\u043a\\u043e\\u0432\\u044b\\u0445 \\u0434\\u0435\\u0442\\u0430\\u043b\\u0435\\u0439 \\u043a\\u043e\\u0440\\u043f\\u0443\\u0441\\u0430 \\u0438\\u0437-\\u0437\\u0430 \\u043e\\u0431\\u0440\\u0430\\u0431\\u043e\\u0442\\u043a\\u0438 \\u0434\\u0435\\u0437\\u0438\\u043d\\u0444\\u0438\\u0446\\u0438\\u0440\\u0443\\u044e\\u0449\\u0435\\u043c\\u0438 \\u0441\\u0440\\u0435\\u0434\\u0441\\u0442\\u0432\\u0430\\u043c\\u0438, \\u043a\\u043e\\u0440\\u0440\\u043e\\u0437\\u0438\\u044f \\u043c\\u0435\\u0442\\u0430\\u043b\\u0438\\u0447\\u0435\\u0441\\u043a\\u0438\\u0445 \\u0447\\u0430\\u0441\\u0442\\u0435\\u0439 \\u0438\\u0437\\u0434\\u0435\\u043b\\u0438\\u044f, \\u0440\\u0435\\u043c\\u043e\\u043d\\u0442\\u0443 \\u043d\\u0435 \\u043f\\u043e\\u0434\\u043b\\u0435\\u0436\\u0438\\u0442.<\\/p>\",\"field_1776074136303_kdhnu\":\"<p>\\u041d\\u0435\\u0432\\u043e\\u0437\\u043c\\u043e\\u0436\\u043d\\u0430 \\u0438\\u0437-\\u0437\\u0430 \\u043f\\u043e\\u043b\\u043d\\u043e\\u0433\\u043e \\u0444\\u0438\\u0437\\u0438\\u0447\\u0435\\u0441\\u043a\\u043e\\u0433\\u043e \\u0438\\u0437\\u043d\\u043e\\u0441\\u0430.<\\/p>\",\"field_1776074150437_v05nn\":\"<p>\\u043a \\u0441\\u043f\\u0438\\u0441\\u0430\\u043d\\u0438\\u044e<\\/p>\",\"recipient_user_id\":1}', 1, NULL, 1, NULL, NULL, '2026-04-13 02:00:34', '2026-04-13 02:01:30', NULL),
(2, 2, '{\"field_select_1\":\"1\",\"recipient_user_id\":1}', 1, NULL, 2, NULL, NULL, '2026-04-13 02:25:29', '2026-04-13 02:37:06', '2026-04-13 02:37:06'),
(3, 3, '{\"header_overrides\":{\"hdr_1776076586108_3xujwtp\":\"\\u0413\\u0430\\u0439\\u0434\\u0430\\u0440\\u043e\\u0432\\u0443 \\u0413.\\u041c.\",\"hdr_1776076583942_ct2w73b\":\"\\u0415\\u0444\\u0430\\u0440\\u043e\\u0432\\u043e\\u0439 \\u041e.\\u041d.\"},\"recipient_user_id\":1}', 1, NULL, 2, NULL, NULL, '2026-04-13 02:37:19', '2026-04-13 02:42:10', '2026-04-13 02:42:10'),
(4, 4, '{\"header_overrides\":{\"hdr_1776076586108_3xujwtp\":\"\\u0413\\u0430\\u0439\\u0434\\u0430\\u0440\\u043e\\u0432\\u0443 \\u0413.\\u041c.\",\"hdr_1776076583942_ct2w73b\":\"\\u0415\\u0444\\u0430\\u0440\\u043e\\u0432\\u043e\\u0439 \\u041e.\\u041d.\"},\"recipient_user_id\":1}', 1, NULL, 2, NULL, NULL, '2026-04-13 02:42:23', '2026-04-13 02:42:23', NULL),
(5, 5, '{\"header_overrides\":{\"hdr_1776077639325_4yuqf3h\":\"\\u0413\\u0430\\u0439\\u0434\\u0430\\u0440\\u043e\\u0432\\u0443 \\u0413.\\u041c.\",\"hdr_1776077639325_l9vjnr5\":\"\\u041a\\u0443\\u0447\\u0435\\u043d\\u043e\\u0432\\u043e\\u0439 \\u042e.\\u0412.\"}}', 1, NULL, 3, NULL, NULL, '2026-04-13 02:55:28', '2026-04-13 02:55:28', NULL);

-- --------------------------------------------------------

--
-- Структура таблицы `request_layout`
--

CREATE TABLE `request_layout` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `schema` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`schema`)),
  `scores` decimal(4,2) DEFAULT NULL,
  `has_header` tinyint(1) NOT NULL DEFAULT 0,
  `type` varchar(32) NOT NULL DEFAULT 'pdf',
  `version` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `approver_id` bigint(20) UNSIGNED DEFAULT NULL,
  `user_assigner_id` bigint(20) UNSIGNED DEFAULT NULL,
  `division_assigner_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `request_layout`
--

INSERT INTO `request_layout` (`id`, `title`, `schema`, `scores`, `has_header`, `type`, `version`, `approver_id`, `user_assigner_id`, `division_assigner_id`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'АКТ_Контроля', '{\"header\":{\"sections\":[{\"align\":\"center\",\"bold\":true,\"font_size_pt\":11,\"font_family\":\"DejaVu Serif\",\"lines\":[\"\\u0424\\u0415\\u0414\\u0415\\u0420\\u0410\\u041b\\u042c\\u041d\\u041e\\u0415 \\u0410\\u0413\\u0415\\u041d\\u0422\\u0421\\u0422\\u0412\\u041e\",\"\\u041f\\u041e \\u0422\\u0415\\u0425\\u041d\\u0418\\u0427\\u0415\\u0421\\u041a\\u041e\\u041c\\u0423 \\u0420\\u0415\\u0413\\u0423\\u041b\\u0418\\u0420\\u041e\\u0412\\u0410\\u041d\\u0418\\u042e \\u0418 \\u041c\\u0415\\u0422\\u0420\\u041e\\u041b\\u041e\\u0413\\u0418\\u0418\"]},{\"align\":\"center\",\"bold\":true,\"font_size_pt\":11,\"font_family\":\"DejaVu Serif\",\"lines\":[\"\\u0424\\u0411\\u0423 \\u00ab\\u0413\\u043e\\u0441\\u0443\\u0434\\u0430\\u0440\\u0441\\u0442\\u0432\\u0435\\u043d\\u043d\\u044b\\u0439 \\u0440\\u0435\\u0433\\u0438\\u043e\\u043d\\u0430\\u043b\\u044c\\u043d\\u044b\\u0439 \\u0446\\u0435\\u043d\\u0442\\u0440 \\u0441\\u0442\\u0430\\u043d\\u0434\\u0430\\u0440\\u0442\\u0438\\u0437\\u0430\\u0446\\u0438\\u0438,\",\"\\u043c\\u0435\\u0442\\u0440\\u043e\\u043b\\u043e\\u0433\\u0438\\u0438 \\u0438 \\u0438\\u0441\\u043f\\u044b\\u0442\\u0430\\u043d\\u0438\\u0439 \\u0432 \\u0418\\u0440\\u043a\\u0443\\u0442\\u0441\\u043a\\u043e\\u0439 \\u043e\\u0431\\u043b\\u0430\\u0441\\u0442\\u0438\\u00bb\"]},{\"align\":\"center\",\"bold\":true,\"font_size_pt\":10,\"font_family\":\"DejaVu Serif\",\"lines\":[\"\\u0410\\u041a\\u0422\",\"\\u043a\\u043e\\u043d\\u0442\\u0440\\u043e\\u043b\\u044f \\u0442\\u0435\\u0445\\u043d\\u0438\\u0447\\u0435\\u0441\\u043a\\u043e\\u0433\\u043e \\u0441\\u043e\\u0441\\u0442\\u043e\\u044f\\u043d\\u0438\\u044f\",\"\\u0438\\u0437\\u0434\\u0435\\u043b\\u0438\\u0439 \\u043c\\u0435\\u0434\\u0438\\u0446\\u0438\\u043d\\u0441\\u043a\\u043e\\u0439 \\u0442\\u0435\\u0445\\u043d\\u0438\\u043a\\u0438\"]}]},\"fields\":[{\"id\":\"field_text_1\",\"name\":\"(\\u043d\\u0430\\u0438\\u043c\\u0435\\u043d\\u043e\\u0432\\u0430\\u043d\\u0438\\u0435, \\u043c\\u043e\\u0434\\u0435\\u043b\\u044c, \\u0438\\u043d\\u0432\\u0435\\u043d\\u0442\\u0430\\u0440\\u043d\\u044b\\u0439 \\u043d\\u043e\\u043c\\u0435\\u0440, \\u0433\\u043e\\u0434 \\u0432\\u044b\\u043f\\u0443\\u0441\\u043a\\u0430)\",\"type\":\"text\"},{\"id\":\"field_num_1\",\"name\":\"(\\u043d\\u0430\\u0438\\u043c\\u0435\\u043d\\u043e\\u0432\\u0430\\u043d\\u0438\\u0435 \\u0443\\u0447\\u0440\\u0435\\u0436\\u0434\\u0435\\u043d\\u0438\\u044f \\u0437\\u0434\\u0440\\u0430\\u0432\\u043e\\u043e\\u0445\\u0440\\u0430\\u043d\\u0435\\u043d\\u0438\\u044f)\",\"type\":\"text\"},{\"id\":\"field_select_1\",\"name\":\"\\u0412\\u0432\\u0435\\u0434\\u0438\\u0442\\u0435 \\u0432\\u044b\\u044f\\u0432\\u043b\\u0435\\u043d\\u043d\\u044b\\u0435 \\u043d\\u0435\\u0438\\u0441\\u043f\\u0440\\u0430\\u0432\\u043d\\u043e\\u0441\\u0442\\u0438\",\"type\":\"text\"},{\"id\":\"field_1776074136303_kdhnu\",\"name\":\"\\u0412\\u0432\\u0435\\u0434\\u0438\\u0442\\u0435 \\u043f\\u0440\\u0438\\u0447\\u0438\\u043d\\u044b\",\"type\":\"text\"},{\"id\":\"field_1776074150437_v05nn\",\"name\":\"\\u0412\\u0432\\u0435\\u0434\\u0438\\u0442\\u0435 \\u0440\\u0435\\u043a\\u043e\\u043c\\u0435\\u043d\\u0434\\u0430\\u0446\\u0438\\u0438\",\"type\":\"text\"}],\"document_title\":\"\",\"document_subtitle\":\"\",\"document_title_font_size_pt\":18,\"document_subtitle_font_size_pt\":12,\"body_default_font_family\":\"DejaVu Serif\",\"body_default_font_size_pt\":11,\"body_line_height\":1.35,\"body_html\":\"<p>\\u041d\\u0430 \\u043f\\u043b\\u0430\\u043d\\u043e\\u0432\\u0443\\u044e\\/\\u0432\\u043d\\u0435\\u043f\\u043b\\u0430\\u043d\\u043e\\u0432\\u0443\\u044e \\u0442\\u0435\\u0445\\u043d\\u0438\\u0447\\u0435\\u0441\\u043a\\u0443\\u044e \\u044d\\u043a\\u0441\\u043f\\u0435\\u0440\\u0442\\u0438\\u0437\\u0443 \\u0431\\u044b\\u043b\\u043e \\u043f\\u0440\\u0435\\u0434\\u0441\\u0442\\u0430\\u0432\\u043b\\u0435\\u043d\\u043e:<\\/p><p style=\\\"text-align: center;\\\">{{field:field_text_1}}&nbsp;<br><\\/p><p style=\\\"text-align: left;\\\">\\u043d\\u0430\\u0445\\u043e\\u0434\\u044f\\u0449\\u0435\\u0435\\u0441\\u044f \\u043d\\u0430 \\u0431\\u0430\\u043b\\u0430\\u043d\\u0441\\u0435 \\u0432:<\\/p><p style=\\\"text-align: center;\\\">{{field:field_num_1}}&nbsp;<br><\\/p><p style=\\\"text-align: center;\\\">\\u0412 \\u0440\\u0435\\u0437\\u0443\\u043b\\u044c\\u0442\\u0430\\u0442\\u0435 \\u043a\\u043e\\u043d\\u0442\\u0440\\u043e\\u043b\\u044f \\u0442\\u0435\\u0445\\u043d\\u0438\\u0447\\u0435\\u0441\\u043a\\u043e\\u0433\\u043e \\u0441\\u043e\\u0441\\u0442\\u043e\\u044f\\u043d\\u0438\\u044f<\\/p><p style=\\\"text-align: left;\\\">\\u0432\\u044b\\u044f\\u0432\\u043b\\u0435\\u043d\\u043e \\u0441\\u043b\\u0435\\u0434\\u0443\\u044e\\u0449\\u0435\\u0435:<\\/p><p style=\\\"text-align: left;\\\">{{field:field_select_1}}&nbsp;<br><\\/p><p style=\\\"text-align: left;\\\">\\u0414\\u0430\\u043b\\u044c\\u043d\\u0435\\u0439\\u0448\\u0430\\u044f \\u044d\\u043a\\u0441\\u043f\\u043b\\u0443\\u0430\\u0442\\u0430\\u0446\\u0438\\u044f \\u0432\\u044b\\u0448\\u0435 \\u043d\\u0430\\u0437\\u0432\\u0430\\u043d\\u043d\\u043e\\u0439 \\u043c\\u0435\\u0434\\u0438\\u0446\\u0438\\u043d\\u0441\\u043a\\u043e\\u0439 \\u0442\\u0435\\u0445\\u043d\\u0438\\u043a\\u0438 \\u043d\\u0435\\u0432\\u043e\\u0437\\u043c\\u043e\\u0436\\u043d\\u0430<\\/p><p style=\\\"text-align: left;\\\">(\\u043f\\u0440\\u0438\\u0447\\u0438\\u043d\\u044b):&nbsp;{{field:field_1776074136303_kdhnu}}<br><\\/p><p style=\\\"text-align: left;\\\">\\u0420\\u0435\\u043a\\u043e\\u043c\\u0435\\u043d\\u0434\\u0430\\u0446\\u0438\\u0438:&nbsp;{{field:field_1776074150437_v05nn}}&nbsp;<\\/p>\"}', NULL, 1, 'pdf', 1, NULL, NULL, NULL, '2026-04-13 01:57:47', '2026-04-13 02:01:54', NULL),
(2, 'Рапорт_на_списания_оборудования', '{\"header\":{\"sections\":[{\"align\":\"right\",\"bold\":false,\"font_size_pt\":11,\"font_family\":\"DejaVu Serif\",\"lines\":[\"\\u0413\\u043b\\u0430\\u0432\\u043d\\u043e\\u043c\\u0443 \\u0432\\u0440\\u0430\\u0447\\u0443\",\"\\u041a\\u043b\\u0438\\u043d\\u0438\\u043a \\u0424\\u0413\\u0411\\u041e\\u0423 \\u0412\\u041e \\u0418\\u0413\\u041c\\u0423\",{\"text\":\"\\u0413\\u0430\\u0439\\u0434\\u0430\\u0440\\u043e\\u0432\\u0443 \\u0413.\\u041c.\",\"editable\":true,\"line_id\":\"hdr_1776076586108_3xujwtp\"},\"\\u043e\\u0442\",\"\\u0441\\u0442\\u0430\\u0440\\u0448\\u0435\\u0439 \\u043c\\u0435\\u0434\\u0441\\u0435\\u0441\\u0442\\u0440\\u044b\",\"\\u0434\\u0435\\u0440\\u043c\\u0430\\u0442\\u043e\\u043b\\u043e\\u0433\\u0438\\u0447\\u0435\\u0441\\u043a\\u043e\\u0433\\u043e\",\"\\u043e\\u0442\\u0434\\u0435\\u043b\\u0435\\u043d\\u0438\\u044f\",{\"text\":\"\\u0415\\u0444\\u0430\\u0440\\u043e\\u0432\\u043e\\u0439 \\u041e.\\u041d.\",\"editable\":true,\"line_id\":\"hdr_1776076583942_ct2w73b\"}]}]},\"fields\":[],\"document_title\":\"\",\"document_subtitle\":\"\\u0420\\u0430\\u043f\\u043e\\u0440\\u0442\",\"document_title_font_size_pt\":18,\"document_subtitle_font_size_pt\":12,\"body_default_font_family\":\"DejaVu Serif\",\"body_default_font_size_pt\":11,\"body_line_height\":1.35,\"body_html\":\"<p style=\\\"text-align: center;\\\"><span style=\\\"font-size:14.0pt;line-height:115%;\\nfont-family:&quot;Times New Roman&quot;,serif;mso-fareast-font-family:&quot;Times New Roman&quot;;\\nmso-ansi-language:RU;mso-fareast-language:ZH-CN;mso-bidi-language:AR-SA\\\"><span style=\\\"font-family: &quot;Times New Roman&quot;, Times, serif; font-size: 14pt;\\\">\\u041f\\u0440\\u043e\\u0448\\u0443\\n\\u0412\\u0430\\u0448\\u0435\\u0433\\u043e \\u0440\\u0430\\u0437\\u0440\\u0435\\u0448\\u0435\\u043d\\u0438\\u044f \\u043d\\u0430 \\u0441\\u043f\\u0438\\u0441\\u0430\\u043d\\u0438\\u0435 \\u043e\\u0441\\u043d\\u043e\\u0432\\u043d\\u044b\\u0445 \\u0441\\u0440\\u0435\\u0434\\u0441\\u0442\\u0432:<\\/span><\\/span><\\/p><p style=\\\"text-align: center;\\\"><span style=\\\"font-size:14.0pt;line-height:115%;\\nfont-family:&quot;Times New Roman&quot;,serif;mso-fareast-font-family:&quot;Times New Roman&quot;;\\nmso-ansi-language:RU;mso-fareast-language:ZH-CN;mso-bidi-language:AR-SA\\\"><span style=\\\"font-family: &quot;Times New Roman&quot;, Times, serif; font-size: 14pt;\\\">{{sys.writeoff_equipment_list}}&nbsp;<br><\\/span><\\/span><\\/p>\",\"pdf_footer\":{\"style\":\"rapport_three\",\"head_user_id\":2,\"engineer_user_id\":1}}', NULL, 1, 'pdf', 1, NULL, NULL, NULL, '2026-04-13 02:20:01', '2026-04-13 03:13:25', NULL),
(3, 'Рапорт_на_перемещения_оборудования', '{\"header\":{\"sections\":[{\"align\":\"right\",\"bold\":false,\"font_size_pt\":11,\"font_family\":\"DejaVu Serif\",\"lines\":[\"\\u0413\\u043b\\u0430\\u0432\\u043d\\u043e\\u043c\\u0443 \\u0432\\u0440\\u0430\\u0447\\u0443\",\"\\u041a\\u043b\\u0438\\u043d\\u0438\\u043a \\u0424\\u0413\\u0411\\u041e\\u0423 \\u0412\\u041e \\u0418\\u0413\\u041c\\u0423\",{\"text\":\"\\u0413\\u0430\\u0439\\u0434\\u0430\\u0440\\u043e\\u0432\\u0443 \\u0413.\\u041c.\",\"editable\":true,\"line_id\":\"hdr_1776077639325_4yuqf3h\"},\"\\u043e\\u0442\",\"\\u0441\\u0442\\u0430\\u0440\\u0448\\u0435\\u0439 \\u043c\\u0435\\u0434\\u0441\\u0435\\u0441\\u0442\\u0440\\u044b\",\"\\u0434\\u0435\\u0440\\u043c\\u0430\\u0442\\u043e\\u043b\\u043e\\u0433\\u0438\\u0447\\u0435\\u0441\\u043a\\u043e\\u0433\\u043e\",\"\\u043e\\u0442\\u0434\\u0435\\u043b\\u0435\\u043d\\u0438\\u044f\",{\"text\":\"\\u0415\\u0444\\u0430\\u0440\\u043e\\u0432\\u043e\\u0439 \\u041e.\\u041d.\",\"editable\":true,\"line_id\":\"hdr_1776077639325_l9vjnr5\"}]}]},\"fields\":[],\"document_title\":\"\",\"document_subtitle\":\"\\u0420\\u0430\\u043f\\u043e\\u0440\\u0442\",\"document_title_font_size_pt\":18,\"document_subtitle_font_size_pt\":14,\"body_default_font_family\":\"DejaVu Serif\",\"body_default_font_size_pt\":11,\"body_line_height\":1.35,\"body_html\":\"<p style=\\\"text-align: left;\\\"><span style=\\\"font-size:14.0pt;line-height:115%;\\nfont-family:&quot;Times New Roman&quot;,serif;mso-fareast-font-family:Calibri;mso-fareast-theme-font:\\nminor-latin;mso-ansi-language:RU;mso-fareast-language:EN-US;mso-bidi-language:\\nAR-SA\\\">\\u041f\\u0440\\u043e\\u0448\\u0443 \\u0412\\u0430\\u0448\\u0435\\u0433\\u043e \\u0440\\u0430\\u0437\\u0440\\u0435\\u0448\\u0435\\u043d\\u0438\\u044f \\u043f\\u0435\\u0440\\u0435\\u043c\\u0435\\u0441\\u0442\\u0438\\u0442\\u044c \\u043d\\u0430 \\u0441\\u043a\\u043b\\u0430\\u0434-\\u0445\\u0440\\u0430\\u043d\\u0435\\u043d\\u0438\\u044f \\u0441\\u043b\\u0435\\u0434\\u0443\\u044e\\u0449\\u0438\\u0435\\n\\u043e\\u0431\\u043b\\u0443\\u0447\\u0430\\u0442\\u0435\\u043b\\u0438 \\u0434\\u043b\\u044f \\u043e\\u0436\\u0438\\u0434\\u0430\\u043d\\u0438\\u044f \\u0441\\u043f\\u0438\\u0441\\u0430\\u043d\\u0438\\u044f, \\u0442\\u0430\\u043a \\u043a\\u0430\\u043a \\u0432 \\u043e\\u0442\\u0434\\u0435\\u043b\\u0435\\u043d\\u0438\\u0438 \\u043c\\u0435\\u0441\\u0442 \\u0434\\u043b\\u044f \\u0445\\u0440\\u0430\\u043d\\u0435\\u043d\\u0438\\u044f \\u043d\\u0435\\u0442.<\\/span><\\/p><p style=\\\"text-align: left;\\\"><span style=\\\"font-size:14.0pt;line-height:115%;\\nfont-family:&quot;Times New Roman&quot;,serif;mso-fareast-font-family:Calibri;mso-fareast-theme-font:\\nminor-latin;mso-ansi-language:RU;mso-fareast-language:EN-US;mso-bidi-language:\\nAR-SA\\\">{{sys.move_equipment_list}}&nbsp;<br><\\/span><\\/p>\",\"pdf_footer\":{\"style\":\"rapport_two\",\"head_user_id\":2,\"engineer_user_id\":null}}', NULL, 1, 'pdf', 1, NULL, NULL, NULL, '2026-04-13 02:55:09', '2026-04-13 03:13:13', NULL);

-- --------------------------------------------------------

--
-- Структура таблицы `roles`
--

CREATE TABLE `roles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(50) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `roles`
--

INSERT INTO `roles` (`id`, `name`, `created_at`, `updated_at`) VALUES
(1, 'admin', '2026-04-13 00:53:45', '2026-04-13 00:53:45'),
(2, 'user', '2026-04-13 00:53:45', '2026-04-13 00:53:45'),
(3, 'senior_nurse', '2026-04-13 00:53:45', '2026-04-13 00:53:45'),
(4, 'nurse', '2026-04-13 00:53:45', '2026-04-13 00:53:45'),
(5, 'accountant', '2026-04-13 00:53:45', '2026-04-13 00:53:45'),
(6, 'disposal_officer', '2026-04-13 00:53:45', '2026-04-13 00:53:45');

-- --------------------------------------------------------

--
-- Структура таблицы `service_organizations`
--

CREATE TABLE `service_organizations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `contact_info` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('6mhsCu1gk1bzJEphy4vcFgJZKtGA15w1li5wv9Vg', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 YaBrowser/26.3.0.0 Safari/537.36', 'YTo0OntzOjY6Il90b2tlbiI7czo0MDoiRHo4WXZJcUpoR1FRSndFa2pVdnpsREJ3YXBnM0RseVF3UHhJQ1BqaSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NDQ6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9hZG1pbi9hY3Rpdml0eS1hcmNoaXZlIjtzOjU6InJvdXRlIjtzOjIyOiJhZG1pbi5hY3Rpdml0eS1hcmNoaXZlIjt9czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTt9', 1776267430),
('TFN603nQM1tWvdsagbLgoY9pMPW3EEm2cJT3scAC', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Cursor/3.0.16 Chrome/142.0.7444.265 Electron/39.8.1 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiWFlyRmZaeG16a2lGOUx1QktxZlE2T2RwRUFXQkVwRXprOGh5M1VTVCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9sb2dpbiI7czo1OiJyb3V0ZSI7czo1OiJsb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1776267048);

-- --------------------------------------------------------

--
-- Структура таблицы `suppliers`
--

CREATE TABLE `suppliers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `contact_info` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `username` varchar(255) DEFAULT NULL,
  `first_name` varchar(255) DEFAULT NULL,
  `last_name` varchar(255) DEFAULT NULL,
  `patronymic` varchar(255) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role_id` bigint(20) UNSIGNED DEFAULT NULL,
  `last_login` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `date_joined` timestamp NULL DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `users`
--

INSERT INTO `users` (`id`, `username`, `first_name`, `last_name`, `patronymic`, `email`, `email_verified_at`, `password`, `role_id`, `last_login`, `is_active`, `date_joined`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, NULL, 'Елена', 'Лиханова', 'Юрьевна', 'admin@example.com', '2026-04-13 00:53:45', '$2y$12$Q23HrxNH.8cw6cODsX0leeqiY83jYgKEH728majJlnI/QyL7VYbfO', 1, NULL, 1, '2026-04-13 00:53:45', NULL, '2026-04-13 00:53:45', '2026-04-13 00:55:47'),
(2, NULL, 'Пользователь', 'Просмотр', NULL, 'user@example.com', '2026-04-13 00:53:45', '$2y$12$zC9BbOOeWfBSoj9muhtFeO7vBV7vPWoG.7cWA8yyy63Wsi/RtrOnq', 2, NULL, 1, '2026-04-13 00:53:45', NULL, '2026-04-13 00:53:46', '2026-04-13 00:53:46'),
(3, NULL, 'Старшая', 'Медсестра', NULL, 'nurse@example.com', '2026-04-13 00:53:45', '$2y$12$xImutV2SszTAE854crxbV.E/xvqOAgMfjyi5W9ZBTqBVrsdQMJRkO', 3, NULL, 1, '2026-04-13 00:53:45', NULL, '2026-04-13 00:53:46', '2026-04-13 00:53:46'),
(4, NULL, 'Тестовый', 'Бухгалтер', NULL, 'accountant@example.com', '2026-04-13 00:53:45', '$2y$12$qg4hGw1fLEXJpyK5RY78XeNGZ/YtQotsODZjyRngmvwnNTgvFmAlW', 5, NULL, 1, '2026-04-13 00:53:45', NULL, '2026-04-13 00:53:46', '2026-04-13 00:53:46');

-- --------------------------------------------------------

--
-- Структура таблицы `utilization_states`
--

CREATE TABLE `utilization_states` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `code` varchar(32) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `utilization_states`
--

INSERT INTO `utilization_states` (`id`, `code`) VALUES
(1, 'none'),
(2, 'utilized');

-- --------------------------------------------------------

--
-- Структура таблицы `writeoff_states`
--

CREATE TABLE `writeoff_states` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `code` varchar(32) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Дамп данных таблицы `writeoff_states`
--

INSERT INTO `writeoff_states` (`id`, `code`) VALUES
(3, 'approved'),
(1, 'none'),
(2, 'requested');

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `activity_logs_user_id_foreign` (`user_id`),
  ADD KEY `activity_logs_entity_type_entity_id_index` (`entity_type`,`entity_id`),
  ADD KEY `activity_logs_occurred_at_index` (`occurred_at`);

--
-- Индексы таблицы `cabinets`
--
ALTER TABLE `cabinets`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_expiration_index` (`expiration`);

--
-- Индексы таблицы `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_locks_expiration_index` (`expiration`);

--
-- Индексы таблицы `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `equipment`
--
ALTER TABLE `equipment`
  ADD PRIMARY KEY (`id`),
  ADD KEY `equipment_equipment_type_id_foreign` (`equipment_type_id`),
  ADD KEY `equipment_department_id_foreign` (`department_id`),
  ADD KEY `equipment_cabinet_id_foreign` (`cabinet_id`),
  ADD KEY `equipment_group_id_foreign` (`group_id`),
  ADD KEY `equipment_equipment_condition_id_foreign` (`equipment_condition_id`),
  ADD KEY `equipment_supplier_id_foreign` (`supplier_id`),
  ADD KEY `equipment_service_organization_id_foreign` (`service_organization_id`),
  ADD KEY `equipment_writeoff_state_id_foreign` (`writeoff_state_id`),
  ADD KEY `equipment_utilization_state_id_foreign` (`utilization_state_id`),
  ADD KEY `equipment_number_index` (`number`);

--
-- Индексы таблицы `equipment_conditions`
--
ALTER TABLE `equipment_conditions`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `equipment_documents`
--
ALTER TABLE `equipment_documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `equipment_documents_document_type_id_foreign` (`document_type_id`),
  ADD KEY `equipment_documents_equipment_id_foreign` (`equipment_id`);

--
-- Индексы таблицы `equipment_document_types`
--
ALTER TABLE `equipment_document_types`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `equipment_document_types_code_unique` (`code`);

--
-- Индексы таблицы `equipment_images`
--
ALTER TABLE `equipment_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `equipment_images_equipment_id_foreign` (`equipment_id`);

--
-- Индексы таблицы `equipment_requests`
--
ALTER TABLE `equipment_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `equipment_requests_equipment_id_foreign` (`equipment_id`),
  ADD KEY `equipment_requests_user_id_foreign` (`user_id`),
  ADD KEY `equipment_requests_request_type_id_foreign` (`request_type_id`),
  ADD KEY `equipment_requests_request_status_id_foreign` (`request_status_id`),
  ADD KEY `equipment_requests_from_department_id_foreign` (`from_department_id`),
  ADD KEY `equipment_requests_to_department_id_foreign` (`to_department_id`);

--
-- Индексы таблицы `equipment_request_statuses`
--
ALTER TABLE `equipment_request_statuses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `equipment_request_statuses_code_unique` (`code`);

--
-- Индексы таблицы `equipment_request_types`
--
ALTER TABLE `equipment_request_types`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `equipment_request_types_code_unique` (`code`);

--
-- Индексы таблицы `equipment_types`
--
ALTER TABLE `equipment_types`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Индексы таблицы `groups`
--
ALTER TABLE `groups`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `group_user`
--
ALTER TABLE `group_user`
  ADD PRIMARY KEY (`user_id`,`group_id`),
  ADD KEY `group_user_group_id_foreign` (`group_id`);

--
-- Индексы таблицы `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Индексы таблицы `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Индексы таблицы `reports`
--
ALTER TABLE `reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `reports_user_id_foreign` (`user_id`);

--
-- Индексы таблицы `requests`
--
ALTER TABLE `requests`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `requests_registry_number_unique` (`registry_number`),
  ADD KEY `requests_created_by_foreign` (`created_by`),
  ADD KEY `requests_checked_by_foreign` (`checked_by`),
  ADD KEY `requests_request_layout_id_foreign` (`request_layout_id`);

--
-- Индексы таблицы `request_layout`
--
ALTER TABLE `request_layout`
  ADD PRIMARY KEY (`id`),
  ADD KEY `request_layout_approver_id_foreign` (`approver_id`),
  ADD KEY `request_layout_user_assigner_id_foreign` (`user_assigner_id`),
  ADD KEY `request_layout_division_assigner_id_foreign` (`division_assigner_id`);

--
-- Индексы таблицы `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `roles_name_unique` (`name`);

--
-- Индексы таблицы `service_organizations`
--
ALTER TABLE `service_organizations`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Индексы таблицы `suppliers`
--
ALTER TABLE `suppliers`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD KEY `users_role_id_foreign` (`role_id`);

--
-- Индексы таблицы `utilization_states`
--
ALTER TABLE `utilization_states`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `utilization_states_code_unique` (`code`);

--
-- Индексы таблицы `writeoff_states`
--
ALTER TABLE `writeoff_states`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `writeoff_states_code_unique` (`code`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT для таблицы `cabinets`
--
ALTER TABLE `cabinets`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT для таблицы `departments`
--
ALTER TABLE `departments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT для таблицы `equipment`
--
ALTER TABLE `equipment`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT для таблицы `equipment_conditions`
--
ALTER TABLE `equipment_conditions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `equipment_documents`
--
ALTER TABLE `equipment_documents`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT для таблицы `equipment_document_types`
--
ALTER TABLE `equipment_document_types`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT для таблицы `equipment_images`
--
ALTER TABLE `equipment_images`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT для таблицы `equipment_requests`
--
ALTER TABLE `equipment_requests`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT для таблицы `equipment_request_statuses`
--
ALTER TABLE `equipment_request_statuses`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT для таблицы `equipment_request_types`
--
ALTER TABLE `equipment_request_types`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT для таблицы `equipment_types`
--
ALTER TABLE `equipment_types`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT для таблицы `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `groups`
--
ALTER TABLE `groups`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT для таблицы `reports`
--
ALTER TABLE `reports`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `requests`
--
ALTER TABLE `requests`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT для таблицы `request_layout`
--
ALTER TABLE `request_layout`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT для таблицы `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT для таблицы `service_organizations`
--
ALTER TABLE `service_organizations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `suppliers`
--
ALTER TABLE `suppliers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT для таблицы `utilization_states`
--
ALTER TABLE `utilization_states`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT для таблицы `writeoff_states`
--
ALTER TABLE `writeoff_states`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Ограничения внешнего ключа сохраненных таблиц
--

--
-- Ограничения внешнего ключа таблицы `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD CONSTRAINT `activity_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Ограничения внешнего ключа таблицы `equipment`
--
ALTER TABLE `equipment`
  ADD CONSTRAINT `equipment_cabinet_id_foreign` FOREIGN KEY (`cabinet_id`) REFERENCES `cabinets` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `equipment_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `equipment_equipment_condition_id_foreign` FOREIGN KEY (`equipment_condition_id`) REFERENCES `equipment_conditions` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `equipment_equipment_type_id_foreign` FOREIGN KEY (`equipment_type_id`) REFERENCES `equipment_types` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `equipment_group_id_foreign` FOREIGN KEY (`group_id`) REFERENCES `groups` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `equipment_service_organization_id_foreign` FOREIGN KEY (`service_organization_id`) REFERENCES `service_organizations` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `equipment_supplier_id_foreign` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `equipment_utilization_state_id_foreign` FOREIGN KEY (`utilization_state_id`) REFERENCES `utilization_states` (`id`),
  ADD CONSTRAINT `equipment_writeoff_state_id_foreign` FOREIGN KEY (`writeoff_state_id`) REFERENCES `writeoff_states` (`id`);

--
-- Ограничения внешнего ключа таблицы `equipment_documents`
--
ALTER TABLE `equipment_documents`
  ADD CONSTRAINT `equipment_documents_document_type_id_foreign` FOREIGN KEY (`document_type_id`) REFERENCES `equipment_document_types` (`id`),
  ADD CONSTRAINT `equipment_documents_equipment_id_foreign` FOREIGN KEY (`equipment_id`) REFERENCES `equipment` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `equipment_images`
--
ALTER TABLE `equipment_images`
  ADD CONSTRAINT `equipment_images_equipment_id_foreign` FOREIGN KEY (`equipment_id`) REFERENCES `equipment` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `equipment_requests`
--
ALTER TABLE `equipment_requests`
  ADD CONSTRAINT `equipment_requests_equipment_id_foreign` FOREIGN KEY (`equipment_id`) REFERENCES `equipment` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `equipment_requests_from_department_id_foreign` FOREIGN KEY (`from_department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `equipment_requests_request_status_id_foreign` FOREIGN KEY (`request_status_id`) REFERENCES `equipment_request_statuses` (`id`),
  ADD CONSTRAINT `equipment_requests_request_type_id_foreign` FOREIGN KEY (`request_type_id`) REFERENCES `equipment_request_types` (`id`),
  ADD CONSTRAINT `equipment_requests_to_department_id_foreign` FOREIGN KEY (`to_department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `equipment_requests_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `group_user`
--
ALTER TABLE `group_user`
  ADD CONSTRAINT `group_user_group_id_foreign` FOREIGN KEY (`group_id`) REFERENCES `groups` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `group_user_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `reports`
--
ALTER TABLE `reports`
  ADD CONSTRAINT `reports_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `requests`
--
ALTER TABLE `requests`
  ADD CONSTRAINT `requests_checked_by_foreign` FOREIGN KEY (`checked_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `requests_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `requests_request_layout_id_foreign` FOREIGN KEY (`request_layout_id`) REFERENCES `request_layout` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `request_layout`
--
ALTER TABLE `request_layout`
  ADD CONSTRAINT `request_layout_approver_id_foreign` FOREIGN KEY (`approver_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `request_layout_division_assigner_id_foreign` FOREIGN KEY (`division_assigner_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `request_layout_user_assigner_id_foreign` FOREIGN KEY (`user_assigner_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Ограничения внешнего ключа таблицы `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
