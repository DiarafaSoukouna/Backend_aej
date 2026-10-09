--##################################################################################
--##################################################################################
-- Voir les regles de "ON DELETE"
SELECT rc.CONSTRAINT_SCHEMA, rc.TABLE_NAME, rc.CONSTRAINT_NAME, rc.DELETE_RULE
FROM information_schema.REFERENTIAL_CONSTRAINTS AS rc
WHERE rc.CONSTRAINT_SCHEMA = 'c1apis03'
  AND rc.DELETE_RULE = 'CASCADE';

SELECT rc.CONSTRAINT_SCHEMA, rc.TABLE_NAME, rc.CONSTRAINT_NAME, rc.DELETE_RULE
FROM information_schema.REFERENTIAL_CONSTRAINTS AS rc
WHERE rc.CONSTRAINT_SCHEMA = 'c1apis03'
  AND rc.DELETE_RULE = 'SET NULL';

SELECT rc.CONSTRAINT_SCHEMA, rc.TABLE_NAME, rc.CONSTRAINT_NAME, rc.DELETE_RULE
FROM information_schema.REFERENTIAL_CONSTRAINTS AS rc
WHERE rc.CONSTRAINT_SCHEMA = 'c1apis03'
  AND rc.DELETE_RULE = 'RESTRICT';

--##################################################################################
--##################################################################################
SELECT
    rc.TABLE_NAME,
    rc.CONSTRAINT_NAME,
    kcu.COLUMN_NAME,
    kcu.REFERENCED_TABLE_NAME,
    kcu.REFERENCED_COLUMN_NAME,
    rc.DELETE_RULE,
    rc.UPDATE_RULE
FROM information_schema.REFERENTIAL_CONSTRAINTS rc
JOIN information_schema.KEY_COLUMN_USAGE kcu
    ON rc.CONSTRAINT_SCHEMA = kcu.CONSTRAINT_SCHEMA
    AND rc.CONSTRAINT_NAME = kcu.CONSTRAINT_NAME
    AND rc.TABLE_NAME = kcu.TABLE_NAME
WHERE rc.CONSTRAINT_SCHEMA = 'c1apis03'
ORDER BY
    rc.DELETE_RULE,
    rc.TABLE_NAME,
    rc.CONSTRAINT_NAME;


--##################################################################################
--##################################################################################
-- Voir les changes de cles non null
SELECT 
  kcu.TABLE_NAME,
  kcu.COLUMN_NAME,
  c.IS_NULLABLE,
  c.COLUMN_TYPE,
  kcu.REFERENCED_TABLE_NAME,
  kcu.REFERENCED_COLUMN_NAME
FROM information_schema.KEY_COLUMN_USAGE AS kcu
JOIN information_schema.COLUMNS AS c
  ON  c.TABLE_SCHEMA = kcu.TABLE_SCHEMA
  AND c.TABLE_NAME   = kcu.TABLE_NAME
  AND c.COLUMN_NAME  = kcu.COLUMN_NAME
JOIN information_schema.REFERENTIAL_CONSTRAINTS AS rc
  ON  rc.CONSTRAINT_SCHEMA = kcu.CONSTRAINT_SCHEMA
  AND rc.CONSTRAINT_NAME   = kcu.CONSTRAINT_NAME
  AND rc.TABLE_NAME        = kcu.TABLE_NAME
WHERE kcu.CONSTRAINT_SCHEMA = 'c1apis03'
  AND rc.DELETE_RULE = 'CASCADE'
  AND kcu.REFERENCED_TABLE_NAME IS NOT NULL 
ORDER BY c.IS_NULLABLE, kcu.TABLE_NAME;


--##################################################################################
--##################################################################################
-- Changer les regles "ON DELETE CASCADE" par "ON DELETE RESTRICT"
DELIMITER $$

DROP PROCEDURE IF EXISTS `convert_delete_roles`$$

