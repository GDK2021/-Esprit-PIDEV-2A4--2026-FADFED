CREATE DATABASE IF NOT EXISTS web_app CHARACTER SET utf8mb4;
USE web_app;

CREATE TABLE IF NOT EXISTS user (
    id_user          INT AUTO_INCREMENT PRIMARY KEY,
    nom              VARCHAR(50)  NOT NULL,
    prenom           VARCHAR(50)  NOT NULL,
    age              INT          NOT NULL,
    genre            VARCHAR(10)  NOT NULL,
    bloodtype        VARCHAR(3)   NOT NULL,
    role             VARCHAR(20)  NOT NULL,
    email            VARCHAR(100) NOT NULL UNIQUE,
    pseudo           VARCHAR(50)  NOT NULL UNIQUE,
    login            VARCHAR(50)  NOT NULL UNIQUE,
    mdp              VARCHAR(255) NOT NULL,
    num_telephone    INT          NOT NULL,
    date_inscription DATE         NOT NULL,
    photo_profil     VARCHAR(255) NULL
);