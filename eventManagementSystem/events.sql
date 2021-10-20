
-- -----------------------------------------------------
-- Table `venue`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `venue` ;

CREATE TABLE IF NOT EXISTS `venue` (
  `idvenue` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(50) NOT NULL,
  `capacity` INT NULL,
  PRIMARY KEY (`idvenue`),
  UNIQUE INDEX `name_UNIQUE` (`name` ASC))
ENGINE = MyISAM;


-- -----------------------------------------------------
-- Table `event`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `event` ;

CREATE TABLE IF NOT EXISTS `event` (
  `idevent` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(50) NOT NULL,
  `datestart` DATETIME NOT NULL,
  `dateend` DATETIME NOT NULL,
  `numberallowed` INT NOT NULL,
  `venue` INT NOT NULL,
  PRIMARY KEY (`idevent`),
  UNIQUE INDEX `name_UNIQUE` (`name` ASC),
  INDEX `venue_fk_idx` (`venue` ASC))
ENGINE = MyISAM;


-- -----------------------------------------------------
-- Table `session`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `session` ;

CREATE TABLE IF NOT EXISTS `session` (
  `idsession` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(50) NOT NULL,
  `numberallowed` INT NOT NULL,
  `event` INT NOT NULL,
  `startdate` DATETIME NOT NULL,
  `enddate` DATETIME NOT NULL,
  PRIMARY KEY (`idsession`))
ENGINE = MyISAM;


-- -----------------------------------------------------
-- Table `attendee`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `attendee` ;

CREATE TABLE IF NOT EXISTS `attendee` (
  `idattendee` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `password` VARCHAR(100) NOT NULL,
  `role` INT NULL,
  PRIMARY KEY (`idattendee`),
  INDEX `role_idx` (`role` ASC))
ENGINE = MyISAM;


-- -----------------------------------------------------
-- Table `manager_event`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `manager_event` ;

CREATE TABLE IF NOT EXISTS `manager_event` (
  `event` INT NOT NULL,
  `manager` INT NOT NULL,
	PRIMARY KEY (`event`, `manager`))
ENGINE = MyISAM;

-- -----------------------------------------------------
-- Table `attendee_event`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `attendee_event` ;

CREATE TABLE IF NOT EXISTS `attendee_event` (
  `event` INT NOT NULL,
  `attendee` INT NOT NULL,
  `paid` TINYINT NOT NULL DEFAULT 0,
  PRIMARY KEY (`event`, `attendee`))
ENGINE = MyISAM;


-- -----------------------------------------------------
-- Table `attendee_session`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `attendee_session` ;

CREATE TABLE IF NOT EXISTS `attendee_session` (
  `session` INT NOT NULL,
  `attendee` INT NOT NULL,
  PRIMARY KEY (`session`, `attendee`))
ENGINE = MyISAM;

-- -----------------------------------------------------
-- Table `role`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `roles` ;
DROP TABLE IF EXISTS `role` ;

CREATE TABLE IF NOT EXISTS `role` (
  `idrole` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(45) NOT NULL,
  PRIMARY KEY (`idrole`),
  UNIQUE INDEX `name_UNIQUE` (`name` ASC))
ENGINE = MyISAM;

INSERT INTO `role` (`name`) values ('admin'),('event manager'),('attendee');

/*Adding attendees*/
INSERT INTO attendee (name, password, role) VALUES ('Charles', '$2y$10$e5Xj1QUzM.cbb0rEYmWmKuxkHoKm2oBhmvxPWS4Vzt3gdFk0LQBUe', 3); /* Password: charlesbarkley */
INSERT INTO attendee (name, password, role) VALUES ('Abe', '$2y$10$FI44bn87yB8bJwgtGUS3WO3/aw3EQyPK60/7qWDSgn.HAcoEg9UZq', 3); /* Password: lincoln16th */
INSERT INTO attendee (name, password, role) VALUES ('Kevin', '$2y$10$3QOFHv424iYgzMJ88znTzuUZueagweokhg5A6LmEZLUtKVuIq5u2O', 2); /* Password: fishISgood */

/* Undeletable admin --> cannot be removed from frontend */
INSERT INTO attendee (name, password, role) VALUES ('Alexis Gordon', '$2y$10$JmPsN07mRCkZ4qjG.dEczePg5QSjyke2HdfA4I4uzLwLzjjI01CKG', 1); /* Password: aag7593 */

/*Adding venues*/
INSERT INTO venue (name, capacity) VALUES ('Mount Vernon', 350);
INSERT INTO venue (name, capacity) VALUES ('The San Diego Convention Center', 1000);
INSERT INTO venue (name, capacity) VALUES ('The Met', 240);
INSERT INTO venue (name, capacity) VALUES ('Elmwood Park', 170);

