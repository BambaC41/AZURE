package main

import (
	"database/sql"
	"encoding/json"
	"fmt"
	"log"
	"net/http"
	"os"
	"strconv"
	"strings"
	"time"

	_ "github.com/go-sql-driver/mysql"
)

type Ecole struct {
	ID        int    `json:"id"`
	Nom       string `json:"nom"`
	Adresse   string `json:"adresse"`
	Ville     string `json:"ville"`
	Telephone string `json:"telephone"`
	Email     string `json:"email"`
	Directeur string `json:"directeur"`
}

type Classe struct {
	ID                  int     `json:"id"`
	Nom                 string  `json:"nom"`
	Niveau              string  `json:"niveau"`
	Filiere             string  `json:"filiere"`
	AnneeScolaire       string  `json:"annee_scolaire"`
	Salle               string  `json:"salle"`
	ProfesseurPrincipal string  `json:"professeur_principal"`
	EcoleID             int     `json:"ecole_id"`
	NombreEleves        int     `json:"nombre_eleves"`
	MoyenneClasse       float64 `json:"moyenne_classe"`
	NoteMin             float64 `json:"note_min"`
	NoteMax             float64 `json:"note_max"`
}

type Etudiant struct {
	ID              int     `json:"id"`
	Nom             string  `json:"nom"`
	Prenom          string  `json:"prenom"`
	Matricule       string  `json:"matricule"`
	Email           string  `json:"email"`
	Telephone       string  `json:"telephone"`
	DateNaissance   string  `json:"date_naissance"`
	Genre           string  `json:"genre"`
	ClasseID        int     `json:"classe_id"`
	MoyenneGenerale float64 `json:"moyenne_generale"`
	Rang            int     `json:"rang"`
	Mention         string  `json:"mention"`
	Statut          string  `json:"statut"`
}

type Matiere struct {
	ID          int     `json:"id"`
	Nom         string  `json:"nom"`
	Coefficient float64 `json:"coefficient"`
	Enseignant  string  `json:"enseignant"`
}

type Note struct {
	ID         int     `json:"id"`
	Valeur     float64 `json:"valeur"`
	TypeNote   string  `json:"type_note"`
	DateNote   string  `json:"date_note"`
	EtudiantID int     `json:"etudiant_id"`
	MatiereID  int     `json:"matiere_id"`
}

var db *sql.DB

func main() {
	host := getEnv("DB_HOST", "localhost")
	port := getEnv("DB_PORT", "3306")
	user := getEnv("DB_USER", "root")
	password := getEnv("DB_PASSWORD", "root")
	dbname := getEnv("DB_NAME", "school")
	log.Println("DB_HOST =", host)
	log.Println("DB_PORT =", port)
	log.Println("DB_USER =", user)
	log.Println("DB_NAME =", dbname)

	dsn := fmt.Sprintf("%s:%s@tcp(%s:%s)/%s?parseTime=true",
		user, password, host, port, dbname,
	)

	var err error

	for i := 0; i < 20; i++ {
		db, err = sql.Open("mysql", dsn)
		if err == nil {
			err = db.Ping()
			if err == nil {
				break
			}
		}
		log.Println("Waiting for MySQL...", err)
		time.Sleep(3 * time.Second)
	}

	if err != nil {
		log.Fatal("Database connectiozn failed:", err)
	}
	defer db.Close()

	recalculateAllStats()

	http.HandleFunc("/health", healthHandler)
	http.HandleFunc("/ecoles", ecolesHandler)
	http.HandleFunc("/classes", classesHandler)
	http.HandleFunc("/matieres", matieresHandler)
	http.HandleFunc("/etudiants", etudiantsHandler)
	http.HandleFunc("/etudiants/", etudiantByIDHandler)
	http.HandleFunc("/notes", notesHandler)
	http.HandleFunc("/notes/", noteByIDHandler)

	log.Println("API running on :3000")
	log.Fatal(http.ListenAndServe(":3000", nil))
}

func getEnv(key, fallback string) string {
	value := os.Getenv(key)
	if value == "" {
		return fallback
	}
	return value
}

func healthHandler(w http.ResponseWriter, r *http.Request) {
	writeJSON(w, http.StatusOK, map[string]string{"status": "ok"})
}

