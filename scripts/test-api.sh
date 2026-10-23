#!/usr/bin/env bash
# ─────────────────────────────────────────────────────────────────────
#  Tests automatiques de l'API Toubilib (avec curl)
#  Usage :   ./scripts/test-api.sh                  (API sur http://localhost:7080)
#            ./scripts/test-api.sh http://autre:port
#  Chaque test envoie une requête et vérifie le STATUT HTTP reçu.
# ─────────────────────────────────────────────────────────────────────
BASE="${1:-http://localhost:7080}"
PRATICIEN="af7bb2f1-cc52-3388-b9bc-c0b89e7f4c5b"
PATIENT="d975aca7-50c5-3d16-b211-cf7d302cba50"
PRATICIEN_AVEC_RDVS="4305f5e9-be5a-4ccf-8792-7e07d7017363"
INCONNU="00000000-0000-4000-8000-000000000000"
REPONSE=$(mktemp)
OK=0; KO=0

# check "description" STATUT_ATTENDU METHODE URL [BODY_JSON]
check() {
    local desc="$1" attendu="$2" methode="$3" url="$4" body="$5" recu
    if [ -n "$body" ]; then
        recu=$(curl -s -o "$REPONSE" -w '%{http_code}' -X "$methode" "$BASE$url" \
               -H 'Content-Type: application/json' -d "$body")
    else
        recu=$(curl -s -o "$REPONSE" -w '%{http_code}' -X "$methode" "$BASE$url")
    fi
    if [ "$recu" = "$attendu" ]; then
        echo "  ✅ $recu  $methode $url  — $desc"; OK=$((OK+1))
    else
        echo "  ❌ $recu (attendu $attendu)  $methode $url  — $desc"
        echo "     réponse : $(head -c 300 "$REPONSE")"; KO=$((KO+1))
    fi
}

# un jour ouvré au hasard dans le futur (pour ne pas retomber sur le même créneau)
date_rdv() {
    local j=$((RANDOM % 300 + 30)) jour
    while :; do
        jour=$(date -d "+$j days" +%Y-%m-%d)
        [ "$(date -d "$jour" +%u)" -le 5 ] && break
        j=$((j+1))
    done
    printf '%s %02d:%02d' "$jour" $((RANDOM % 10 + 8)) $(( (RANDOM % 4) * 15 ))
}

echo "== Praticiens =="
check "liste des praticiens"            200 GET "/praticiens"
check "détail d'un praticien"           200 GET "/praticiens/$PRATICIEN"
check "praticien inexistant"            404 GET "/praticiens/$INCONNU"
check "agenda sur une période"          200 GET "/praticiens/$PRATICIEN_AVEC_RDVS/rdvs?debut=2025-12-01&fin=2025-12-07"
check "créneaux occupés"                200 GET "/praticiens/$PRATICIEN_AVEC_RDVS/creneaux?debut=2025-12-01&fin=2025-12-07"
check "agenda : date mal formée"        400 GET "/praticiens/$PRATICIEN/rdvs?debut=01-12-2025"
check "agenda : début après fin"        400 GET "/praticiens/$PRATICIEN/rdvs?debut=2025-12-07&fin=2025-12-01"
check "agenda : praticien inexistant"   404 GET "/praticiens/$INCONNU/rdvs"
check "recherche par spécialité"        200 GET "/praticiens?specialite=p%C3%A9diatrie"
check "recherche par ville"             200 GET "/praticiens?ville=nancy"
check "recherche spécialité + ville"    200 GET "/praticiens?specialite=ORL&ville=nancy"
check "recherche : balise HTML refusée" 400 GET "/praticiens?ville=%3Cscript%3E"

echo "== Patients =="
check "détail d'un patient"             200 GET "/patients/$PATIENT"
check "historique d'un patient"         200 GET "/patients/$PATIENT/rdvs"
check "patient inexistant"              404 GET "/patients/$INCONNU/rdvs"

echo "== Création d'un RDV =="
DATE=$(date_rdv)
RDV="{\"praticien_id\":\"$PRATICIEN\",\"patient_id\":\"$PATIENT\",\"date_heure_debut\":\"$DATE\",\"motif_visite\":\"CO\"}"
check "création ($DATE)"                201 POST "/rdvs" "$RDV"
ID=$(grep -o '"id":"[^"]*"' "$REPONSE" | head -1 | cut -d'"' -f4)
check "même créneau : praticien occupé" 409 POST "/rdvs" "$RDV"
check "motif inconnu"                   422 POST "/rdvs" "${RDV/\"CO\"/\"ZZ\"}"
check "praticien inexistant"            422 POST "/rdvs" "${RDV/$PRATICIEN/$INCONNU}"
check "un samedi"                       422 POST "/rdvs" "${RDV/$DATE/2027-01-02 10:00}"
check "date au mauvais format"          400 POST "/rdvs" "${RDV/$DATE/demain}"
check "body vide"                       400 POST "/rdvs" "{}"

echo "== Cycle de vie du RDV $ID =="
check "détail du RDV créé"              200 GET  "/rdvs/$ID"
check "RDV inexistant"                  404 GET  "/rdvs/$INCONNU"
check "honorer un RDV futur : refusé"   409 POST "/rdvs/$ID/honorer"
check "annuler"                         200 POST "/rdvs/$ID/annuler"
check "annuler une 2e fois : refusé"    409 POST "/rdvs/$ID/annuler"
check "ignorer un RDV annulé : refusé"  409 POST "/rdvs/$ID/ignorer"

echo "== HTTP / CORS =="
check "méthode non autorisée"           405 DELETE "/praticiens"
check "route inexistante"               404 GET  "/nimporte-quoi"
check "preflight CORS"                  204 OPTIONS "/rdvs"

rm -f "$REPONSE"
echo
echo "Résultat : $OK réussi(s), $KO échoué(s)"
[ "$KO" -eq 0 ]
