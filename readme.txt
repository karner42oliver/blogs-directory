=== PS Blogs-Verzeichnis ===
Contributors: DerN3rd (PSOURCE)
Tags: multisite, blogs, verzeichnis, classicpress, wordpress, psource
Requires at least: 3.8
Tested up to: 5.6
ClasicPress: 2.6.0
Stable tag: 1.0.0
Requires PHP: 7.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Ein modernes, performantes und durchsuchbares Verzeichnis fuer alle Seiten in Deiner ClassicPress Multisite - inklusive Layout-Optionen, Blog-Avataren, optionalen Bewertungen und Netzwerk-Suchmodulen.

== Description ==

PS Blogs-Verzeichnis erstellt automatisch eine zentrale Verzeichnisseite fuer Dein Netzwerk und zeigt dort alle relevanten Blogs in einer strukturierten Liste oder Grid-Ansicht an.

Das Plugin richtet sich an Multisite-Admins, die:

* ein gepflegtes Frontend-Verzeichnis aller Blogs anbieten wollen
* Suchfunktionen fuer Besucher bereitstellen wollen
* Blog-Branding (Avatar, Site-Icon, Logo) sauber integrieren moechten
* optional Bewertungsdaten pro Site einblenden wollen
* Netzwerkweite Einstellungen zentral steuern wollen

= Einsatzzweck =

Typische Einsatzszenarien:

* Mitglieder-/Community-Netzwerke mit vielen Subsites
* Bildungseinrichtungen mit Schul-, Kurs- oder Projektseiten
* Agentur- und Hosting-Setups mit zentraler Kundenuebersicht
* Content-Netzwerke, die nach Thema, Name oder Beschreibung auffindbar sein sollen

= Module im Plugin =

1. Core Routing & Ausgabe
Erzeugt die Verzeichnisseite, Routing und Frontend-Rendering (Landing, Suche, Navigation, List/Grid).

2. Netzwerk-Admin Einstellungen
Steuert Sortierung, Seitenlaenge, Sichtbarkeit, Layout/Farben, Recent-Posts und Reviews zentral im Netzwerk.

3. Blog-Avatar Modul
Pro Subsite kann ein eigener Blog-Avatar gesetzt, zugeschnitten und zurueckgesetzt werden.

4. Branding-Fallbacks
Avatar-Kette: Custom Blog-Avatar -> Site-Icon/Logo -> Default-Avatar.

5. Recent Posts Modul
Zeigt optional aktuelle Beitraege je Blog direkt im Verzeichnis an (inkl. Titel-/Excerpt-Laenge und Author-Avatar).

6. Site-Reviews Integration
Optionale Anzeige von Bewertungsdurchschnitt und Anzahl je Site (inkl. Netzwerkmodus off/allow/force).

7. Global Site Search Modul
Eigenes Suchmodul fuer netzwerkweite Beitrags-Suche inkl. Formular/Widget.

8. Performance- & Security-Layer
Abgesicherte AJAX-Endpunkte (WordPress AJAX + Nonce + Throttle + Ergebnislimits), Caching mit automatischer Invalidierung bei relevanten Updates.

= Features (Kurzueberblick) =

* Automatische Verzeichnisseite fuer die Hauptseite
* Durchsuchbare Blog-Liste mit Pagination
* Layout-Modus: Liste oder Grid
* Einstellbare Farben fuer Hintergrund, Titel, Text, Links und Rahmen
* Optional: Beschreibungen, Recent Posts, Author-Avatare
* Optional: Bewertungsanzeige ueber Site Reviews
* Blog-Avatar-Verwaltung pro Subsite
* Integration von Domain-Mapping (wenn vorhanden)
* Netzwerkweite Steuerung ueber die Einstellungen

= Verwendung =

Nach Aktivierung legt das Plugin auf der Hauptseite automatisch eine Verzeichnisseite an (Standard-Slug: blogs).

Falls die Seite nicht automatisch erstellt wurde:

1. Erstelle auf der Hauptseite manuell eine Seite.
2. Verwende den Slug blogs (oder Deinen eigenen, siehe Anpassung).
3. Oeffne Netzwerk-Admin -> Einstellungen -> Blogs-Verzeichnis und konfiguriere Darstellung/Suche.

= Anpassung =

Du kannst den Verzeichnis-Slug per Konstante anpassen (z.B. in einer mu-plugin/bootstrap Datei oder direkt im Plugin-Setup):

define('BLOGS_DIRECTORY_SLUG', 'dein-slug');

Danach einmal Permalinks neu speichern bzw. Rewrite-Regeln aktualisieren.

== Installation ==

1. Plugin in wp-content/plugins installieren.
2. Netzwerkweit aktivieren (Multisite).
3. In Netzwerk-Admin -> Einstellungen -> Blogs-Verzeichnis konfigurieren.
4. Verzeichnisseite auf der Hauptseite pruefen.

== Frequently Asked Questions ==

= Ist das Plugin nur fuer Multisite gedacht? =

Ja. Das Plugin ist fuer ClassicPress/WordPress Multisite-Netzwerke konzipiert.

= Kann ich private Blogs ausblenden? =

Ja. In den Netzwerk-Einstellungen kannst Du private Blogs ausblenden.

= Kann ich die Hauptseite aus dem Verzeichnis entfernen? =

Ja. Es gibt eine eigene Option, ob die Hauptseite angezeigt wird.

= Muss ich Site Reviews aktiviert haben? =

Nein. Die Reviews-Integration ist optional und kann pro Netzwerkmodus gesteuert werden.

= Wie wirkt sich Caching auf Aktualitaet aus? =

Das Plugin nutzt kurze Caches fuer Performance und invalidiert diese automatisch bei relevanten Aenderungen (Einstellungen, Blog-Inhalte, Avatar-Updates, Site-Lifecycle).

== ChangeLog ==

= 1.0.0 =
* PSOURCE RELEASE
* Security-Hardening fuer oeffentliche Such-Endpunkte (WordPress AJAX, Nonce, Throttle, Ergebnislimit)
* Performance-Optimierungen fuer Verzeichnissuche und Rendering
* Caching fuer Branding, Reviews, Recent Posts und Suchindex
* Automatische Cache-Invalidierung bei Einstellungen-, Blog- und Avatar-Updates
* Ueberarbeitete Dokumentation und Modulbeschreibung