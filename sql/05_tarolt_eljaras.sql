-- Tárolt eljárás: Új videó beszúrása
CREATE OR REPLACE PROCEDURE uj_video_beszur (
    p_cim           IN VARCHAR2,
    p_hossz         IN NUMBER,
    p_feltolto_id   IN NUMBER,
    p_datum         IN DATE
) AS
BEGIN
    INSERT INTO Video (cim, hossz, feltolto_id, feltoltes_datum)
    VALUES (p_cim, p_hossz, p_feltolto_id, p_datum);
END;
/