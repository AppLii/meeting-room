-- 1. user テーブルのサンプルデータ
INSERT INTO `user` (`id`, `email`, `name`) VALUES
(1, 'alice@example.com', 'Alice Johnson'),
(2, 'bob@example.com', 'Bob Smith'),
(3, 'charlie@example.com', 'Charlie Brown');

-- 2. pj テーブル（num_of_rsvを削除）のサンプルデータ
INSERT INTO `pj` (`id`, `rep`, `name`, `nickname`, `max_rsv`) VALUES
(1, 1, 'Project Alpha', 'Alpha', 5),
(2, 2, 'Project Beta', 'Beta', 4);

-- 3. pj_roster テーブルのサンプルデータ
INSERT INTO `pj_roster` (`id`, `user_id`, `pj_id`) VALUES
(1, 1, 1),
(2, 2, 1),
(3, 3, 2);

-- 4. room テーブルのサンプルデータ
INSERT INTO `room` (`id`, `name`, `short_name`) VALUES
(1, 'Conference Room A', 'ConfA'),
(2, 'Meeting Room B', 'MeetB');

-- 5. blackout_definition テーブルのサンプルデータ
INSERT INTO `blackout_definition` (`id`, `name`, `start_apply_at`, `finish_apply_at`) VALUES
(1, 'Maintenance Period', '2025-03-07 00:00:00', '2025-03-07 23:59:59'),
(2, 'Holiday Closure', '2025-12-25 00:00:00', '2025-12-26 23:59:59');

-- 6. room_blackout テーブルのサンプルデータ
INSERT INTO `room_blackout` (`id`, `definition_id`, `room_id`, `start_at`, `finish_at`) VALUES
(1, 1, 1, '2025-03-07 08:00:00', '2025-03-07 12:00:00'),
(2, 2, 2, '2025-12-25 00:00:00', '2025-12-25 23:59:59');

-- 7. rsv テーブルのサンプルデータ
-- 予約がblackout期間と重ならないよう調整：
-- Reservation 1はRoom1を07:00～07:45に設定（blackout前）。
-- Reservation 2はRoom2で通常時間に設定。
-- Reservation 3はRoom1でblackout期間外（翌日）に設定。
INSERT INTO `rsv` (`id`, `pj_id`, `room_id`, `user_id`, `start_at`, `finish_at`, `note`, `rsved_at`) VALUES
(1, 1, 1, 1, '2025-03-07 07:00:00', '2025-03-07 07:45:00', 'Morning meeting', '2025-03-06 10:00:00'),
(2, 1, 2, 2, '2025-03-07 13:00:00', '2025-03-07 14:00:00', 'Project discussion', '2025-03-06 11:00:00'),
(3, 2, 1, 3, '2025-03-08 15:00:00', '2025-03-08 16:00:00', 'Client meeting', '2025-03-06 12:00:00');