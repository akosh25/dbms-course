-- tablak torlese, ha szukseges

DROP TABLE VideoCimke CASCADE CONSTRAINTS PURGE;
DROP TABLE VideoMetadata CASCADE CONSTRAINTS PURGE;
DROP TABLE Hozzaszolas CASCADE CONSTRAINTS PURGE;
DROP TABLE Kedvencek CASCADE CONSTRAINTS PURGE;
DROP TABLE LejatszasiListaVideo CASCADE CONSTRAINTS PURGE;
DROP TABLE LejatszasiLista CASCADE CONSTRAINTS PURGE;
DROP TABLE Megtekintes CASCADE CONSTRAINTS PURGE;
DROP TABLE Ajanlasok CASCADE CONSTRAINTS PURGE;
DROP TABLE Video CASCADE CONSTRAINTS PURGE;
DROP TABLE Cimke CASCADE CONSTRAINTS PURGE;
DROP TABLE Kategoria CASCADE CONSTRAINTS PURGE;
DROP TABLE Felhasznalo CASCADE CONSTRAINTS PURGE;

-- tablak letrehozasa

CREATE TABLE Felhasznalo (
    felhasznalo_id NUMBER PRIMARY KEY,
    felhasznalonev VARCHAR2(100) NOT NULL,
    email VARCHAR2(100) NOT NULL,
    jelszo VARCHAR2(100) NOT NULL,
    regisztracio_idobont TIMESTAMP,
    utolso_bejelentkezes TIMESTAMP,
    profilkep_url VARCHAR2(200),
    bio VARCHAR2(500)
);

CREATE TABLE Kategoria (
    kategoria_id NUMBER PRIMARY KEY,
    nev VARCHAR2(100) NOT NULL,
    leiras VARCHAR2(200)
);

CREATE TABLE Video (
    video_id NUMBER PRIMARY KEY,
    cim VARCHAR2(200) NOT NULL,
    leiras VARCHAR2(1000),
    feltoltes_datum TIMESTAMP,
    kategoria_id NUMBER,
    felhasznalo_id NUMBER,
    hossz NUMBER,
    is_short NUMBER(1),
    video_url VARCHAR2(300) UNIQUE,
    CONSTRAINT fk_video_kategoria FOREIGN KEY (kategoria_id) REFERENCES Kategoria(kategoria_id),
    CONSTRAINT fk_video_felhasznalo FOREIGN KEY (felhasznalo_id) REFERENCES Felhasznalo(felhasznalo_id)
);

CREATE TABLE Cimke (
    cimke_id NUMBER PRIMARY KEY,
    nev VARCHAR2(100) NOT NULL
);

CREATE TABLE VideoCimke (
    video_id NUMBER,
    cimke_id NUMBER,
    PRIMARY KEY (video_id, cimke_id),
    CONSTRAINT fk_videocimke_video FOREIGN KEY (video_id) REFERENCES Video(video_id),
    CONSTRAINT fk_videocimke_cimke FOREIGN KEY (cimke_id) REFERENCES Cimke(cimke_id)
);

CREATE TABLE VideoMetadata (
    video_url VARCHAR2(300) PRIMARY KEY,
    miniatur_url VARCHAR2(300),
    beagyazasi_kod VARCHAR2(1000),
    CONSTRAINT fk_videometadata_video FOREIGN KEY (video_url) REFERENCES Video(video_url)
);

CREATE TABLE Hozzaszolas (
    hozzaszolas_id NUMBER PRIMARY KEY,
    felhasznalo_id NUMBER,
    video_id NUMBER,
    hozzaszolas_szoveg VARCHAR2(1000),
    letrehozas_datum TIMESTAMP,
    CONSTRAINT fk_hozzaszolas_felhasznalo FOREIGN KEY (felhasznalo_id) REFERENCES Felhasznalo(felhasznalo_id),
    CONSTRAINT fk_hozzaszolas_video FOREIGN KEY (video_id) REFERENCES Video(video_id)
);

CREATE TABLE Kedvencek (
    felhasznalo_id NUMBER,
    video_id NUMBER,
    hozzaadas_datum TIMESTAMP,
    PRIMARY KEY (felhasznalo_id, video_id),
    CONSTRAINT fk_kedvencek_felhasznalo FOREIGN KEY (felhasznalo_id) REFERENCES Felhasznalo(felhasznalo_id),
    CONSTRAINT fk_kedvencek_video FOREIGN KEY (video_id) REFERENCES Video(video_id)
);

CREATE TABLE LejatszasiLista (
    lista_id NUMBER PRIMARY KEY,
    nev VARCHAR2(200),
    felhasznalo_id NUMBER,
    publikus NUMBER(1),
    letrehozas_datum TIMESTAMP,
    CONSTRAINT fk_lejatszasilista_felhasznalo FOREIGN KEY (felhasznalo_id) REFERENCES Felhasznalo(felhasznalo_id)
);

CREATE TABLE LejatszasiListaVideo (
    lista_id NUMBER,
    video_id NUMBER,
    pozicio NUMBER,
    hozzaadas_datum TIMESTAMP,
    PRIMARY KEY (lista_id, video_id),
    CONSTRAINT fk_llvideo_lejatszasilista FOREIGN KEY (lista_id) REFERENCES LejatszasiLista(lista_id),
    CONSTRAINT fk_llvideo_video FOREIGN KEY (video_id) REFERENCES Video(video_id)
);

CREATE TABLE Megtekintes (
    megtekintes_id NUMBER PRIMARY KEY,
    video_id NUMBER,
    felhasznalo_id NUMBER,
    megtekintes_datum TIMESTAMP,
    megtekintes_hossz NUMBER,
    CONSTRAINT fk_megtekintesek_video FOREIGN KEY (video_id) REFERENCES Video(video_id),
    CONSTRAINT fk_megtekintesek_felhasznalo FOREIGN KEY (felhasznalo_id) REFERENCES Felhasznalo(felhasznalo_id)
);

CREATE TABLE Ajanlasok (
    ajanlas_id NUMBER PRIMARY KEY,
    felhasznalo_id NUMBER,
    video_id NUMBER,
    ajanlas_erosseg NUMBER,
    ajanlas_datum TIMESTAMP,
    CONSTRAINT fk_ajanlasok_felhasznalo FOREIGN KEY (felhasznalo_id) REFERENCES Felhasznalo(felhasznalo_id),
    CONSTRAINT fk_ajanlasok_video FOREIGN KEY (video_id) REFERENCES Video(video_id)
);

-- sequencek letrehozasa
CREATE SEQUENCE felhasznalo_seq START WITH 1 INCREMENT BY 1;
CREATE SEQUENCE kategoria_seq START WITH 1 INCREMENT BY 1;
CREATE SEQUENCE video_seq START WITH 1 INCREMENT BY 1;
CREATE SEQUENCE hozzaszolas_seq START WITH 1 INCREMENT BY 1;
CREATE SEQUENCE cimke_seq START WITH 1 INCREMENT BY 1;
CREATE SEQUENCE lejatszasilista_seq START WITH 1 INCREMENT BY 1;
CREATE SEQUENCE megtekintes_seq START WITH 1 INCREMENT BY 1;
CREATE SEQUENCE ajanlas_seq START WITH 1 INCREMENT BY 1;