func ecolesHandler(w http.ResponseWriter, r *http.Request) {
	switch r.Method {
	case http.MethodGet:
		rows, err := db.Query(`SELECT id, nom, adresse, ville, telephone, email, directeur FROM ecoles ORDER BY id DESC`)
		if err != nil {
			writeError(w, http.StatusInternalServerError, err.Error())
			return
		}
		defer rows.Close()

		var list []Ecole
		for rows.Next() {
			var e Ecole
			if err := rows.Scan(&e.ID, &e.Nom, &e.Adresse, &e.Ville, &e.Telephone, &e.Email, &e.Directeur); err != nil {
				writeError(w, http.StatusInternalServerError, err.Error())
				return
			}
			list = append(list, e)
		}
		writeJSON(w, http.StatusOK, list)

	case http.MethodPost:
		var e Ecole
		if err := json.NewDecoder(r.Body).Decode(&e); err != nil {
			writeError(w, http.StatusBadRequest, "invalid body")
			return
		}

		res, err := db.Exec(`INSERT INTO ecoles (nom, adresse, ville, telephone, email, directeur) VALUES (?, ?, ?, ?, ?, ?)`,
			e.Nom, e.Adresse, e.Ville, e.Telephone, e.Email, e.Directeur)
		if err != nil {
			writeError(w, http.StatusInternalServerError, err.Error())
			return
		}

		id, _ := res.LastInsertId()
		e.ID = int(id)
		writeJSON(w, http.StatusCreated, e)

	default:
		writeError(w, http.StatusMethodNotAllowed, "method not allowed")
	}
}

func classesHandler(w http.ResponseWriter, r *http.Request) {
	switch r.Method {
	case http.MethodGet:
		rows, err := db.Query(`SELECT id, nom, niveau, filiere, annee_scolaire, salle, professeur_principal, ecole_id, nombre_eleves, moyenne_classe, note_min, note_max FROM classes ORDER BY id DESC`)
		if err != nil {
			writeError(w, http.StatusInternalServerError, err.Error())
			return
		}
		defer rows.Close()

		var list []Classe
		for rows.Next() {
			var c Classe
			if err := rows.Scan(&c.ID, &c.Nom, &c.Niveau, &c.Filiere, &c.AnneeScolaire, &c.Salle, &c.ProfesseurPrincipal, &c.EcoleID, &c.NombreEleves, &c.MoyenneClasse, &c.NoteMin, &c.NoteMax); err != nil {
				writeError(w, http.StatusInternalServerError, err.Error())
				return
			}
			list = append(list, c)
		}
		writeJSON(w, http.StatusOK, list)

	case http.MethodPost:
		var c Classe
		if err := json.NewDecoder(r.Body).Decode(&c); err != nil {
			writeError(w, http.StatusBadRequest, "invalid body")
			return
		}

		res, err := db.Exec(`INSERT INTO classes (nom, niveau, filiere, annee_scolaire, salle, professeur_principal, ecole_id, nombre_eleves, moyenne_classe, note_min, note_max)
			VALUES (?, ?, ?, ?, ?, ?, ?, 0, 0, 0, 0)`,
			c.Nom, c.Niveau, c.Filiere, c.AnneeScolaire, c.Salle, c.ProfesseurPrincipal, c.EcoleID)
		if err != nil {
			writeError(w, http.StatusInternalServerError, err.Error())
			return
		}

		id, _ := res.LastInsertId()
		c.ID = int(id)
		writeJSON(w, http.StatusCreated, c)

	default:
		writeError(w, http.StatusMethodNotAllowed, "method not allowed")
	}
}

func matieresHandler(w http.ResponseWriter, r *http.Request) {
	switch r.Method {
	case http.MethodGet:
		rows, err := db.Query(`SELECT id, nom, coefficient, enseignant FROM matieres ORDER BY id DESC`)
		if err != nil {
			writeError(w, http.StatusInternalServerError, err.Error())
			return
		}
		defer rows.Close()

		var list []Matiere
		for rows.Next() {
			var m Matiere
			if err := rows.Scan(&m.ID, &m.Nom, &m.Coefficient, &m.Enseignant); err != nil {
				writeError(w, http.StatusInternalServerError, err.Error())
				return
			}
			list = append(list, m)
		}
		writeJSON(w, http.StatusOK, list)

	case http.MethodPost:
		var m Matiere
		if err := json.NewDecoder(r.Body).Decode(&m); err != nil {
			writeError(w, http.StatusBadRequest, "invalid body")
			return
		}

		res, err := db.Exec(`INSERT INTO matieres (nom, coefficient, enseignant) VALUES (?, ?, ?)`,
			m.Nom, m.Coefficient, m.Enseignant)
		if err != nil {
			writeError(w, http.StatusInternalServerError, err.Error())
			return
		}

		id, _ := res.LastInsertId()
		m.ID = int(id)
		writeJSON(w, http.StatusCreated, m)

	default:
		writeError(w, http.StatusMethodNotAllowed, "method not allowed")
	}
}

