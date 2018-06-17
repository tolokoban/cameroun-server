SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données :  `${PREFIX}`
--

-- --------------------------------------------------------

--
-- Structure de la table `${PREFIX}carecenter`
--

CREATE TABLE `${PREFIX}carecenter` (
  `id` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8_bin NOT NULL,
  `code` varchar(24) COLLATE utf8_bin NOT NULL COMMENT 'Code pour l''installation du client'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Structure de la table `${PREFIX}form_data`
--

CREATE TABLE `${PREFIX}form_data` (
  `user` int(11) NOT NULL,
  `consultation` int(11) NOT NULL,
  `key` varchar(64) COLLATE utf8_bin NOT NULL,
  `value` varchar(256) COLLATE utf8_bin NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Structure de la table `${PREFIX}organization`
--

CREATE TABLE `${PREFIX}organization` (
  `id` int(11) NOT NULL,
  `name` varchar(256) COLLATE utf8_bin NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Structure de la table `${PREFIX}patient`
--

CREATE TABLE `${PREFIX}patient` (
  `id` int(11) NOT NULL,
  `carecenter` int(11) NOT NULL,
  `key` varchar(64) COLLATE utf8_bin NOT NULL COMMENT 'Identifiant unique du patient'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Structure de la table `${PREFIX}patient_data`
--

CREATE TABLE `${PREFIX}patient_data` (
  `patient` int(11) NOT NULL,
  `key` varchar(64) COLLATE utf8_bin NOT NULL,
  `value` varchar(256) COLLATE utf8_bin NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Structure de la table `${PREFIX}translation`
--

CREATE TABLE `${PREFIX}translation` (
  `key` varchar(64) COLLATE utf8_bin NOT NULL,
  `lang` varchar(2) COLLATE utf8_bin NOT NULL,
  `value` varchar(255) COLLATE utf8_bin NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Structure de la table `${PREFIX}type`
--

CREATE TABLE `${PREFIX}type` (
  `organization` int(11) NOT NULL,
  `key` varchar(64) COLLATE utf8_bin NOT NULL,
  `value` text COLLATE utf8_bin NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Structure de la table `${PREFIX}user`
--

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

--
-- Contenu de la table `${PREFIX}user`
--

INSERT INTO `${PREFIX}user` (`id`, `login`, `password`, `name`, `roles`, `enabled`, `creation`, `data`) VALUES
(1, '${USER}', '${PASSWORD}', 'Administrator', '["USER", "POWER", "ADMIN"]', 1, ${DATE}, '{}');

--
-- Index pour les tables exportées
--

--
-- Index pour la table `${PREFIX}carecenter`
--
ALTER TABLE `${PREFIX}carecenter`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `${PREFIX}organization`
--
ALTER TABLE `${PREFIX}organization`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `${PREFIX}patient`
--
ALTER TABLE `${PREFIX}patient`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_carecenter` (`carecenter`),
  ADD KEY `key` (`key`);

--
-- Index pour la table `${PREFIX}patient_data`
--
ALTER TABLE `${PREFIX}patient_data`
  ADD PRIMARY KEY (`patient`,`key`);

--
-- Index pour la table `${PREFIX}translation`
--
ALTER TABLE `${PREFIX}translation`
  ADD PRIMARY KEY (`key`,`lang`),
  ADD KEY `lang` (`lang`);

--
-- Index pour la table `${PREFIX}type`
--
ALTER TABLE `${PREFIX}type`
  ADD PRIMARY KEY (`organization`,`key`);

--
-- Index pour la table `${PREFIX}user`
--
ALTER TABLE `${PREFIX}user`
  ADD PRIMARY KEY (`id`),
  ADD KEY `login` (`login`(255));

--
-- AUTO_INCREMENT pour les tables exportées
--

--
-- AUTO_INCREMENT pour la table `${PREFIX}carecenter`
--
ALTER TABLE `${PREFIX}carecenter`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT pour la table `${PREFIX}organization`
--
ALTER TABLE `${PREFIX}organization`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT pour la table `${PREFIX}patient`
--
ALTER TABLE `${PREFIX}patient`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT pour la table `${PREFIX}user`
--
ALTER TABLE `${PREFIX}user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