CREATE PROCEDURE `convert_delete_roles`()
BEGIN
    DECLARE done INT DEFAULT FALSE;
    DECLARE v_table VARCHAR(255);
    DECLARE v_constraint VARCHAR(255);
    DECLARE v_ref_table VARCHAR(255);
    DECLARE v_update_rule VARCHAR(50);
    DECLARE v_columns TEXT;
    DECLARE v_ref_columns TEXT;

    /*
     * Récupère chaque contrainte FK ayant ON DELETE CASCADE.
     * GROUP_CONCAT permet de gérer également les FK composites.
     */
    DECLARE cur CURSOR FOR
        SELECT
            rc.TABLE_NAME,
            rc.CONSTRAINT_NAME,
            rc.REFERENCED_TABLE_NAME,
            rc.UPDATE_RULE,

            GROUP_CONCAT(
                CONCAT('`', kcu.COLUMN_NAME, '`')
                ORDER BY kcu.ORDINAL_POSITION
                SEPARATOR ', '
            ) AS columns_list,

            GROUP_CONCAT(
                CONCAT('`', kcu.REFERENCED_COLUMN_NAME, '`')
                ORDER BY kcu.ORDINAL_POSITION
                SEPARATOR ', '
            ) AS referenced_columns_list

        FROM information_schema.REFERENTIAL_CONSTRAINTS rc

        INNER JOIN information_schema.KEY_COLUMN_USAGE kcu
            ON rc.CONSTRAINT_SCHEMA = kcu.CONSTRAINT_SCHEMA
            AND rc.TABLE_NAME = kcu.TABLE_NAME
            AND rc.CONSTRAINT_NAME = kcu.CONSTRAINT_NAME

        WHERE rc.CONSTRAINT_SCHEMA = 'c1apis03'
          AND rc.DELETE_RULE = 'CASCADE'

        GROUP BY
            rc.TABLE_NAME,
            rc.CONSTRAINT_NAME,
            rc.REFERENCED_TABLE_NAME,
            rc.UPDATE_RULE;

    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;

    OPEN cur;

    read_loop: LOOP
        FETCH cur INTO
            v_table,
            v_constraint,
            v_ref_table,
            v_update_rule,
            v_columns,
            v_ref_columns;

        IF done THEN
            LEAVE read_loop;
        END IF;

        SET @sql = CONCAT( 'ALTER TABLE `c1apis03`.`', v_table, '` DROP FOREIGN KEY `', v_constraint, '`');

        PREPARE stmt FROM @sql;
        EXECUTE stmt;
        DEALLOCATE PREPARE stmt;

        /*
         * 2. Recréer la FK avec ON DELETE RESTRICT
         *
         * RESTRICT est le comportement explicite :
         * impossible de supprimer le parent si des enfants existent.
         */
        SET @sql = CONCAT(
            'ALTER TABLE `c1apis03`.`',
            v_table,
            '` ADD CONSTRAINT `',
            v_constraint,
            '` FOREIGN KEY (',
            v_columns,
            ') REFERENCES `c1apis03`.`',
            v_ref_table,
            '` (',
            v_ref_columns,
            ') ON DELETE RESTRICT ON UPDATE ',
            v_update_rule
        );

        PREPARE stmt FROM @sql;
        EXECUTE stmt;
        DEALLOCATE PREPARE stmt;

    END LOOP;

    CLOSE cur;

END$$

DELIMITER ;

CALL convert_delete_roles();
DROP PROCEDURE IF EXISTS `convert_delete_roles`;

--##################################################################################
--##################################################################################
ALTER TABLE nom_table
DROP FOREIGN KEY nom_contrainte;

ALTER TABLE nom_table
ADD CONSTRAINT nom_contrainte
FOREIGN KEY (colonne)
REFERENCES table_parent(id)
ON DELETE RESTRICT;


--##################################################################################
-- Verifier les regles "ON DELETE"
SELECT DELETE_RULE, COUNT(*) AS nb
FROM information_schema.REFERENTIAL_CONSTRAINTS
WHERE CONSTRAINT_SCHEMA = 'c1apis03'
GROUP BY DELETE_RULE;