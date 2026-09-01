#!/usr/bin/env python3
"""
Divise c1apis03-3.sql en 3 fichiers et ajoute les tables des 3 dernières migrations.
"""

import os

INPUT_FILE = 'c1apis03-3.sql'
OUTPUT_FILES = [
    'c1apis03_part1.sql',
    'c1apis03_part2.sql',
    'c1apis03_part3.sql',
]

SQL_HEADER = """-- phpMyAdmin SQL Dump - Partie {part}/3
-- Genere automatiquement depuis c1apis03-3.sql
-- Base de donnees: `c1apis03`

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

"""

SQL_FOOTER = """
COMMIT;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
"""

NEW_TABLES_SQL = """
-- --------------------------------------------------------
--
-- Table `ai_report_logs` (migration 2026_08_27_200315)
--

CREATE TABLE IF NOT EXISTS `ai_report_logs` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `personnel_id` bigint(20) UNSIGNED DEFAULT NULL,
  `type_rapport` enum('individual','global','bulletin','question') NOT NULL,
  `sujet_id` bigint(20) UNSIGNED DEFAULT NULL,
  `sujet_type` varchar(255) DEFAULT NULL,
  `question` text DEFAULT NULL,
  `sql_genere` text DEFAULT NULL,
  `sql_valide` tinyint(1) DEFAULT NULL,
  `erreurs_validation` longtext DEFAULT NULL,
  `reponse_ia` longtext DEFAULT NULL,
  `pdf_path` varchar(255) DEFAULT NULL,
  `duree_ms` int(10) UNSIGNED DEFAULT NULL,
  `modele_ia` varchar(255) DEFAULT 'claude-sonnet-4-5',
  `tokens_utilises` int(10) UNSIGNED DEFAULT NULL,
  `statut` enum('success','error','sql_rejected') NOT NULL DEFAULT 'success',
  `erreur_message` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ai_report_logs_personnel_id_index` (`personnel_id`),
  KEY `ai_report_logs_type_rapport_created_at_index` (`type_rapport`,`created_at`),
  KEY `ai_report_logs_sujet_id_sujet_type_index` (`sujet_id`,`sujet_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
--
-- Table `periodic_bulletins` (migration 2026_08_27_200315)
--

CREATE TABLE IF NOT EXISTS `periodic_bulletins` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `periode_type` enum('mensuel','trimestriel') NOT NULL,
  `periode_debut` date NOT NULL,
  `periode_fin` date NOT NULL,
  `organisme_id` bigint(20) UNSIGNED DEFAULT NULL,
  `kpis` longtext NOT NULL,
  `commentaire_ia` longtext DEFAULT NULL,
  `categorisation_payeurs` longtext DEFAULT NULL,
  `pdf_path` varchar(255) DEFAULT NULL,
  `genere_par` bigint(20) UNSIGNED DEFAULT NULL,
  `genere_auto` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `periodic_bulletins_organisme_id_index` (`organisme_id`),
  KEY `periodic_bulletins_genere_par_index` (`genere_par`),
  KEY `periodic_bulletins_periode_index` (`periode_type`,`periode_debut`,`periode_fin`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

"""

def main():
    print("Lecture du fichier source...")
    with open(INPUT_FILE, 'r', errors='replace') as f:
        lines = f.readlines()

    total_lines = len(lines)
    print(f"  -> {total_lines} lignes lues")

    separators = []
    for i, line in enumerate(lines):
        if line.startswith('-- --------------------------------------------------------'):
            separators.append(i)

    total_sep = len(separators)
    print(f"  -> {total_sep} blocs de tables trouves")

    cut1_sep = total_sep // 3
    cut2_sep = (2 * total_sep) // 3

    cut1_line = separators[cut1_sep]
    cut2_line = separators[cut2_sep]

    print(f"  -> Coupure 1 : ligne {cut1_line} (bloc {cut1_sep}/{total_sep})")
    print(f"  -> Coupure 2 : ligne {cut2_line} (bloc {cut2_sep}/{total_sep})")

    end_data_line = total_lines
    for i in range(total_lines - 1, max(total_lines - 50, 0), -1):
        if lines[i].strip() == 'COMMIT;':
            end_data_line = i
            break

    segments = [
        (0, cut1_line),
        (cut1_line, cut2_line),
        (cut2_line, end_data_line),
    ]

    for idx, (start, end) in enumerate(segments):
        part_num = idx + 1
        out_file = OUTPUT_FILES[idx]
        print(f"\nEcriture de {out_file} (lignes {start+1}--{end})...")

        with open(out_file, 'w', encoding='utf-8') as f:
            f.write(SQL_HEADER.format(part=part_num))
            f.writelines(lines[start:end])

            if idx == 2:
                print("  -> Ajout des tables ai_report_logs et periodic_bulletins...")
                f.write(NEW_TABLES_SQL)

            f.write(SQL_FOOTER)

        size_mb = os.path.getsize(out_file) / (1024 * 1024)
        print(f"  OK: {out_file} cree ({size_mb:.1f} MB)")

    print("\nTermine ! Les 3 fichiers sont prets :")
    for fname in OUTPUT_FILES:
        size_mb = os.path.getsize(fname) / (1024 * 1024)
        print(f"  * {fname} : {size_mb:.1f} MB")

    print("""
Ordre d'importation recommande :
  1. mysql -u USERNAME -p DATABASE < c1apis03_part1.sql
  2. mysql -u USERNAME -p DATABASE < c1apis03_part2.sql
  3. mysql -u USERNAME -p DATABASE < c1apis03_part3.sql
""")

if __name__ == '__main__':
    main()
