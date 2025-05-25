-- Tárolt eljárás 1: Új videó beszúrása
CREATE OR REPLACE PROCEDURE uj_video_beszur (
    p_cim           IN VARCHAR2,
    p_hossz         IN NUMBER,
    p_feltolto_id   IN NUMBER,
    p_datum         IN DATE
) AS
BEGIN
    INSERT INTO Video (cim, hossz, felhasznalo_id, feltoltes_datum)
    VALUES (p_cim, p_hossz, p_feltolto_id, p_datum);
END;

-- Tárolt eljárás 2: Kedvencekhez ad

CREATE OR REPLACE PROCEDURE kedvenchez_ad (
    p_felhasznalo_id IN NUMBER,
    p_video_id       IN NUMBER
) AS
BEGIN
    INSERT INTO Kedvencek (felhasznalo_id, video_id, hozzaadas_datum)
    VALUES (p_felhasznalo_id, p_video_id, SYSTIMESTAMP);
END;