func etudiantsHandler(w http.ResponseWriter, r *http.Request) {
	switch r.Method {
	case http.MethodGet:
		rows, err := db.Query(`SELECT id, nom, prenom, matricule, email, telephone, date_naissance, genre, classe_id, moyenne_generale, rang, mention, statut FROM etudiants ORDER BY classe_id, rang ASC, id ASC`)
		if err != nil {
			writeError(w, http.StatusInternalServerError, err.Error())
			return
		}
		defer rows.Close()

		var list []Etudiant
		for rows.Next() {
			var e Etudiant
			if err := rows.Scan(&e.ID, &e.Nom, &e.Prenom, &e.Matricule, &e.Email, &e.Telephone, &e.DateNaissance, &e.Genre, &e.ClasseID, &e.MoyenneGenerale, &e.Rang, &e.Mention, &e.Statut); err != nil {
				writeError(w, http.StatusInternalServerError, err.Error())
				return
			}
			list = append(list, e)
		}
		writeJSON(w, http.StatusOK, list)

	case http.MethodPost:
		var e Etudiant
		if err := json.NewDecoder(r.Body).Decode(&e); err != nil {
			writeError(w, http.StatusBadRequest, "invalid body")
			return
		}

		res, err := db.Exec(`INSERT INTO etudiants (nom, prenom, matricule, email, telephone, date_naissance, genre, classe_id, moyenne_generale, rang, mention, statut)
			VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0, 0, '', '')`,
			e.Nom, e.Prenom, e.Matricule, e.Email, e.Telephone, e.DateNaissance, e.Genre, e.ClasseID)
		if err != nil {
			writeError(w, http.StatusInternalServerError, err.Error())
			return
		}

		id, _ := res.LastInsertId()
		e.ID = int(id)
		updateClasseStats(e.ClasseID)
		updateRanksForClass(e.ClasseID)

		writeJSON(w, http.StatusCreated, e)

	default:
		writeError(w, http.StatusMethodNotAllowed, "method not allowed")
	}
}

func etudiantByIDHandler(w http.ResponseWriter, r *http.Request) {
	id, err := parseID(r.URL.Path, "/etudiants/")
	if err != nil {
		writeError(w, http.StatusBadRequest, "invalid id")
		return
	}

	switch r.Method {
	case http.MethodGet:
		var e Etudiant
		err := db.QueryRow(`SELECT id, nom, prenom, matricule, email, telephone, date_naissance, genre, classe_id, moyenne_generale, rang, mention, statut FROM etudiants WHERE id = ?`, id).
			Scan(&e.ID, &e.Nom, &e.Prenom, &e.Matricule, &e.Email, &e.Telephone, &e.DateNaissance, &e.Genre, &e.ClasseID, &e.MoyenneGenerale, &e.Rang, &e.Mention, &e.Statut)
		if err != nil {
			writeError(w, http.StatusNotFound, "etudiant not found")
			return
		}
		writeJSON(w, http.StatusOK, e)

	case http.MethodPut:
		var oldClasseID int
		if err := db.QueryRow(`SELECT classe_id FROM etudiants WHERE id = ?`, id).Scan(&oldClasseID); err != nil {
			writeError(w, http.StatusNotFound, "etudiant not found")
			return
		}

		var e Etudiant
		if err := json.NewDecoder(r.Body).Decode(&e); err != nil {
			writeError(w, http.StatusBadRequest, "invalid body")
			return
		}

		_, err := db.Exec(`UPDATE etudiants
			SET nom = ?, prenom = ?, matricule = ?, email = ?, telephone = ?, date_naissance = ?, genre = ?, classe_id = ?
			WHERE id = ?`,
			e.Nom, e.Prenom, e.Matricule, e.Email, e.Telephone, e.DateNaissance, e.Genre, e.ClasseID, id)
		if err != nil {
			writeError(w, http.StatusInternalServerError, err.Error())
			return
		}

		updateClasseStats(oldClasseID)
		updateRanksForClass(oldClasseID)
		updateClasseStats(e.ClasseID)
		updateRanksForClass(e.ClasseID)

		e.ID = id
		reloadStudentComputedFields(&e)
		writeJSON(w, http.StatusOK, e)

	case http.MethodDelete:
		var classeID int
		if err := db.QueryRow(`SELECT classe_id FROM etudiants WHERE id = ?`, id).Scan(&classeID); err != nil {
			writeError(w, http.StatusNotFound, "etudiant not found")
			return
		}

		_, _ = db.Exec(`DELETE FROM notes WHERE etudiant_id = ?`, id)
		if _, err := db.Exec(`DELETE FROM etudiants WHERE id = ?`, id); err != nil {
			writeError(w, http.StatusInternalServerError, err.Error())
			return
		}

		updateClasseStats(classeID)
		updateRanksForClass(classeID)
		writeJSON(w, http.StatusOK, map[string]bool{"deleted": true})

	default:
		writeError(w, http.StatusMethodNotAllowed, "method not allowed")
	}
}

