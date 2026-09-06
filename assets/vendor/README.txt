Librerie di terze parti incluse nel plugin
==========================================

three.min.js
  three.js r128 - https://threejs.org
  Copyright 2010-2021 Three.js Authors - Licenza MIT
  Scaricato da https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js

  Incluso localmente invece di essere caricato da CDN perche':
   - le linee guida di WordPress.org vietano il caricamento di script remoti;
   - il simulatore deve funzionare anche senza connessione (laboratorio);
   - evita una richiesta a un servizio terzo a ogni apertura della pagina.

  La versione e' fissata a r128: dalla r155 three.js ha cambiato la gestione
  del colore e l'intensita' delle luci, quindi un aggiornamento va verificato
  visivamente prima di essere adottato.

../fonts/JetBrainsMono.woff2, ../fonts/IBMPlexSans.woff2
  JetBrains Mono e IBM Plex Sans, sottoinsieme "latin", formato variabile
  (un solo file copre tutti i pesi usati).
  SIL Open Font License 1.1
  Scaricati da fonts.gstatic.com. Self-hosted per gli stessi motivi:
  il caricamento da fonts.googleapis.com comporta l'invio dell'IP
  dell'utente a Google, problematico per un ente pubblico (GDPR).
