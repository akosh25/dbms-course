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
ALTER TABLE Video DISABLE CONSTRAINT fk_video_kategoria;
ALTER TABLE Video DISABLE CONSTRAINT fk_video_felhasznalo;

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

-- 1. Adatok beszúrása: Felhasznalo
INSERT INTO Felhasznalo (felhasznalo_id, felhasznalonev, email, jelszo, szerepkor, regisztracio_idopont, utolso_bejelentkezes, profilkep_url, bio)
VALUES (felhasznalo_seq.NEXTVAL, 'Munkácsy Mihály', 'munkacsy.mihaly@pelda.com', 'A_zeneszoba1878', 'user', TIMESTAMP '2022-01-15 10:30:00', TIMESTAMP '2023-05-20 14:45:00', 'https://pelda.com/profilkepek/munkacsy.jpg', '...');

INSERT INTO Felhasznalo (felhasznalo_id, felhasznalonev, email, jelszo, szerepkor, regisztracio_idopont, utolso_bejelentkezes, profilkep_url, bio)
VALUES (felhasznalo_seq.NEXTVAL, 'Liszt Ferenc', 'liszt.ferenc@pelda.com', 'Magyar_rapszodiak1853', 'admin', TIMESTAMP '2022-02-20 09:15:00', TIMESTAMP '2023-05-19 16:30:00', 'https://pelda.com/profilkepek/lisztferenc.jpg', 'Minden művészet egyazon forrásból ered.');

INSERT INTO Felhasznalo (felhasznalo_id, felhasznalonev, email, jelszo, szerepkor, regisztracio_idopont, utolso_bejelentkezes, profilkep_url, bio)
VALUES (felhasznalo_seq.NEXTVAL, 'Robert Capa', 'robert.capa@pelda.com', 'Kisse_elmosodva1947', 'user', TIMESTAMP '2022-03-10 14:20:00', TIMESTAMP '2023-05-18 11:25:00', 'https://pelda.com/profilkepek/robertcapa.jpg', 'Ha nem elég jók a képeid, nem voltál elég közel.');

INSERT INTO Felhasznalo (felhasznalo_id, felhasznalonev, email, jelszo, szerepkor, regisztracio_idopont, utolso_bejelentkezes, profilkep_url, bio)
VALUES (felhasznalo_seq.NEXTVAL, 'Jávor Pál', 'javor.pal@pelda.com', 'Halalos_tavasz1939', 'user', TIMESTAMP '2022-04-05 11:45:00', TIMESTAMP '2023-05-17 19:10:00', 'https://pelda.com/profilkepek/javorpal.jpg', 'Csak azt is eljátszottam, ha kellett.');

INSERT INTO Felhasznalo (felhasznalo_id, felhasznalonev, email, jelszo, szerepkor, regisztracio_idopont, utolso_bejelentkezes, profilkep_url, bio)
VALUES (felhasznalo_seq.NEXTVAL, 'Babits Mihály', 'babits.mihaly@pelda.com', 'A_golyakalifa1913', 'user', TIMESTAMP '2022-05-12 16:50:00', TIMESTAMP '2023-05-16 08:55:00', 'https://pelda.com/profilkepek/babitsmihaly.jpg', 'A kimerült író olyan, mint a megcsalt férj: maga veszi észre legkésőbb a szomorú igazságot.');

-- 2. Adatok beszúrása: Kategoria
INSERT INTO Kategoria (kategoria_id, nev, leiras)
VALUES (kategoria_seq.NEXTVAL, 'Festészet', 'Videók festőkről, festészetről');

INSERT INTO Kategoria (kategoria_id, nev, leiras)
VALUES (kategoria_seq.NEXTVAL, 'Zeneművészet', 'Koncertfelvételek, zeneművek videói, okatatóanyagok');

INSERT INTO Kategoria (kategoria_id, nev, leiras)
VALUES (kategoria_seq.NEXTVAL, 'Fotóművészet', 'Fotózással és fotósokkal kapcsolatos videók');

INSERT INTO Kategoria (kategoria_id, nev, leiras)
VALUES (kategoria_seq.NEXTVAL, 'Színészet', 'Színészekkel és színészettel foglalkozó videók');

INSERT INTO Kategoria (kategoria_id, nev, leiras)
VALUES (kategoria_seq.NEXTVAL, 'Irodalom-művészet', 'Irodalommal, foglalkozó videók, oktatóanyagok');

