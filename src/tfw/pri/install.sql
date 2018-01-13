CREATE TABLE `${PREFIX}user` (
  `id` int(11) NOT NULL,
  `login` varchar(256) NOT NULL,
  `password` varchar(256) NOT NULL,
  `name` varchar(256) NOT NULL,
  `roles` varchar(512) NOT NULL DEFAULT '[]',
  `enabled` tinyint(1) NOT NULL,
  `creation` int(11) NOT NULL,
  `data` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


INSERT INTO `${PREFIX}user` (`id`, `login`, `password`, `name`, `roles`, `enabled`, `creation`, `data`) VALUES
(1, '${USER}', '${PASSWORD}', 'Administrator', '["USER", "POWER", "ADMIN"]', 1, ${DATE}, '{}');


ALTER TABLE `${PREFIX}user`
  ADD PRIMARY KEY (`id`),
  ADD KEY `login` (`login`(255));


ALTER TABLE `${PREFIX}user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;


