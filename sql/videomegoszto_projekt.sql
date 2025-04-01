-- tablak torlese, ha letezik
DROP TABLE Megtekintes CASCADE CONSTRAINTS PURGE;
DROP TABLE Hozzaszolas CASCADE CONSTRAINTS PURGE;
DROP TABLE Kedvencek CASCADE CONSTRAINTS PURGE;
DROP TABLE LejatszasiListaVideo CASCADE CONSTRAINTS PURGE;
DROP TABLE LejatszasiLista CASCADE CONSTRAINTS PURGE;
DROP TABLE Video CASCADE CONSTRAINTS PURGE;
DROP TABLE Felhasznalo CASCADE CONSTRAINTS PURGE;

-- tablak letrehozasa
CREATE TABLE Felhasznalo (
    felhasznalo_id INT PRIMARY KEY,
    felhasznalonev VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    jelszo VARCHAR(255) NOT NULL,
    regisztracio_idopont TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    utolso_bejelentkezes TIMESTAMP DEFAULT NULL,
    profilkep_url VARCHAR(255),
    bio CLOB
);

CREATE TABLE Video (
    video_id INT PRIMARY KEY,
    cim VARCHAR(100) NOT NULL,
    leiras CLOB,
    feltoltes_datum TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
    hossz INT NOT NULL,
    is_short NUMBER(1) DEFAULT 0,
    miniatur_url VARCHAR(255),
    video_url VARCHAR(255) NOT NULL,
    kategoria_id INT NOT NULL,
    felhasznalo_id INT NOT NULL,
    FOREIGN KEY (kategoria_id) REFERENCES Kategoria(kategoria_id),
    FOREIGN KEY (felhasznalo_id) REFERENCES Felhasznalo(felhasznalo_id) ON DELETE CASCADE
);

CREATE TABLE LejatszasiLista (
    lista_id INT PRIMARY KEY,
    nev VARCHAR(100) NOT NULL,
    publikus NUMBER(1) DEFAULT 0,
    letrehozas_datum TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
    felhasznalo_id INT NOT NULL,
    FOREIGN KEY (felhasznalo_id) REFERENCES Felhasznalo(felhasznalo_id) ON DELETE CASCADE
);

CREATE TABLE LejatszasiListaVideo (
    lista_id INT NOT NULL,
    video_id INT NOT NULL,
    pozicio INT NOT NULL,
    hozzaadas_datum TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
    PRIMARY KEY (lista_id, video_id),
    FOREIGN KEY (lista_id) REFERENCES LejatszasiLista(lista_id) ON DELETE CASCADE,
    FOREIGN KEY (video_id) REFERENCES Video(video_id) ON DELETE CASCADE
);

CREATE TABLE Kedvencek (
    felhasznalo_id INT NOT NULL,
    video_id INT NOT NULL,
    hozzaadas_datum TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
    PRIMARY KEY (felhasznalo_id, video_id),
    FOREIGN KEY (felhasznalo_id) REFERENCES Felhasznalo(felhasznalo_id) ON DELETE CASCADE,
    FOREIGN KEY (video_id) REFERENCES Video(video_id) ON DELETE CASCADE
);

CREATE TABLE Hozzaszolas (
    hozzaszolas_id INT PRIMARY KEY,
    hozzaszolas_szoveg CLOB NOT NULL,
    letrehozas_datum TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
    felhasznalo_id INT NOT NULL,
    video_id INT NOT NULL,
    FOREIGN KEY (felhasznalo_id) REFERENCES Felhasznalo(felhasznalo_id) ON DELETE CASCADE,
    FOREIGN KEY (video_id) REFERENCES Video(video_id) ON DELETE CASCADE
);

CREATE TABLE Megtekintes (
    megtekintes_id INT PRIMARY KEY,
    megtekintes_datum TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
    megtekintes_hossz INT NOT NULL,
    video_id INT NOT NULL,
    felhasznalo_id INT NOT NULL,
    FOREIGN KEY (video_id) REFERENCES Video(video_id) ON DELETE CASCADE,
    FOREIGN KEY (felhasznalo_id) REFERENCES Felhasznalo(felhasznalo_id) ON DELETE CASCADE
);

CREATE TABLE VideoMetadata (
    video_url VARCHAR(255),
    miniatur_url VARCHAR(255),
    beagyazasi_kod CLOB,
    FOREIGN KEY (video_url) REFERENCES Video(video_url) ON DELETE CASCADE
);

CREATE TABLE VideoCimke (
    cimke_id INT,
    video_id INT,
    PRIMARY KEY (cimke_id, video_id),
    FOREIGN KEY (cimke_id) REFERENCES Cimke(cimke_id) ON DELETE CASCADE,
    FOREIGN KEY (video_id) REFERENCES Video(video_id) ON DELETE CASCADE
);

CREATE TABLE Kategoria (
    kategoria_id INT PRIMARY KEY,
    nev VARCHAR(100) NOT NULL
);

CREATE TABLE Cimke (
    cimke_id INT PRIMARY KEY,
    nev VARCHAR(100) NOT NULL
);