-- 3. Adatok beszúrása: Video
INSERT INTO Video (video_id, cim, leiras, feltoltes_datum, kategoria_id, felhasznalo_id, hossz, is_short, video_url)
VALUES (video_seq.NEXTVAL, 'Bevezetés a festészetbe', 'A színek pszichológiai hatása, színkeverés', TIMESTAMP '2023-02-10 09:30:00', 1, 3, 845, 0, 'https://pelda.com/videok/szinkeveres.mp4');

INSERT INTO Video (video_id, cim, leiras, feltoltes_datum, kategoria_id, felhasznalo_id, hossz, is_short, video_url)
VALUES (video_seq.NEXTVAL, 'Liszt Ferenc: II. Magyar rapszódia', 'November 17-én a hazai művészeti élet 69 kiválósága vette át a Nemzet Művésze díjat a Vigadóban. Az ünnepi rendezvényen Liszt Ferenc II. Magyar rapszódiáját Bogányi Gergely Kossuth- és Liszt Ferenc-díjas zongoraművész előadásában hallgathatták meg a díjazottak.', TIMESTAMP '2023-03-25 14:15:00', 2, 4, 1256, 0, 'https://pelda.com/videok/magyar-rapszodia.mp4');

INSERT INTO Video (video_id, cim, leiras, feltoltes_datum, kategoria_id, felhasznalo_id, hossz, is_short, video_url)
VALUES (video_seq.NEXTVAL, 'Hogyan készíts jó fotókat a nyaraláson', 'Tanuld meg, a fotózás fortályait, hogy a barátaid számára is élvezetesek legyenek a fotóid!', TIMESTAMP '2023-05-05 11:20:00', 3, 5, 732, 0, 'https://pelda.com/videok/fotozas.mp4');

INSERT INTO Video (video_id, cim, leiras, feltoltes_datum, kategoria_id, felhasznalo_id, hossz, is_short, video_url)
VALUES (video_seq.NEXTVAL, 'Csehov - Cseresznyéskert', '2007. március 17-i bemutató felvétele a Vígszínházból.', TIMESTAMP '2023-02-15 16:45:00', 4, 3, 3650, 0, 'https://pelda.com/videok/cseresznyeskert.mp4');

INSERT INTO Video (video_id, cim, leiras, feltoltes_datum, kategoria_id, felhasznalo_id, hossz, is_short, video_url)
VALUES (video_seq.NEXTVAL, 'Magyar irodalom-érettségi témakörök', 'Átbeszáljük az érettségi témaköröket, hogy ne érjen meglepetés az érettségin!', TIMESTAMP '2023-01-30 10:00:00', 5, 1, 612, 1, 'https://pelda.com/videok/irodalom-erettsegi.mp4');

-- 4. Adatok beszúrása: VideoMetadata
INSERT INTO VideoMetadata (video_url, miniatur_url, beagyazasi_kod)
VALUES ('https://pelda.com/videok/szinkeveres.mp4', 'https://pelda.com/miniaturok/szinkeveres.jpg', '<iframe src="https://pelda.com/beagyazas/szinkeveres"></iframe>');

INSERT INTO VideoMetadata (video_url, miniatur_url, beagyazasi_kod)
VALUES ('https://pelda.com/videok/magyar-rapszodia.mp4', 'https://pelda.com/miniaturok/magyar-rapszodia.jpg', '<iframe src="https://pelda.com/beagyazas/magyar-rapszodia"></iframe>');

INSERT INTO VideoMetadata (video_url, miniatur_url, beagyazasi_kod)
VALUES ('https://pelda.com/videok/fotozas.mp4', 'https://pelda.com/miniaturok/fotozas.jpg', '<iframe src="https://pelda.com/beagyazas/fotozas"></iframe>');

INSERT INTO VideoMetadata (video_url, miniatur_url, beagyazasi_kod)
VALUES ('https://pelda.com/videok/cseresznyeskert.mp4', 'https://pelda.com/miniaturok/cseresznyeskert.jpg', '<iframe src="https://pelda.com/beagyazas/cseresznyeskert"></iframe>');

INSERT INTO VideoMetadata (video_url, miniatur_url, beagyazasi_kod)
VALUES ('https://pelda.com/videok/irodalom-erettsegi.mp4', 'https://pelda.com/miniaturok/irodalom-erettsegi.jpg', '<iframe src="https://pelda.com/beagyazas/irodalom-erettsegi"></iframe>');

-- 5. Adatok beszúrása: Hozzaszolas
INSERT INTO Hozzaszolas (hozzaszolas_id, felhasznalo_id, video_id, hozzaszolas_szoveg, letrehozas_datum)
VALUES (hozzaszolas_seq.NEXTVAL, 2, 1, 'Nagyon sokat tanultam a videódból! Lesz videó az ecsethasználatról valamikor?', TIMESTAMP '2023-04-10 14:25:00');

