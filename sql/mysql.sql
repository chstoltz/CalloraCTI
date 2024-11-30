SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;


CREATE TABLE `adm_benutzer` (
  `id` int(11) NOT NULL,
  `username` varchar(30) NOT NULL,
  `password` varchar(128) NOT NULL,
  `salt` varchar(128) NOT NULL,
  `nst` int(3) NOT NULL,
  `email` varchar(50) NOT NULL,
  `regdate` timestamp NOT NULL DEFAULT current_timestamp(),
  `level` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `adm_benutzer` (`id`, `username`, `password`, `salt`, `nst`, `email`, `regdate`, `level`) VALUES
(1, 'admin', '03920b53dcc0795010e1d5a484c5a8fcd9310c8c1a84a363ed6e196d814cda07ffc3e4d30e426bd44f5a0a84091bd07417aefa0f924c2dcd50fb3aabf8165f6c', 'b542e8824ce2203b4704d15499b77e6e29e5e55556970ec7232a665b30c74a240d05981cffae8cf9f6ea7417c16c2790f417c3e5ed6a101c06c8f447e6e52477', 0, 'deine@mail.de', '2024-11-10 11:52:32', 9);

CREATE TABLE `adm_einstellungen` (
  `id` tinyint(1) NOT NULL DEFAULT 0,
  `ws_fqdn` varchar(255) NOT NULL,
  `ws_ip` varchar(15) NOT NULL,
  `ws_path` varchar(255) NOT NULL,
  `cell_prefix` varchar(255) NOT NULL,
  `polling` tinyint(1) NOT NULL DEFAULT 0,
  `xml_application_post_list` varchar(255) NOT NULL DEFAULT '0.0.0.0',
  `ntp_server1` varchar(255) DEFAULT 'ptbtime1.ptb.de',
  `ntp_server2` varchar(255) DEFAULT 'ptbtime2.ptb.de',
  `ntp_server3` varchar(255) DEFAULT 'pool.ntp.org',
  `admin_password_phone` varchar(255) NOT NULL DEFAULT '22222',
  `web_interface_enabled` tinyint(1) NOT NULL DEFAULT 1,
  `ip_whitelist` varchar(255) DEFAULT NULL,
  `sip_whitelist` tinyint(1) NOT NULL DEFAULT 0,
  `options_password_enabled` tinyint(1) NOT NULL DEFAULT 0,
  `codecs` varchar(255) DEFAULT NULL,
  `protocol` varchar(5) NOT NULL DEFAULT 'http',
  `dectsystem` varchar(20) NOT NULL DEFAULT 'mitel',
  `db_version` smallint(6) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `adm_fritzbox` (
  `fb_url` varchar(255) NOT NULL,
  `fb_ip` varchar(15) NOT NULL,
  `fb_user` varchar(255) NOT NULL,
  `fb_pass` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `adm_sipdect` (
  `omm_ip` varchar(15) NOT NULL,
  `registrar_ip` varchar(255) NOT NULL,
  `proxy_ip` varchar(255) NOT NULL,
  `omm_password` varchar(255) NOT NULL,
  `root_password` varchar(255) NOT NULL,
  `system_name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `callmonitor` (
  `datum` varchar(30) NOT NULL,
  `aktion` varchar(20) NOT NULL,
  `id` tinyint(4) NOT NULL,
  `nst` smallint(6) NOT NULL,
  `lokal` varchar(20) NOT NULL,
  `remote` varchar(20) NOT NULL,
  `konto` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `callstate` (
  `nst` int(11) NOT NULL,
  `state` varchar(12) NOT NULL,
  `remotenumber` varchar(16) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `model` (
  `model` varchar(10) NOT NULL,
  `hersteller` varchar(20) DEFAULT NULL,
  `softkey` int(11) NOT NULL DEFAULT 0,
  `topsoftkey` int(11) NOT NULL DEFAULT 0,
  `sidekeys` tinyint(1) NOT NULL DEFAULT 0,
  `expkey` tinyint(4) NOT NULL DEFAULT 0,
  `sip` tinyint(1) NOT NULL DEFAULT 0,
  `dect` tinyint(1) NOT NULL DEFAULT 0,
  `exp` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `model` (`model`, `hersteller`, `softkey`, `topsoftkey`, `sidekeys`, `expkey`, `sip`, `dect`, `exp`) VALUES
('612', 'mitel', 0, 0, 0, 0, 0, 1, 0),
('622', 'mitel', 0, 0, 3, 0, 0, 1, 0),
('632', 'mitel', 0, 0, 3, 0, 0, 1, 0),
('680', 'mitel', 0, 0, 0, 16, 0, 0, 1),
('685', 'mitel', 0, 0, 0, 84, 0, 0, 1),
('6865', 'mitel', 0, 8, 0, 0, 1, 0, 0),
('6867', 'mitel', 4, 6, 0, 0, 1, 0, 0),
('6869', 'mitel', 5, 12, 0, 0, 1, 0, 0),
('6873', 'mitel', 6, 12, 0, 0, 1, 0, 0),
('6915', 'mitel', 4, 6, 0, 0, 1, 0, 0),
('6920', 'mitel', 4, 8, 0, 0, 1, 0, 0),
('6930', 'mitel', 5, 12, 0, 0, 1, 0, 0),
('6940', 'mitel', 6, 12, 0, 0, 1, 0, 0),
('700', 'mitel', 0, 0, 3, 0, 0, 1, 0),
('D713', 'snom', 4, 4, 0, 0, 1, 0, 0),
('D715', 'snom', 4, 0, 0, 0, 1, 0, 0),
('D717', 'snom', 4, 3, 0, 0, 1, 0, 0),
('D735', 'snom', 4, 8, 0, 0, 1, 0, 0),
('D785', 'snom', 4, 6, 0, 0, 1, 0, 0),
('D812', 'snom', 4, 8, 0, 0, 1, 0, 0),
('D815', 'snom', 4, 10, 0, 0, 1, 0, 0),
('D862', 'snom', 4, 8, 0, 0, 1, 0, 0),
('D865', 'snom', 4, 10, 0, 0, 1, 0, 0),
('T42U', 'yealink', 4, 6, 0, 0, 1, 0, 0),
('T43U', 'yealink', 4, 8, 0, 0, 1, 0, 0),
('T44W', 'yealink', 4, 8, 0, 0, 1, 0, 0),
('T46U', 'yealink', 4, 10, 0, 0, 1, 0, 0),
('T48U', 'yealink', 4, 12, 0, 0, 1, 0, 0),
('T53W', 'yealink', 4, 8, 0, 0, 1, 0, 0),
('T54W', 'yealink', 4, 10, 0, 0, 1, 0, 0),
('T57W', 'yealink', 4, 12, 0, 0, 1, 0, 0);

CREATE TABLE `poll` (
  `nst` int(11) NOT NULL,
  `duration` varchar(15) NOT NULL,
  `linestate` varchar(15) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `regevent` (
  `nst` int(11) NOT NULL,
  `regstate` varchar(20) NOT NULL,
  `regcode` int(11) NOT NULL,
  `ip` varchar(15) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `tasten` (
  `id` int(11) NOT NULL,
  `nst` int(3) NOT NULL,
  `taste` varchar(13) NOT NULL,
  `ziel` varchar(255) NOT NULL,
  `type` varchar(20) NOT NULL,
  `label` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `usr_einstellungen` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `fb_ab` tinyint(2) NOT NULL DEFAULT 99,
  `fb_book` tinyint(2) NOT NULL DEFAULT 99,
  `fb_deflection` varchar(255) DEFAULT NULL,
  `fb_ports` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `usr_mobilteil` (
  `nst` smallint(3) NOT NULL,
  `model` smallint(3) NOT NULL,
  `ipei` bigint(13) NOT NULL DEFAULT 0,
  `auth_code` int(6) NOT NULL,
  `pin` smallint(4) NOT NULL DEFAULT 1234,
  `pin_lock` smallint(4) NOT NULL DEFAULT 1234,
  `key_lock_enable` tinyint(1) NOT NULL DEFAULT 0,
  `key_lock_time` smallint(4) NOT NULL DEFAULT 0,
  `id` tinyint(2) NOT NULL,
  `name` varchar(20) NOT NULL DEFAULT '',
  `sip_user` varchar(255) NOT NULL,
  `sip_password` varchar(255) NOT NULL,
  `sidekey1` varchar(20) NOT NULL DEFAULT 'x',
  `sidekey2` varchar(20) NOT NULL DEFAULT 'x',
  `sidekey3` varchar(20) NOT NULL DEFAULT 'x',
  `LedInfo` tinyint(1) NOT NULL DEFAULT 1,
  `DispLang` varchar(2) NOT NULL DEFAULT 'de',
  `DispColor` varchar(15) NOT NULL DEFAULT 'business',
  `RingerMelodyIntern` varchar(63) NOT NULL DEFAULT 'weekend',
  `RingerMelodyExtern` varchar(63) NOT NULL DEFAULT 'butterfly'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `usr_telefon` (
  `screenname` varchar(50) NOT NULL DEFAULT '0',
  `screenname2` varchar(50) NOT NULL DEFAULT '0',
  `username` varchar(50) NOT NULL DEFAULT '0',
  `displayname` int(3) NOT NULL,
  `authname` varchar(50) NOT NULL DEFAULT '0',
  `password` varchar(50) NOT NULL DEFAULT '0',
  `registrationperiod` int(4) NOT NULL DEFAULT 3600,
  `proxy` varchar(255) DEFAULT '0',
  `registrar` varchar(255) NOT NULL DEFAULT '0',
  `nst` int(11) NOT NULL,
  `call_forward_disabled` tinyint(1) NOT NULL DEFAULT 1,
  `idle_screen_font_color` varchar(10) NOT NULL DEFAULT 'black',
  `dst_config` tinyint(4) NOT NULL DEFAULT 3,
  `call_waiting_tone` tinyint(4) NOT NULL DEFAULT 0,
  `mwi_led_line` tinyint(4) NOT NULL DEFAULT 1,
  `missed_calls_indicator_disabled` tinyint(1) NOT NULL DEFAULT 0,
  `brightness_level` tinyint(4) NOT NULL DEFAULT 5,
  `bl_on_time` tinyint(4) NOT NULL DEFAULT 60,
  `inactivity_brightness_level` tinyint(1) NOT NULL DEFAULT 3,
  `screen_save_time` smallint(6) NOT NULL DEFAULT 1800,
  `switch_focus_to_ringing_line` tinyint(1) NOT NULL DEFAULT 0,
  `handset_volume` tinyint(4) NOT NULL DEFAULT 5,
  `speaker_volume` tinyint(4) NOT NULL DEFAULT 5,
  `headset_volume` tinyint(4) NOT NULL DEFAULT 5,
  `ringer_volume` tinyint(4) NOT NULL DEFAULT 5,
  `audio_mode` tinyint(4) NOT NULL DEFAULT 1,
  `line1_ring_tone` varchar(30) NOT NULL DEFAULT '1',
  `sip_explicit_mwi_subscription` tinyint(1) NOT NULL DEFAULT 1,
  `custom_ringtone_1` varchar(255) DEFAULT NULL,
  `mac` varchar(12) NOT NULL DEFAULT '0',
  `model` varchar(20) NOT NULL,
  `exp` smallint(3) NOT NULL DEFAULT 0,
  `firmware` varchar(255) DEFAULT 'N/A'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `usr_tuerklingel` (
  `id` int(11) NOT NULL,
  `wc_url_s` varchar(255) DEFAULT NULL,
  `wc_url_m` varchar(255) DEFAULT NULL,
  `wc_url_l` varchar(255) DEFAULT NULL,
  `wc_url_browser` varchar(255) DEFAULT NULL,
  `wc_browser_type` varchar(5) DEFAULT NULL,
  `wc_nst` varchar(11) DEFAULT NULL,
  `door_url` varchar(255) DEFAULT NULL,
  `door_key` varchar(5) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


ALTER TABLE `adm_benutzer`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `adm_einstellungen`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `callmonitor`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `callstate`
  ADD PRIMARY KEY (`nst`);

ALTER TABLE `model`
  ADD PRIMARY KEY (`model`);

ALTER TABLE `poll`
  ADD PRIMARY KEY (`nst`);

ALTER TABLE `regevent`
  ADD PRIMARY KEY (`nst`);

ALTER TABLE `tasten`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `usr_einstellungen`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `usr_mobilteil`
  ADD PRIMARY KEY (`nst`);

ALTER TABLE `usr_telefon`
  ADD PRIMARY KEY (`nst`);

ALTER TABLE `usr_tuerklingel`
  ADD PRIMARY KEY (`id`);


ALTER TABLE `adm_benutzer`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

ALTER TABLE `tasten`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `usr_einstellungen`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