/*Adding events*/
INSERT INTO event (name, datestart, dateend, numberallowed, venue) VALUES ('Presidents Birthday', '2022-02-16 17:00:00', '2022-02-18 22:00:00', 120, 1);
INSERT INTO event (name, datestart, dateend, numberallowed, venue) VALUES ('Plant Expo', '2022-09-20 08:00:00', '2022-11-01 16:00:00', 400, 2);
INSERT INTO event (name, datestart, dateend, numberallowed, venue) VALUES ('Star Wars Expo', '2022-12-10 12:00:00', '2023-01-01 22:00:00', 500, 2);
INSERT INTO event (name, datestart, dateend, numberallowed, venue) VALUES ('Charity Gala', '2022-12-28 22:00:00', '2023-01-04 02:00:00', 240, 3);
INSERT INTO event (name, datestart, dateend, numberallowed, venue) VALUES ('Zoo Exhibit', '2022-04-16 16:00:00', '2022-04-30 16:00:00', 170, 4);
INSERT INTO event (name, datestart, dateend, numberallowed, venue) VALUES ('Stranger Things Comic Con', '2021-10-01 08:00:00', '2021-11-01 22:00:00', 300, 2);

/*Adding sessions for each event*/
INSERT INTO session (name, numberallowed, event, startdate, enddate) VALUES ('Lincolns Birthday', 80, 1, '2022-02-16 17:00:00', '2022-02-16 22:00:00');
INSERT INTO session (name, numberallowed, event, startdate, enddate) VALUES ('Washingtons Birthday', 100, 1, '2022-02-17 17:00:00', '2022-02-17 22:00:00');
INSERT INTO session (name, numberallowed, event, startdate, enddate) VALUES ('Adams Birthday', 90, 1, '2022-02-18 17:00:00', '2022-02-18 22:00:00');

INSERT INTO session (name, numberallowed, event, startdate, enddate) VALUES ('Succulents and Squash', 200, 2, '2022-09-30 10:00:00', '2022-09-30 14:00:00');
INSERT INTO session (name, numberallowed, event, startdate, enddate) VALUES ('Carrots, Carrots, and Above All Carrots', 130, 2, '2022-10-15 11:00:00', '2022-10-15 15:00:00');

INSERT INTO session (name, numberallowed, event, startdate, enddate) VALUES ('Turning to the Dark Side 101', 300, 3, '2022-12-31 15:00:00', '2022-12-31 20:00:00');
INSERT INTO session (name, numberallowed, event, startdate, enddate) VALUES ('The English Analysis of Anakin Skywalker', 150, 3, '2022-12-24 15:00:00', '2022-12-24 20:00:00');

INSERT INTO session (name, numberallowed, event, startdate, enddate) VALUES ('The Met Ball', 80, 4, '2022-12-31 20:00:00', '2023-01-01 06:00:00');

INSERT INTO session (name, numberallowed, event, startdate, enddate) VALUES ('Swimming with the Penguins', 150, 5, '2022-04-16 12:00:00', '2022-04-20 20:00:00');

INSERT INTO session (name, numberallowed, event, startdate, enddate) VALUES ('Meet the Cast of Season 3', 210, 6, '2021-10-30 08:00:00', '2021-10-30 13:00:00');

/*Adding attendee to event and sessions*/
INSERT INTO attendee_event (event, attendee, paid) VALUES (2, 1, 1);
INSERT INTO attendee_event (event, attendee, paid) VALUES (3, 1, 1);
INSERT INTO attendee_event (event, attendee, paid) VALUES (1, 2, 1);
INSERT INTO attendee_event (event, attendee, paid) VALUES (2, 3, 1);
INSERT INTO attendee_event (event, attendee, paid) VALUES (3, 3, 1);
INSERT INTO attendee_event (event, attendee, paid) VALUES (2, 4, 1);
INSERT INTO attendee_event (event, attendee, paid) VALUES (3, 4, 1);
INSERT INTO attendee_event (event, attendee, paid) VALUES (4, 4, 1);
INSERT INTO attendee_event (event, attendee, paid) VALUES (6, 4, 1);

INSERT INTO attendee_session (session, attendee) VALUES (4, 1);
INSERT INTO attendee_session (session, attendee) VALUES (5, 1);
INSERT INTO attendee_session (session, attendee) VALUES (7, 1);
INSERT INTO attendee_session (session, attendee) VALUES (2, 2);
INSERT INTO attendee_session (session, attendee) VALUES (3, 2);
INSERT INTO attendee_session (session, attendee) VALUES (4, 3);
INSERT INTO attendee_session (session, attendee) VALUES (6, 3);
INSERT INTO attendee_session (session, attendee) VALUES (4, 4);
INSERT INTO attendee_session (session, attendee) VALUES (7, 4);
INSERT INTO attendee_session (session, attendee) VALUES (8, 4);
INSERT INTO attendee_session (session, attendee) VALUES (10, 4);

/*Adding event manager to events*/
INSERT INTO manager_event (event, manager) VALUES (2, 3);
INSERT INTO manager_event (event, manager) VALUES (4, 3);
INSERT INTO manager_event (event, manager) VALUES (6, 3);