INSERT INTO Hozzaszolas (hozzaszolas_id, felhasznalo_id, video_id, hozzaszolas_szoveg, letrehozas_datum)
VALUES (hozzaszolas_seq.NEXTVAL, 2, 2, 'Köszi a feltöltést! Sajnos nem lehettem ott az előadáson, de nagyon örülök, hogy meghallgathattam felvételről.', TIMESTAMP '2023-03-26 09:30:00');

INSERT INTO Hozzaszolas (hozzaszolas_id, felhasznalo_id, video_id, hozzaszolas_szoveg, letrehozas_datum)
VALUES (hozzaszolas_seq.NEXTVAL, 3, 3, 'Kipróbáltam ezeket a tippeket és nagyon látványos fotókat készítettem a családi nyaraláson! Lesz mit a fotóalbumba tenni!', TIMESTAMP '2023-05-06 18:15:00');

INSERT INTO Hozzaszolas (hozzaszolas_id, felhasznalo_id, video_id, hozzaszolas_szoveg, letrehozas_datum)
VALUES (hozzaszolas_seq.NEXTVAL, 5, 4, 'Csodálatos darab és nagyon jó felvétel!', TIMESTAMP '2023-02-16 20:45:00');

INSERT INTO Hozzaszolas (hozzaszolas_id, felhasznalo_id, video_id, hozzaszolas_szoveg, letrehozas_datum)
VALUES (hozzaszolas_seq.NEXTVAL, 4, 5, 'Én tavaly érettségiztem és nagyon sokat segített ez a videó a felkészülésben.', TIMESTAMP '2023-01-31 11:10:00');

-- 6. Adatok beszúrása: Cimke
INSERT INTO Cimke (cimke_id, nev)
VALUES (cimke_seq.NEXTVAL, 'festészet');

INSERT INTO Cimke (cimke_id, nev)
VALUES (cimke_seq.NEXTVAL, 'zene');

INSERT INTO Cimke (cimke_id, nev)
VALUES (cimke_seq.NEXTVAL, 'fotózás');

INSERT INTO Cimke (cimke_id, nev)
VALUES (cimke_seq.NEXTVAL, 'színház');

INSERT INTO Cimke (cimke_id, nev)
VALUES (cimke_seq.NEXTVAL, 'oktatás');

-- 7. Adatok beszúrása: VideoCimke
INSERT INTO VideoCimke (video_id, cimke_id)
VALUES (1, 1);

INSERT INTO VideoCimke (video_id, cimke_id)
VALUES (2, 2);

INSERT INTO VideoCimke (video_id, cimke_id)
VALUES (3, 3);

INSERT INTO VideoCimke (video_id, cimke_id)
VALUES (4, 4);

INSERT INTO VideoCimke (video_id, cimke_id)
VALUES (5, 5);

-- 8. Adatok beszúrása: LejatszasiLista
INSERT INTO LejatszasiLista (lista_id, nev, felhasznalo_id, publikus, letrehozas_datum)
VALUES (lejatszasilista_seq.NEXTVAL, 'Tutoriálok', 1, 1, TIMESTAMP '2023-01-20 15:30:00');

INSERT INTO LejatszasiLista (lista_id, nev, felhasznalo_id, publikus, letrehozas_datum)
VALUES (lejatszasilista_seq.NEXTVAL, 'Élő zene', 4, 1, TIMESTAMP '2023-02-10 12:45:00');

INSERT INTO LejatszasiLista (lista_id, nev, felhasznalo_id, publikus, letrehozas_datum)
VALUES (lejatszasilista_seq.NEXTVAL, 'Fotózás', 5, 1, TIMESTAMP '2023-03-05 09:20:00');

INSERT INTO LejatszasiLista (lista_id, nev, felhasznalo_id, publikus, letrehozas_datum)
VALUES (lejatszasilista_seq.NEXTVAL, 'Színdarabok', 3, 1, TIMESTAMP '2023-04-15 18:10:00');

INSERT INTO LejatszasiLista (lista_id, nev, felhasznalo_id, publikus, letrehozas_datum)
VALUES (lejatszasilista_seq.NEXTVAL, 'Érettségi felkészülés - irodalom', 2, 0, TIMESTAMP '2023-05-01 14:00:00');

-- 9. Adatok beszúrása: LejatszasiListaVideo
INSERT INTO LejatszasiListaVideo (lista_id, video_id, pozicio, hozzaadas_datum)
VALUES (1, 1, 1, TIMESTAMP '2023-04-11 10:15:00');

