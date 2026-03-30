# School API - Gestion des écoles et étudiants
API REST simple en Go permettant de gérer :
- Écoles
- Classes
- Étudiants
- Matières
- Notes
Le projet est conçu pour être utilisé avec **MySQL + Docker** et déployé sur **Azure / Kubernetes**.
---
#  Lancement du projet
## 1. Prérequis
- Go installé
- MySQL (ou Docker)
- Postman ou curl
---
## 2. Variables d’environnement
Si tu lances en local (GoLand par exemple) :
 
DB_HOST=localhost
DB_PORT=3306
DB_USER=root
DB_PASSWORD=root
DB_NAME=school

---
## 3. Lancer l’API
go run main.go

### API disponible sur :
http://localhost:3000
##  Health Check
### GET /health
curl http://localhost:3000/health
Réponse :
{
  "status": "ok"
}
##  Écoles
GET /ecoles
Récupérer toutes les écoles
curl http://localhost:3000/ecoles
POST /ecoles
Créer une école
{
  "nom": "Ecole Cloud Paris",
  "adresse": "10 rue Azure",
  "ville": "Paris",
  "telephone": "0101010101",
  "email": "contact@cloudparis.fr",
  "directeur": "Mme Azure"
}
## Classes
GET /classes
curl http://localhost:3000/classes
POST /classes
{
  "nom": "L3 CLOUD A",
  "niveau": "Licence 3",
  "filiere": "Cloud Computing",
  "annee_scolaire": "2025-2026",
  "salle": "C301",
  "professeur_principal": "M. Kubernetes",
  "ecole_id": 1
}
## Matières
GET /matieres
curl http://localhost:3000/matieres
POST /matieres
{
  "nom": "Azure",
  "coefficient": 4,
  "enseignant": "M. DevOps"
}
##  Étudiants
GET /etudiants
Liste de tous les étudiants
curl http://localhost:3000/etudiants
### GET /etudiants/{id}
curl http://localhost:3000/etudiants/1
POST /etudiants
Créer un étudiant
{
  "nom": "Bamba",
  "prenom": "Moussa",
  "matricule": "ETU2026999",
  "email": "moussa.bamba@ecole.fr",
  "telephone": "0606060606",
  "date_naissance": "2003-06-10",
  "genre": "M",
  "classe_id": 1
}
### PUT /etudiants/{id}
Modifier un étudiant
{
  "nom": "Diallo",
  "prenom": "Mamadou",
  "matricule": "ETU2026001",
  "email": "updated@ecole.fr",
  "telephone": "0611111111",
  "date_naissance": "2003-05-14",
  "genre": "M",
  "classe_id": 1
}
### DELETE /etudiants/{id}
curl -X DELETE http://localhost:3000/etudiants/1
##  Notes
### GET /notes
curl http://localhost:3000/notes
### GET /notes/{id}
curl http://localhost:3000/notes/1
POST /notes
Ajouter une note
{
  "valeur": 18.5,
  "type_note": "Examen",
  "date_note": "2026-03-20",
  "etudiant_id": 1,
  "matiere_id": 1
}
### PUT /notes/{id}
{
  "valeur": 19.0,
  "type_note": "Examen final",
  "date_note": "2026-03-21",
  "etudiant_id": 1,
  "matiere_id": 1
}
### DELETE /notes/{id}
curl -X DELETE http://localhost:3000/notes/1
###  Fonctionnalités automatiques
L’API calcule automatiquement :
###  Étudiant
Moyenne générale
Mention
Statut (Admis / Rattrapage / Ajourné)
Rang dans la classe
###  Classe
Nombre d’élèves
Moyenne de classe
Note minimale
Note maximale
###  Règles de calcul
Mention
Moyenne	Mention
< 10	Insuffisant
10-12	Passable
12-14	Assez bien
14-16	Bien
≥ 16	Très bien
Statut
Moyenne	Statut
≥ 10	Admis
8-10	Rattrapage
< 8	Ajourné
###  Ordre de test recommandé
/health
/ecoles
/classes
/matieres
/etudiants
/notes
POST étudiant
POST note
PUT étudiant
PUT note
DELETE note
DELETE étudiant