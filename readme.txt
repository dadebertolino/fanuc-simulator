=== FANUC ER-4iA Robot Simulator ===
Contributors: davidebertolino
Tags: robotica, fanuc, simulatore, didattica, ITIS
Requires at least: 5.0
Tested up to: 6.4
Requires PHP: 7.4
Stable tag: 1.2.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Simulatore web interattivo del braccio robotico FANUC ER-4iA per la didattica.

== Descrizione ==

Plugin didattico per l'insegnamento della robotica industriale negli istituti tecnici.
Include un simulatore 3D interattivo del braccio FANUC ER-4iA con:

* Modello 3D a 6 assi con pinza animata
* Cinematica diretta e inversa analitica 6-DOF, coerenti con il modello
* Editor TP (Teach Pendant) con esecuzione passo-passo, breakpoint di riga e
  interpretazione di J/L/C, DO, WAIT, R[], IF, JMP, LBL, FOR/ENDFOR
* Import ed export in formato FANUC .LS
* Pannello I/O digitali (8 DO, 8 DI) e 10 registri numerici
* Sfide progressive con verifica automatica
* Visualizzazione inviluppo di lavoro e terne dei link

Il simulatore funziona interamente nel browser e non richiede connessione a
Internet: la libreria 3D e i caratteri sono inclusi nel plugin. Non viene
contattato alcun servizio esterno, quindi nessun dato dell'utente lascia il sito.

== Installazione ==

1. Carica la cartella `fanuc-simulator` in `/wp-content/plugins/`
2. Attiva il plugin dal menu Plugin di WordPress
3. Le pagine "Simulatore" ed "Esercizi" vengono create automaticamente

Non serve copiare o rinominare alcun file a mano.

== Aggiornamenti ==

Il plugin si aggiorna dalla bacheca di WordPress come quelli del repository
ufficiale: controlla le Release del repository GitHub
`dadebertolino/fanuc-simulator` e propone la nuova versione quando il tag
supera quella installata. Il controllo e' in cache per 12 ore.

== Shortcode ==

`[fanuc_sim height="700px" width="100%" class=""]`

Parametri:

* `height` - altezza del riquadro, valore CSS. Default `700px`
* `width`  - larghezza del riquadro, valore CSS. Default `100%`
* `class`  - classe CSS aggiuntiva sul contenitore. Default vuoto

== Disinstallazione ==

Il plugin non rimuove le due pagine create all'attivazione: possono contenere
esercizi modificati dal docente. Se non servono piu', vanno eliminate a mano
dal menu Pagine.

== Requisiti ==

Un browser con supporto WebGL. Il simulatore e' pensato per desktop; sotto i
768 px di larghezza il pannello di controllo si dispone sotto la vista 3D.

== Componenti di terze parti ==

* three.js r128 - MIT - https://threejs.org
* JetBrains Mono - SIL Open Font License 1.1
* IBM Plex Sans - SIL Open Font License 1.1

Dettagli e provenienza in `assets/vendor/README.txt`.

== Changelog ==

= 1.2.0 =
* Aggiornamento automatico da GitHub Releases (inc/class-updater.php), come
  negli altri plugin della suite
* Pagina di amministrazione riscritta sul design system condiviso db-admin-ui
* Workflow di release che verifica versione, lint, assenza di risorse esterne
  e completezza del pacchetto prima di pubblicare
* Corretta la cinematica inversa: risolveva una geometria diversa da quella del
  modello 3D, con errori di centinaia di millimetri sui bersagli cartesiani
* Corretta la lettura di W-P-R: veniva estratta dalla matrice trasposta e con
  gli assi scambiati rispetto alla convenzione FANUC (W su X, P su Y, R su Z)
* Corretto il frame mondo, che era sinistrorso e invertiva il senso di J1
* Corretto il pannello TCP, che mostrava la posa del fotogramma precedente
* Corretto un tag di chiusura che faceva uscire il tab Sfide dal pannello e
  lasciava I/O e registri visibili su tutti i tab
* Corretta l'evidenziazione della riga in esecuzione, sfasata dai commenti
* Le sfide su I/O e registri ora si completano davvero
* FOR/ENDFOR e IF su DI/DO ora vengono interpretati; le istruzioni non
  riconosciute fermano il programma invece di essere ignorate
* I punti non insegnati vengono generati ma segnalati come automatici
* Numerazione delle righe corretta in TEACH e nei pulsanti di inserimento
* L'export .LS scrive l'orientamento reale del punto invece di W=180 P=0 R=0
* L'inviluppo di lavoro e' calcolato dalla cinematica reale invece che da una
  formula che ignorava J3
* three.js e i caratteri sono inclusi nel plugin: niente CDN, niente richieste
  a Google Fonts, funzionamento offline
* Il pulsante "DH" e' stato rinominato "TERNE": mostra le terne dei link del
  modello, non una tabella di Denavit-Hartenberg

= 1.0.0 =
* Prima release