func notesHandler(w http.ResponseWriter, r *http.Request) {
	switch r.Method {
	case http.MethodGet:
		rows, err := db.Query(`SELECT id, valeur, type_note, date_note, etudiant_id, matiere_id FROM notes ORDER BY id DESC`)
		if err != nil {
			writeError(w, http.StatusInternalServerError, err.Error())
			return
		}
		defer rows.Close()

		var list []Note
		for rows.Next() {
			var n Note
			if err := rows.Scan(&n.ID, &n.Valeur, &n.TypeNote, &n.DateNote, &n.EtudiantID, &n.MatiereID); err != nil {
				writeError(w, http.StatusInternalServerError, err.Error())
				return
			}
			list = append(list, n)
		}
		writeJSON(w, http.StatusOK, list)

	case http.MethodPost:
		var n Note
		if err := json.NewDecoder(r.Body).Decode(&n); err != nil {
			writeError(w, http.StatusBadRequest, "invalid body")
			return
		}

		res, err := db.Exec(`INSERT INTO notes (valeur, type_note, date_note, etudiant_id, matiere_id) VALUES (?, ?, ?, ?, ?)`,
			n.Valeur, n.TypeNote, n.DateNote, n.EtudiantID, n.MatiereID)
		if err != nil {
			writeError(w, http.StatusInternalServerError, err.Error())
			return
		}

		id, _ := res.LastInsertId()
		n.ID = int(id)
		recalculateStudentAndClass(n.EtudiantID)

		writeJSON(w, http.StatusCreated, n)

	default:
		writeError(w, http.StatusMethodNotAllowed, "method not allowed")
	}
}

func noteByIDHandler(w http.ResponseWriter, r *http.Request) {
	id, err := parseID(r.URL.Path, "/notes/")
	if err != nil {
		writeError(w, http.StatusBadRequest, "invalid id")
		return
	}

	switch r.Method {
	case http.MethodGet:
		var n Note
		err := db.QueryRow(`SELECT id, valeur, type_note, date_note, etudiant_id, matiere_id FROM notes WHERE id = ?`, id).
			Scan(&n.ID, &n.Valeur, &n.TypeNote, &n.DateNote, &n.EtudiantID, &n.MatiereID)
		if err != nil {
			writeError(w, http.StatusNotFound, "note not found")
			return
		}
		writeJSON(w, http.StatusOK, n)

	case http.MethodPut:
		var oldStudentID int
		if err := db.QueryRow(`SELECT etudiant_id FROM notes WHERE id = ?`, id).Scan(&oldStudentID); err != nil {
			writeError(w, http.StatusNotFound, "note not found")
			return
		}

		var n Note
		if err := json.NewDecoder(r.Body).Decode(&n); err != nil {
			writeError(w, http.StatusBadRequest, "invalid body")
			return
		}

		_, err := db.Exec(`UPDATE notes
			SET valeur = ?, type_note = ?, date_note = ?, etudiant_id = ?, matiere_id = ?
			WHERE id = ?`,
			n.Valeur, n.TypeNote, n.DateNote, n.EtudiantID, n.MatiereID, id)
		if err != nil {
			writeError(w, http.StatusInternalServerError, err.Error())
			return
		}

		n.ID = id
		recalculateStudentAndClass(oldStudentID)
		if oldStudentID != n.EtudiantID {
			recalculateStudentAndClass(n.EtudiantID)
		}
		writeJSON(w, http.StatusOK, n)

	case http.MethodDelete:
		var studentID int
		if err := db.QueryRow(`SELECT etudiant_id FROM notes WHERE id = ?`, id).Scan(&studentID); err != nil {
			writeError(w, http.StatusNotFound, "note not found")
			return
		}

		if _, err := db.Exec(`DELETE FROM notes WHERE id = ?`, id); err != nil {
			writeError(w, http.StatusInternalServerError, err.Error())
			return
		}

		recalculateStudentAndClass(studentID)
		writeJSON(w, http.StatusOK, map[string]bool{"deleted": true})

	default:
		writeError(w, http.StatusMethodNotAllowed, "method not allowed")
	}
}

