-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 12, 2025 at 02:10 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `nextgen_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `categorie`
--

CREATE TABLE `categorie` (
  `id_categorie` int(11) NOT NULL,
  `nom_categorie` varchar(100) NOT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categorie`
--

INSERT INTO `categorie` (`id_categorie`, `nom_categorie`, `description`) VALUES
(1, 'Jeu-Action', 'description'),
(2, 'Jeu-Aventure', 'Jeux narratifs axés sur l’exploration, la découverte et l’immersion.'),
(3, 'Jeu-RPG', 'Jeux où l’on incarne un personnage, progresse en compétences et vit une aventure guidée par des choix.'),
(4, 'Jeu-Sport', 'Jeux simulant des activités sportives réalistes ou arcade, axés sur la compétition et la performance.'),
(5, 'Jeu-IQ', 'Jeux de réflexion conçus pour tester la logique, la mémoire et les capacités de résolution de problèmes.'),
(6, 'Jeu-Puzzle', 'Jeux basés sur la logique et la stratégie, où l’objectif est de résoudre des énigmes ou agencer des éléments.'),
(13, 'iojjkjk', 'hjghjh');

-- --------------------------------------------------------

--
-- Table structure for table `historique`
--

CREATE TABLE `historique` (
  `id_historique` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `type_action` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `date_action` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `historique`
--

INSERT INTO `historique` (`id_historique`, `id_user`, `type_action`, `description`, `date_action`) VALUES
(9, 14, 'inscription', 'Compte créé', '2025-12-12 07:18:06'),
(10, 1, 'purchase', 'Achat : Chess-master', '2025-12-12 07:20:53'),
(11, 1, 'purchase', 'Achat : Snake', '2025-12-12 07:21:26'),
(12, 1, 'purchase', 'Achat : Memo-number', '2025-12-12 08:37:39'),
(13, 15, 'login', 'Compte crépp', '2025-12-12 12:09:00'),
(16, 1, 'login', 'gamooo', '2025-12-25 14:04:00');

-- --------------------------------------------------------

--
-- Table structure for table `jeu`
--

CREATE TABLE `jeu` (
  `id_jeu` int(11) NOT NULL,
  `titre` varchar(255) NOT NULL,
  `prix` decimal(10,2) NOT NULL DEFAULT 0.00,
  `src_img` varchar(500) DEFAULT NULL,
  `video_src` varchar(500) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `id_categorie` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `jeu`
--

INSERT INTO `jeu` (`id_jeu`, `titre`, `prix`, `src_img`, `video_src`, `description`, `id_categorie`) VALUES
(1, 'Snake', 10.00, 'jeu_1764261568_69287ec0941c0.png', 'video_1764263057_6928849150bfd.mp4', 'Snake est un jeu où le serpent grandit en mangeant des fruits.\r\nÀ chaque niveau, la difficulté augmente.\r\nIl améliore votre concentration et vos réflexes.', 2),
(6, 'Chess-master', 20.00, 'jeu_1764389747_692a7373a835f.png', 'video_1764389455_692a724fa7c3c.mp4', 'game', 6),
(7, 'Copy-me', 20.00, 'jeu_1764389440_692a72402e472.png', 'video_1764389440_692a72402e737.mp4', 'game', 5),
(8, 'Asteroid-destroyer', 20.00, 'jeu_1764389698_692a7342e7d53.png', 'video_1764389312_692a71c07b1a8.mp4', 'game', 1),
(9, 'Code-master', 20.00, 'jeu_1764389288_692a71a8dab57.png', 'video_1764389288_692a71a8dae14.mp4', 'game', 6),
(10, 'IQ_test', 20.00, 'jeu_1764389553_692a72b1f0f74.png', 'video_1764389258_692a718ad84c6.mp4', 'game', 5),
(11, 'Memo-number', 20.00, 'jeu_1764389622_692a72f67ec65.png', 'video_1764389222_692a716667b6e.mp4', 'game', 5),
(12, 'Pongi', 204.00, 'jeu_1764389796_692a73a4e58bb.png', 'video_1764389197_692a714d8dd7c.mp4', 'game', 4),
(16, 'number memoryy', 20.00, 'jeu_1765257390_6937b0ae18082.png', NULL, 'a memory enhancing game', 5);

-- --------------------------------------------------------

--
-- Table structure for table `jeux_owned`
--

CREATE TABLE `jeux_owned` (
  `owned_id` int(11) NOT NULL,
  `id` int(11) NOT NULL,
  `id_jeu` int(11) NOT NULL,
  `score` int(11) DEFAULT 0,
  `date_achat` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `jeux_owned`
--

INSERT INTO `jeux_owned` (`owned_id`, `id`, `id_jeu`, `score`, `date_achat`) VALUES
(11, 1, 8, 0, '2025-12-12 04:19:54'),
(12, 1, 6, 0, '2025-12-12 07:20:53'),
(13, 1, 1, 0, '2025-12-12 07:21:26'),
(14, 1, 11, 0, '2025-12-12 08:37:39');

-- --------------------------------------------------------

--
-- Table structure for table `livraisons`
--

CREATE TABLE `livraisons` (
  `id_livraison` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `id_jeu` int(11) NOT NULL,
  `date_commande` datetime DEFAULT current_timestamp(),
  `adresse_complete` text NOT NULL,
  `position_lat` decimal(10,8) NOT NULL,
  `position_lng` decimal(11,8) NOT NULL,
  `mode_paiement` enum('credit_site','espece_livraison') NOT NULL DEFAULT 'credit_site',
  `prix_livraison` decimal(8,3) NOT NULL DEFAULT 8.000,
  `statut` enum('commandee','emballee','en_transit','livree') NOT NULL DEFAULT 'commandee'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `livraisons`
--

INSERT INTO `livraisons` (`id_livraison`, `id_user`, `id_jeu`, `date_commande`, `adresse_complete`, `position_lat`, `position_lng`, `mode_paiement`, `prix_livraison`, `statut`) VALUES
(5, 1, 1, '2025-11-27 18:14:35', 'Tunis, Tunisie', 36.84528155, 10.16565567, '', 8.000, 'commandee');

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `code` char(6) NOT NULL,
  `token` varchar(64) NOT NULL,
  `expiration` datetime NOT NULL,
  `used` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `trajets`
--

CREATE TABLE `trajets` (
  `id_trajet` int(11) NOT NULL,
  `id_livraison` int(11) NOT NULL,
  `position_lat` decimal(10,8) NOT NULL,
  `position_lng` decimal(11,8) NOT NULL,
  `date_update` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `nom` varchar(50) NOT NULL,
  `prenom` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `telephone` varchar(20) NOT NULL,
  `mdp` varchar(255) NOT NULL,
  `role` enum('user','admin') DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `photo_profil` varchar(255) DEFAULT NULL,
  `credits` decimal(10,2) DEFAULT 0.00,
  `last_login` datetime DEFAULT NULL,
  `email_verified` tinyint(1) DEFAULT 0,
  `verification_code` char(6) DEFAULT NULL,
  `verification_expires` datetime DEFAULT NULL,
  `statut` enum('actif','suspendu','banni') DEFAULT 'actif',
  `date_inscription` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `nom`, `prenom`, `email`, `telephone`, `mdp`, `role`, `created_at`, `photo_profil`, `credits`, `last_login`, `email_verified`, `verification_code`, `verification_expires`, `statut`, `date_inscription`) VALUES
(1, 'Admin', 'dhia', 'dhia@gmail.com', '12345678', '0', 'admin', '2025-11-22 14:26:15', 'user_1_1765540230.png', 99245.00, '2025-12-12 12:10:39', 0, NULL, NULL, 'actif', '2025-12-11 16:54:32'),
(14, 'boulares', 'dhia', 'dhiaboulareseddine@gmail.com', '93630066', '$2y$10$AQLYfNzAzpZB8elHAd6isOQYW2fSRmOz/VtVgxOWJGc8XZX3/P7l6', 'user', '2025-12-12 06:18:06', 'default.jpg', 0.00, NULL, 1, NULL, NULL, 'actif', '2025-12-12 07:18:06'),
(15, 'boulares', 'dhia1', 'dhiaboulares11@gmail.com', '93630066', '$2y$10$xW588TWys0hCBm.fBXnqYe5RhlATlciFIcv.mERBY1aYTcs/VkP0u', 'user', '2025-12-12 11:09:28', 'default.jpg', 0.00, NULL, 1, NULL, NULL, 'actif', '2025-12-12 12:09:28');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categorie`
--
ALTER TABLE `categorie`
  ADD PRIMARY KEY (`id_categorie`);

--
-- Indexes for table `historique`
--
ALTER TABLE `historique`
  ADD PRIMARY KEY (`id_historique`),
  ADD KEY `id_user` (`id_user`);

--
-- Indexes for table `jeu`
--
ALTER TABLE `jeu`
  ADD PRIMARY KEY (`id_jeu`),
  ADD KEY `id_categorie` (`id_categorie`);

--
-- Indexes for table `jeux_owned`
--
ALTER TABLE `jeux_owned`
  ADD PRIMARY KEY (`owned_id`),
  ADD UNIQUE KEY `unique_user_game` (`id`,`id_jeu`),
  ADD KEY `id_jeu` (`id_jeu`);

--
-- Indexes for table `livraisons`
--
ALTER TABLE `livraisons`
  ADD PRIMARY KEY (`id_livraison`),
  ADD KEY `id_user` (`id_user`),
  ADD KEY `id_jeu` (`id_jeu`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`),
  ADD KEY `id_user` (`id_user`);

--
-- Indexes for table `trajets`
--
ALTER TABLE `trajets`
  ADD PRIMARY KEY (`id_trajet`),
  ADD KEY `id_livraison` (`id_livraison`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categorie`
--
ALTER TABLE `categorie`
  MODIFY `id_categorie` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `historique`
--
ALTER TABLE `historique`
  MODIFY `id_historique` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `jeu`
--
ALTER TABLE `jeu`
  MODIFY `id_jeu` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `jeux_owned`
--
ALTER TABLE `jeux_owned`
  MODIFY `owned_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `livraisons`
--
ALTER TABLE `livraisons`
  MODIFY `id_livraison` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `trajets`
--
ALTER TABLE `trajets`
  MODIFY `id_trajet` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `historique`
--
ALTER TABLE `historique`
  ADD CONSTRAINT `historique_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `jeu`
--
ALTER TABLE `jeu`
  ADD CONSTRAINT `jeu_ibfk_1` FOREIGN KEY (`id_categorie`) REFERENCES `categorie` (`id_categorie`) ON DELETE SET NULL;

--
-- Constraints for table `jeux_owned`
--
ALTER TABLE `jeux_owned`
  ADD CONSTRAINT `jeux_owned_ibfk_1` FOREIGN KEY (`id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `jeux_owned_ibfk_2` FOREIGN KEY (`id_jeu`) REFERENCES `jeu` (`id_jeu`) ON DELETE CASCADE;

--
-- Constraints for table `livraisons`
--
ALTER TABLE `livraisons`
  ADD CONSTRAINT `livraisons_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `livraisons_ibfk_2` FOREIGN KEY (`id_jeu`) REFERENCES `jeu` (`id_jeu`) ON DELETE CASCADE;

--
-- Constraints for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD CONSTRAINT `password_resets_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `trajets`
--
ALTER TABLE `trajets`
  ADD CONSTRAINT `trajets_ibfk_1` FOREIGN KEY (`id_livraison`) REFERENCES `livraisons` (`id_livraison`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
