CREATE TABLE `agsc_user` (
  `id` bigint(20) NOT NULL,
  `user_group_id` bigint(20) NOT NULL,
  `username` varchar(20) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `firstname` varchar(32) DEFAULT NULL,
  `lastname` varchar(32) DEFAULT NULL,
  `email` varchar(96) DEFAULT NULL,
  `image` varchar(255) DEFAULT '',
  `ip` varchar(40) DEFAULT '',
  `status` tinyint(1) DEFAULT 0,
  `date_added` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `agsc_user`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `agsc_user`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;