func recalculateStudentAndClass(studentID int) {
	updateStudentStats(studentID)

	var classeID int
	if err := db.QueryRow(`SELECT classe_id FROM etudiants WHERE id = ?`, studentID).Scan(&classeID); err == nil {
		updateClasseStats(classeID)
		updateRanksForClass(classeID)
	}
}

func recalculateAllStats() {
	rows, err := db.Query(`SELECT id FROM etudiants`)
	if err != nil {
		return
	}
	defer rows.Close()

	var ids []int
	for rows.Next() {
		var id int
		if rows.Scan(&id) == nil {
			ids = append(ids, id)
		}
	}

	for _, id := range ids {
		updateStudentStats(id)
	}

	classRows, err := db.Query(`SELECT id FROM classes`)
	if err != nil {
		return
	}
	defer classRows.Close()

	var classIDs []int
	for classRows.Next() {
		var id int
		if classRows.Scan(&id) == nil {
			classIDs = append(classIDs, id)
		}
	}

	for _, id := range classIDs {
		updateClasseStats(id)
		updateRanksForClass(id)
	}
}

func updateStudentStats(studentID int) {
	var avg sql.NullFloat64
	err := db.QueryRow(`SELECT AVG(valeur) FROM notes WHERE etudiant_id = ?`, studentID).Scan(&avg)
	if err != nil {
		return
	}

	moyenne := 0.0
	if avg.Valid {
		moyenne = avg.Float64
	}

	mention := getMention(moyenne)
	statut := getStatut(moyenne)

	_, _ = db.Exec(`UPDATE etudiants SET moyenne_generale = ?, mention = ?, statut = ? WHERE id = ?`,
		moyenne, mention, statut, studentID)
}

func updateClasseStats(classeID int) {
	var count int
	var avg, min, max sql.NullFloat64

	err := db.QueryRow(`SELECT COUNT(*), AVG(moyenne_generale), MIN(moyenne_generale), MAX(moyenne_generale) FROM etudiants WHERE classe_id = ?`, classeID).
		Scan(&count, &avg, &min, &max)
	if err != nil {
		return
	}

	moyenne := 0.0
	noteMin := 0.0
	noteMax := 0.0

	if avg.Valid {
		moyenne = avg.Float64
	}
	if min.Valid {
		noteMin = min.Float64
	}
	if max.Valid {
		noteMax = max.Float64
	}

	_, _ = db.Exec(`UPDATE classes SET nombre_eleves = ?, moyenne_classe = ?, note_min = ?, note_max = ? WHERE id = ?`,
		count, moyenne, noteMin, noteMax, classeID)
}

func updateRanksForClass(classeID int) {
	rows, err := db.Query(`SELECT id FROM etudiants WHERE classe_id = ? ORDER BY moyenne_generale DESC, nom ASC, prenom ASC`, classeID)
	if err != nil {
		return
	}
	defer rows.Close()

	rang := 1
	for rows.Next() {
		var studentID int
		if rows.Scan(&studentID) == nil {
			_, _ = db.Exec(`UPDATE etudiants SET rang = ? WHERE id = ?`, rang, studentID)
			rang++
		}
	}
}

func reloadStudentComputedFields(e *Etudiant) {
	_ = db.QueryRow(`SELECT moyenne_generale, rang, mention, statut FROM etudiants WHERE id = ?`, e.ID).
		Scan(&e.MoyenneGenerale, &e.Rang, &e.Mention, &e.Statut)
}

func getMention(m float64) string {
	switch {
	case m < 10:
		return "Insuffisant"
	case m < 12:
		return "Passable"
	case m < 14:
		return "Assez bien"
	case m < 16:
		return "Bien"
	default:
		return "Très bien"
	}
}

func getStatut(m float64) string {
	switch {
	case m >= 10:
		return "Admis"
	case m >= 8:
		return "Rattrapage"
	default:
		return "Ajourné"
	}
}

func parseID(path, prefix string) (int, error) {
	idStr := strings.TrimPrefix(path, prefix)
	return strconv.Atoi(idStr)
}

func writeJSON(w http.ResponseWriter, status int, data any) {
	w.Header().Set("Content-Type", "application/json")
	w.WriteHeader(status)
	_ = json.NewEncoder(w).Encode(data)
}

func writeError(w http.ResponseWriter, status int, message string) {
	writeJSON(w, status, map[string]string{"error": message})
}
