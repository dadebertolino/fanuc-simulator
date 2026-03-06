=== FANUC ER-4iA Robot Simulator ===
Contributors: laboratorio-robotica
Tags: robotica, fanuc, simulatore, didattica, ITIS
Requires at least: 5.0
Tested up to: 6.4
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later

Simulatore web interattivo del braccio robotico FANUC ER-4iA per la didattica.

== Descrizione ==

Plugin didattico per l'insegnamento della robotica industriale negli istituti tecnici.
Include un simulatore 3D interattivo del braccio FANUC ER-4iA con:

* Modello 3D dettagliato a 6 assi
* Cinematica inversa analitica 6-DOF
* Editor TP (Teach Pendant) con syntax highlighting
* Pannello I/O digitali e registri
* Sfide progressive gamificate
* Salvataggio progressi per studenti autenticati

== Installazione ==

1. Carica la cartella `fanuc-simulator` in `/wp-content/plugins/`
2. Attiva il plugin dal menu Plugin di WordPress
3. Copia il file `simulator.js` (il file HTML standalone) in `assets/js/`
4. Le pagine "Simulatore" e "Esercizi" vengono create automaticamente

== Shortcode ==

`[fanuc_sim height="700px" width="100%" tab="joints"]`

Parametri: height, width, tab (joints|cart|tp|ch), theme (dark|light)

== Note ==

Il file `assets/js/simulator.js` deve contenere il codice JavaScript
estratto dal file HTML standalone (il contenuto del tag <script>).
Il file `assets/css/simulator.css` deve contenere gli stili CSS.
