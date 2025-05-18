-- 1. Minden felhasználó adatainak lekérdezése
SELECT * FROM Felhasznalo;

-- 2. Minden videó címe és hossza
SELECT cim, hossz FROM Video;

-- 3. Minden kategória neve
SELECT nev FROM Kategoria;

-- 4. Minden címke neve
SELECT nev FROM Cimke;

-- 5. Videók, amik rövidek (is_short = 1)
SELECT cim FROM Video WHERE is_short = 1;

-- 6. Felhasználók, akiknek van profilképük
SELECT felhasznalonev FROM Felhasznalo WHERE profilkep_url IS NOT NULL;

-- 7. Kategóriák, amelyek leírása nem üres
SELECT nev FROM Kategoria WHERE leiras IS NOT NULL;

-- 8. Videók feltöltési dátuma szerint (legfrissebb elöl)
SELECT cim, feltoltes_datum FROM Video ORDER BY feltoltes_datum DESC;

-- 9. Felhasználók, akik "admin" szerepkörben vannak
SELECT felhasznalonev FROM Felhasznalo WHERE szerepkor = 'admin';

-- 10. Videók, amelyeknek a hossza több mint 10 perc (600 másodperc)
SELECT cim FROM Video WHERE hossz > 600;

-- 11. Összesített felhasználószám
SELECT COUNT(*) AS felhasznalo_szam FROM Felhasznalo;

-- 12. Az első 5 videó (abc sorrendben cím alapján)
SELECT * FROM Video ORDER BY cim FETCH FIRST 5 ROWS ONLY;