INSERT INTO LejatszasiListaVideo (lista_id, video_id, pozicio, hozzaadas_datum)
VALUES (2, 2, 1, TIMESTAMP '2023-03-27 16:30:00');

INSERT INTO LejatszasiListaVideo (lista_id, video_id, pozicio, hozzaadas_datum)
VALUES (3, 3, 1, TIMESTAMP '2023-05-07 11:45:00');

INSERT INTO LejatszasiListaVideo (lista_id, video_id, pozicio, hozzaadas_datum)
VALUES (4, 4, 1, TIMESTAMP '2023-02-17 19:20:00');

INSERT INTO LejatszasiListaVideo (lista_id, video_id, pozicio, hozzaadas_datum)
VALUES (5, 5, 1, TIMESTAMP '2023-02-01 13:10:00');

-- 10. Adatok beszúrása: Kedvencek
INSERT INTO Kedvencek (felhasznalo_id, video_id, hozzaadas_datum)
VALUES (1, 3, TIMESTAMP '2023-05-08 09:45:00');

INSERT INTO Kedvencek (felhasznalo_id, video_id, hozzaadas_datum)
VALUES (2, 5, TIMESTAMP '2023-02-02 14:30:00');

INSERT INTO Kedvencek (felhasznalo_id, video_id, hozzaadas_datum)
VALUES (3, 2, TIMESTAMP '2023-03-28 17:15:00');

INSERT INTO Kedvencek (felhasznalo_id, video_id, hozzaadas_datum)
VALUES (4, 1, TIMESTAMP '2023-04-12 11:20:00');

INSERT INTO Kedvencek (felhasznalo_id, video_id, hozzaadas_datum)
VALUES (5, 4, TIMESTAMP '2023-02-18 20:05:00');

-- 11. Adatok beszúrása: Megtekintes
INSERT INTO Megtekintes (megtekintes_id, video_id, felhasznalo_id, megtekintes_datum, megtekintes_hossz)
VALUES (megtekintes_seq.NEXTVAL, 1, 2, TIMESTAMP '2023-04-11 13:10:00', 845);

INSERT INTO Megtekintes (megtekintes_id, video_id, felhasznalo_id, megtekintes_datum, megtekintes_hossz)
VALUES (megtekintes_seq.NEXTVAL, 2, 1, TIMESTAMP '2023-03-26 10:25:00', 1200);

INSERT INTO Megtekintes (megtekintes_id, video_id, felhasznalo_id, megtekintes_datum, megtekintes_hossz)
VALUES (megtekintes_seq.NEXTVAL, 3, 3, TIMESTAMP '2023-05-06 19:30:00', 732);

INSERT INTO Megtekintes (megtekintes_id, video_id, felhasznalo_id, megtekintes_datum, megtekintes_hossz)
VALUES (megtekintes_seq.NEXTVAL, 4, 5, TIMESTAMP '2023-02-16 21:15:00', 3000);

INSERT INTO Megtekintes (megtekintes_id, video_id, felhasznalo_id, megtekintes_datum, megtekintes_hossz)
VALUES (megtekintes_seq.NEXTVAL, 5, 4, TIMESTAMP '2023-01-31 12:40:00', 612);

-- 12. Adatok beszúrása: Ajanlasok
INSERT INTO Ajanlasok (ajanlas_id, felhasznalo_id, video_id, ajanlas_erosseg, ajanlas_datum)
VALUES (ajanlas_seq.NEXTVAL, 1, 4, 0.85, TIMESTAMP '2023-05-20 15:00:00');

INSERT INTO Ajanlasok (ajanlas_id, felhasznalo_id, video_id, ajanlas_erosseg, ajanlas_datum)
VALUES (ajanlas_seq.NEXTVAL, 2, 2, 0.78, TIMESTAMP '2023-05-19 17:00:00');

INSERT INTO Ajanlasok (ajanlas_id, felhasznalo_id, video_id, ajanlas_erosseg, ajanlas_datum)
VALUES (ajanlas_seq.NEXTVAL, 3, 5, 0.92, TIMESTAMP '2023-05-18 12:00:00');

INSERT INTO Ajanlasok (ajanlas_id, felhasznalo_id, video_id, ajanlas_erosseg, ajanlas_datum)
VALUES (ajanlas_seq.NEXTVAL, 4, 3, 0.65, TIMESTAMP '2023-05-17 20:00:00');

INSERT INTO Ajanlasok (ajanlas_id, felhasznalo_id, video_id, ajanlas_erosseg, ajanlas_datum)
VALUES (ajanlas_seq.NEXTVAL, 5, 1, 0.73, TIMESTAMP '2023-05-16 09:30:00');

COMMIT;

