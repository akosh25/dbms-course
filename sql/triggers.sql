

-- 1. regisztrációs idő automatikus beállítása
CREATE OR REPLACE TRIGGER trg_felhasznalo_regido
BEFORE INSERT ON Felhasznalo
FOR EACH ROW
BEGIN
  IF :NEW.regisztracio_idopont IS NULL THEN
    :NEW.regisztracio_idopont := SYSTIMESTAMP;
  END IF;
END;
/

-- 2. kategoria törlésének naplózása
CREATE OR REPLACE TRIGGER trg_kategoria_torles
BEFORE DELETE ON Kategoria
FOR EACH ROW
BEGIN
  INSERT INTO Kategoria_LOG (id, nev, torles_idopont)
  VALUES (:OLD.kategoria_id, :OLD.nev, SYSTIMESTAMP);
END;
/

-- 3. hozzászólás létrehozási idő automatikus beállítása
CREATE OR REPLACE TRIGGER trg_hozzaszolas_ido
BEFORE INSERT ON Hozzaszolas
FOR EACH ROW
BEGIN
  IF :NEW.letrehozas_datum IS NULL THEN
    :NEW.letrehozas_datum := SYSTIMESTAMP;
  END IF;
END;
/

-- 4. utolsó bejelentkezés automatikus frissítése
CREATE OR REPLACE TRIGGER trg_felhasznalo_belep
BEFORE UPDATE ON Felhasznalo
FOR EACH ROW
BEGIN
  IF :OLD.utolso_bejelentkezes IS NULL OR :NEW.utolso_bejelentkezes != :OLD.utolso_bejelentkezes THEN
    :NEW.utolso_bejelentkezes := SYSTIMESTAMP;
  END IF;
END;
/
