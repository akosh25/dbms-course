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
DROP TABLE VideoKategoria CASCADE CONSTRAINTS PURGE;
DROP TABLE Kategoria_LOG CASCADE CONSTRAINTS PURGE;

-- tablak letrehozasa

CREATE TABLE Felhasznalo (
    felhasznalo_id NUMBER PRIMARY KEY,
    felhasznalonev VARCHAR2(100) NOT NULL,
    email VARCHAR2(100) NOT NULL,
    jelszo VARCHAR2(100) NOT NULL,
	szerepkor VARCHAR2(20) DEFAULT 'user' NOT NULL,
    regisztracio_idopont TIMESTAMP,
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
    felhasznalo_id NUMBER,
    hossz NUMBER,
    is_short NUMBER(1),
    video_url VARCHAR2(300) UNIQUE,
    CONSTRAINT fk_video_felhasznalo FOREIGN KEY (felhasznalo_id) REFERENCES Felhasznalo(felhasznalo_id)
);

CREATE TABLE VideoKategoria (
    video_id NUMBER,
    kategoria_id NUMBER,
    PRIMARY KEY (video_id, kategoria_id),
    CONSTRAINT fk_vk_video FOREIGN KEY (video_id) REFERENCES Video(video_id),
    CONSTRAINT fk_vk_kategoria FOREIGN KEY (kategoria_id) REFERENCES Kategoria(kategoria_id)
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

CREATE TABLE Kategoria_LOG (
  id NUMBER,
  nev VARCHAR2(100),
  torles_idopont TIMESTAMP
);

-- sequencek törlése
DROP SEQUENCE felhasznalo_seq;
DROP SEQUENCE kategoria_seq;
DROP SEQUENCE video_seq;
DROP SEQUENCE hozzaszolas_seq;
DROP SEQUENCE cimke_seq;
DROP SEQUENCE lejatszasilista_seq;
DROP SEQUENCE megtekintes_seq;
DROP SEQUENCE ajanlas_seq;

-- sequencek letrehozasa
CREATE SEQUENCE felhasznalo_seq START WITH 1 INCREMENT BY 1;
CREATE SEQUENCE kategoria_seq START WITH 1 INCREMENT BY 1;
CREATE SEQUENCE video_seq START WITH 1 INCREMENT BY 1;
CREATE SEQUENCE hozzaszolas_seq START WITH 1 INCREMENT BY 1;
CREATE SEQUENCE cimke_seq START WITH 1 INCREMENT BY 1;
CREATE SEQUENCE lejatszasilista_seq START WITH 1 INCREMENT BY 1;
CREATE SEQUENCE megtekintes_seq START WITH 1 INCREMENT BY 1;
CREATE SEQUENCE ajanlas_seq START WITH 1 INCREMENT BY 1;


-- Temesvári Ráhel Anna, V1140F
-- Idegen kulcsok korlátozásának letiltása, hogy törölni lehessen az adatokat
ALTER TABLE Ajanlasok DISABLE CONSTRAINT fk_ajanlasok_felhasznalo;
ALTER TABLE Ajanlasok DISABLE CONSTRAINT fk_ajanlasok_video;
ALTER TABLE Megtekintes DISABLE CONSTRAINT fk_megtekintesek_felhasznalo;
ALTER TABLE Megtekintes DISABLE CONSTRAINT fk_megtekintesek_video;
ALTER TABLE Kedvencek DISABLE CONSTRAINT fk_kedvencek_felhasznalo;
ALTER TABLE Kedvencek DISABLE CONSTRAINT fk_kedvencek_video;
ALTER TABLE LejatszasiListaVideo DISABLE CONSTRAINT fk_llvideo_lejatszasilista;
ALTER TABLE LejatszasiListaVideo DISABLE CONSTRAINT fk_llvideo_video;
ALTER TABLE LejatszasiLista DISABLE CONSTRAINT fk_lejatszasilista_felhasznalo;
ALTER TABLE VideoCimke DISABLE CONSTRAINT fk_videocimke_video;
ALTER TABLE VideoCimke DISABLE CONSTRAINT fk_videocimke_cimke;
ALTER TABLE Hozzaszolas DISABLE CONSTRAINT fk_hozzaszolas_felhasznalo;
ALTER TABLE Hozzaszolas DISABLE CONSTRAINT fk_hozzaszolas_video;
ALTER TABLE VideoMetadata DISABLE CONSTRAINT fk_videometadata_video;


-- Adatok törlése a táblákból
DELETE FROM Ajanlasok;
DELETE FROM Megtekintes;
DELETE FROM Kedvencek;
DELETE FROM LejatszasiListaVideo;
DELETE FROM LejatszasiLista;
DELETE FROM VideoCimke;
DELETE FROM Cimke;
DELETE FROM Hozzaszolas;
DELETE FROM VideoMetadata;
DELETE FROM Video;
DELETE FROM Kategoria;
DELETE FROM Felhasznalo;

-- Korlátotások engedélyezése
ALTER TABLE Ajanlasok ENABLE CONSTRAINT fk_ajanlasok_felhasznalo;
ALTER TABLE Ajanlasok ENABLE CONSTRAINT fk_ajanlasok_video;
ALTER TABLE Megtekintes ENABLE CONSTRAINT fk_megtekintesek_felhasznalo;
ALTER TABLE Megtekintes ENABLE CONSTRAINT fk_megtekintesek_video;
ALTER TABLE Kedvencek ENABLE CONSTRAINT fk_kedvencek_felhasznalo;
ALTER TABLE Kedvencek ENABLE CONSTRAINT fk_kedvencek_video;
ALTER TABLE LejatszasiListaVideo ENABLE CONSTRAINT fk_llvideo_lejatszasilista;
ALTER TABLE LejatszasiListaVideo ENABLE CONSTRAINT fk_llvideo_video;
ALTER TABLE LejatszasiLista ENABLE CONSTRAINT fk_lejatszasilista_felhasznalo;
ALTER TABLE VideoCimke ENABLE CONSTRAINT fk_videocimke_video;
ALTER TABLE VideoCimke ENABLE CONSTRAINT fk_videocimke_cimke;
ALTER TABLE Hozzaszolas ENABLE CONSTRAINT fk_hozzaszolas_felhasznalo;
ALTER TABLE Hozzaszolas ENABLE CONSTRAINT fk_hozzaszolas_video;
ALTER TABLE VideoMetadata ENABLE CONSTRAINT fk_videometadata_video;
ALTER TABLE Video ENABLE CONSTRAINT fk_video_kategoria;
ALTER TABLE Video ENABLE CONSTRAINT fk_video_felhasznalo;

-- Sequence-k indítása
ALTER SEQUENCE felhasznalo_seq RESTART START WITH 1;
ALTER SEQUENCE kategoria_seq RESTART START WITH 1;
ALTER SEQUENCE video_seq RESTART START WITH 1;
ALTER SEQUENCE hozzaszolas_seq RESTART START WITH 1;
ALTER SEQUENCE cimke_seq RESTART START WITH 1;
ALTER SEQUENCE lejatszasilista_seq RESTART START WITH 1;
ALTER SEQUENCE megtekintes_seq RESTART START WITH 1;
ALTER SEQUENCE ajanlas_seq RESTART START WITH 1;


