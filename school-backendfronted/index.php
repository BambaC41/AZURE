<?php
$apiBase = getenv('API_BASE_URL') ?: 'http://localhost:3000';
$page = $_GET['page'] ?? 'dashboard';
$message = null;
$messageType = 'success';

function h($value): string {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function api_request(string $baseUrl, string $method, string $endpoint, ?array $payload = null): array {
    $url = rtrim($baseUrl, '/') . $endpoint;
    $headers = [
        'Accept: application/json',
        'Content-Type: application/json',
    ];

    $options = [
        'http' => [
            'method' => $method,
            'header' => implode("\r\n", $headers),
            'timeout' => 20,
            'ignore_errors' => true,
        ]
    ];

    if ($payload !== null) {
        $options['http']['content'] = json_encode($payload, JSON_UNESCAPED_UNICODE);
    }

    $context = stream_context_create($options);
    $response = @file_get_contents($url, false, $context);

    $statusCode = 0;
    if (isset($http_response_header[0]) && preg_match('/\s(\d{3})\s/', $http_response_header[0], $matches)) {
        $statusCode = (int)$matches[1];
    }

    $data = null;
    if ($response !== false && $response !== '') {
        $decoded = json_decode($response, true);
        $data = is_array($decoded) ? $decoded : ['raw' => $response];
    }

    return [
        'status' => $statusCode,
        'ok' => $statusCode >= 200 && $statusCode < 300,
        'data' => $data,
    ];
}

function api_get(string $baseUrl, string $endpoint): array {
    $result = api_request($baseUrl, 'GET', $endpoint);
    return $result['ok'] && is_array($result['data']) ? $result['data'] : [];
}

function index_by_id(array $items): array {
    $indexed = [];
    foreach ($items as $item) {
        if (isset($item['id'])) {
            $indexed[$item['id']] = $item;
        }
    }
    return $indexed;
}

function badge_class_for_status(string $status): string {
    return match (mb_strtolower($status)) {
        'admis' => 'badge badge-success',
        'rattrapage' => 'badge badge-warning',
        'ajourné', 'ajourne' => 'badge badge-danger',
        default => 'badge badge-neutral',
    };
}

function badge_class_for_mention(string $mention): string {
    return match (mb_strtolower($mention)) {
        'très bien', 'tres bien' => 'badge badge-success',
        'bien' => 'badge badge-primary',
        'assez bien' => 'badge badge-info',
        'passable' => 'badge badge-warning',
        'insuffisant' => 'badge badge-danger',
        default => 'badge badge-neutral',
    };
}

function redirect_to(string $page, string $extra = ''): void {
    header('Location: ?page=' . urlencode($page) . $extra);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    switch ($action) {
        case 'create_ecole':
            $payload = [
                'nom' => trim($_POST['nom'] ?? ''),
                'adresse' => trim($_POST['adresse'] ?? ''),
                'ville' => trim($_POST['ville'] ?? ''),
                'telephone' => trim($_POST['telephone'] ?? ''),
                'email' => trim($_POST['email'] ?? ''),
                'directeur' => trim($_POST['directeur'] ?? ''),
            ];
            $result = api_request($apiBase, 'POST', '/ecoles', $payload);
            redirect_to('ecoles', '&msg=' . urlencode($result['ok'] ? 'École ajoutée avec succès.' : 'Échec lors de l’ajout de l’école.') . '&type=' . ($result['ok'] ? 'success' : 'error'));

        case 'create_classe':
            $payload = [
                'nom' => trim($_POST['nom'] ?? ''),
                'niveau' => trim($_POST['niveau'] ?? ''),
                'filiere' => trim($_POST['filiere'] ?? ''),
                'annee_scolaire' => trim($_POST['annee_scolaire'] ?? ''),
                'salle' => trim($_POST['salle'] ?? ''),
                'professeur_principal' => trim($_POST['professeur_principal'] ?? ''),
                'ecole_id' => (int)($_POST['ecole_id'] ?? 0),
            ];
            $result = api_request($apiBase, 'POST', '/classes', $payload);
            redirect_to('classes', '&msg=' . urlencode($result['ok'] ? 'Classe ajoutée avec succès.' : 'Échec lors de l’ajout de la classe.') . '&type=' . ($result['ok'] ? 'success' : 'error'));

        case 'create_matiere':
            $payload = [
                'nom' => trim($_POST['nom'] ?? ''),
                'coefficient' => (float)($_POST['coefficient'] ?? 1),
                'enseignant' => trim($_POST['enseignant'] ?? ''),
            ];
            $result = api_request($apiBase, 'POST', '/matieres', $payload);
            redirect_to('matieres', '&msg=' . urlencode($result['ok'] ? 'Matière ajoutée avec succès.' : 'Échec lors de l’ajout de la matière.') . '&type=' . ($result['ok'] ? 'success' : 'error'));

        case 'create_etudiant':
            $payload = [
                'nom' => trim($_POST['nom'] ?? ''),
                'prenom' => trim($_POST['prenom'] ?? ''),
                'matricule' => trim($_POST['matricule'] ?? ''),
                'email' => trim($_POST['email'] ?? ''),
                'telephone' => trim($_POST['telephone'] ?? ''),
                'date_naissance' => trim($_POST['date_naissance'] ?? ''),
                'genre' => trim($_POST['genre'] ?? ''),
                'classe_id' => (int)($_POST['classe_id'] ?? 0),
            ];
            $result = api_request($apiBase, 'POST', '/etudiants', $payload);
            redirect_to('etudiants', '&msg=' . urlencode($result['ok'] ? 'Étudiant ajouté avec succès.' : 'Échec lors de l’ajout de l’étudiant.') . '&type=' . ($result['ok'] ? 'success' : 'error'));

        case 'update_etudiant':
            $id = (int)($_POST['id'] ?? 0);
            $payload = [
                'nom' => trim($_POST['nom'] ?? ''),
                'prenom' => trim($_POST['prenom'] ?? ''),
                'matricule' => trim($_POST['matricule'] ?? ''),
                'email' => trim($_POST['email'] ?? ''),
                'telephone' => trim($_POST['telephone'] ?? ''),
                'date_naissance' => trim($_POST['date_naissance'] ?? ''),
                'genre' => trim($_POST['genre'] ?? ''),
                'classe_id' => (int)($_POST['classe_id'] ?? 0),
            ];
            $result = api_request($apiBase, 'PUT', '/etudiants/' . $id, $payload);
            redirect_to('etudiants', '&msg=' . urlencode($result['ok'] ? 'Étudiant modifié.' : 'Échec de modification de l’étudiant.') . '&type=' . ($result['ok'] ? 'success' : 'error'));

        case 'delete_etudiant':
            $id = (int)($_POST['id'] ?? 0);
            $result = api_request($apiBase, 'DELETE', '/etudiants/' . $id);
            redirect_to('etudiants', '&msg=' . urlencode($result['ok'] ? 'Étudiant supprimé.' : 'Échec de suppression de l’étudiant.') . '&type=' . ($result['ok'] ? 'success' : 'error'));

        case 'create_note':
            $payload = [
                'valeur' => (float)($_POST['valeur'] ?? 0),
                'type_note' => trim($_POST['type_note'] ?? ''),
                'date_note' => trim($_POST['date_note'] ?? ''),
                'etudiant_id' => (int)($_POST['etudiant_id'] ?? 0),
                'matiere_id' => (int)($_POST['matiere_id'] ?? 0),
            ];
            $result = api_request($apiBase, 'POST', '/notes', $payload);
            redirect_to('notes', '&msg=' . urlencode($result['ok'] ? 'Note ajoutée avec succès.' : 'Échec lors de l’ajout de la note.') . '&type=' . ($result['ok'] ? 'success' : 'error'));

        case 'update_note':
            $id = (int)($_POST['id'] ?? 0);
            $payload = [
                'valeur' => (float)($_POST['valeur'] ?? 0),
                'type_note' => trim($_POST['type_note'] ?? ''),
                'date_note' => trim($_POST['date_note'] ?? ''),
                'etudiant_id' => (int)($_POST['etudiant_id'] ?? 0),
                'matiere_id' => (int)($_POST['matiere_id'] ?? 0),
            ];
            $result = api_request($apiBase, 'PUT', '/notes/' . $id, $payload);
            redirect_to('notes', '&msg=' . urlencode($result['ok'] ? 'Note modifiée.' : 'Échec de modification de la note.') . '&type=' . ($result['ok'] ? 'success' : 'error'));

        case 'delete_note':
            $id = (int)($_POST['id'] ?? 0);
            $result = api_request($apiBase, 'DELETE', '/notes/' . $id);
            redirect_to('notes', '&msg=' . urlencode($result['ok'] ? 'Note supprimée.' : 'Échec de suppression de la note.') . '&type=' . ($result['ok'] ? 'success' : 'error'));
    }
}

if (isset($_GET['msg'])) {
    $message = $_GET['msg'];
    $messageType = $_GET['type'] ?? 'success';
}

$health = api_get($apiBase, '/health');
$ecoles = api_get($apiBase, '/ecoles');
$classes = api_get($apiBase, '/classes');
$matieres = api_get($apiBase, '/matieres');
$etudiants = api_get($apiBase, '/etudiants');
$notes = api_get($apiBase, '/notes');

$ecolesById = index_by_id($ecoles);
$classesById = index_by_id($classes);
$matieresById = index_by_id($matieres);
$etudiantsById = index_by_id($etudiants);

$totalEcoles = count($ecoles);
$totalClasses = count($classes);
$totalMatieres = count($matieres);
$totalEtudiants = count($etudiants);
$totalNotes = count($notes);

$sumMoyennes = 0.0;
$admisCount = 0;
$bestStudent = null;
$topClass = null;
foreach ($etudiants as $etudiant) {
    $moyenne = (float)($etudiant['moyenne_generale'] ?? 0);
    $sumMoyennes += $moyenne;
    if (($etudiant['statut'] ?? '') === 'Admis') {
        $admisCount++;
    }
    if ($bestStudent === null || $moyenne > (float)$bestStudent['moyenne_generale']) {
        $bestStudent = $etudiant;
    }
}
foreach ($classes as $classe) {
    if ($topClass === null || (float)$classe['moyenne_classe'] > (float)$topClass['moyenne_classe']) {
        $topClass = $classe;
    }
}
$globalAverage = $totalEtudiants ? $sumMoyennes / $totalEtudiants : 0;
$admissionRate = $totalEtudiants ? ($admisCount / $totalEtudiants) * 100 : 0;

$classFilter = $page === 'etudiants' ? ($_GET['classe_id'] ?? '') : '';
$statusFilter = $page === 'etudiants' ? ($_GET['statut'] ?? '') : '';
$search = $page === 'etudiants' ? trim($_GET['search'] ?? '') : '';
$selectedEditStudent = isset($_GET['edit_student']) ? (int)$_GET['edit_student'] : 0;
$selectedEditNote = isset($_GET['edit_note']) ? (int)$_GET['edit_note'] : 0;

$filteredStudents = array_values(array_filter($etudiants, function (array $etudiant) use ($classFilter, $statusFilter, $search) {
    if ($classFilter !== '' && (string)($etudiant['classe_id'] ?? '') !== $classFilter) {
        return false;
    }
    if ($statusFilter !== '' && ($etudiant['statut'] ?? '') !== $statusFilter) {
        return false;
    }
    if ($search !== '') {
        $haystack = mb_strtolower(($etudiant['nom'] ?? '') . ' ' . ($etudiant['prenom'] ?? '') . ' ' . ($etudiant['matricule'] ?? ''));
        if (!str_contains($haystack, mb_strtolower($search))) {
            return false;
        }
    }
    return true;
}));

$notesByStudent = [];
foreach ($notes as $note) {
    $studentId = $note['etudiant_id'] ?? null;
    if ($studentId !== null) {
        $notesByStudent[$studentId][] = $note;
    }
}

$editStudent = $selectedEditStudent && isset($etudiantsById[$selectedEditStudent]) ? $etudiantsById[$selectedEditStudent] : null;
$editNote = $selectedEditNote && isset(index_by_id($notes)[$selectedEditNote]) ? index_by_id($notes)[$selectedEditNote] : null;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>School Management</title>
    <style>
        :root {
            --bg: #07111f;
            --bg-2: #0e1b2e;
            --panel: rgba(9, 18, 34, 0.9);
            --panel-2: rgba(18, 29, 49, 0.92);
            --border: rgba(148, 163, 184, 0.18);
            --text: #e5eefc;
            --muted: #93a6c6;
            --primary: #5da8ff;
            --primary-2: #7c92ff;
            --success: #22c55e;
            --warning: #f59e0b;
            --danger: #ef4444;
            --info: #06b6d4;
            --shadow: 0 30px 80px rgba(0, 0, 0, 0.35);
            --radius: 22px;
        }
        * { box-sizing: border-box; }
        html { scroll-behavior: smooth; }
        body {
            margin: 0;
            font-family: Inter, Segoe UI, Arial, sans-serif;
            color: var(--text);
            background:
                radial-gradient(circle at 10% 10%, rgba(93,168,255,0.18), transparent 22%),
                radial-gradient(circle at 90% 0%, rgba(124,146,255,0.18), transparent 20%),
                linear-gradient(180deg, #050c16 0%, #08111f 36%, #0b1527 100%);
        }
        a { color: inherit; text-decoration: none; }
        .app {
            display: grid;
            grid-template-columns: 280px 1fr;
            min-height: 100vh;
        }
        .sidebar {
            position: sticky;
            top: 0;
            height: 100vh;
            padding: 24px 18px;
            background: rgba(5, 12, 24, 0.9);
            border-right: 1px solid var(--border);
            backdrop-filter: blur(14px);
        }
        .brand {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 14px 16px;
            background: linear-gradient(135deg, rgba(93,168,255,0.16), rgba(124,146,255,0.18));
            border: 1px solid var(--border);
            border-radius: 20px;
            margin-bottom: 22px;
        }
        .brand-badge {
            width: 48px;
            height: 48px;
            border-radius: 16px;
            display: grid;
            place-items: center;
            font-weight: 800;
            background: linear-gradient(135deg, var(--primary), var(--primary-2));
            color: white;
            box-shadow: var(--shadow);
        }
        .brand h1 {
            margin: 0;
            font-size: 18px;
        }
        .brand p {
            margin: 4px 0 0;
            color: var(--muted);
            font-size: 12px;
        }
        .nav {
            display: grid;
            gap: 10px;
        }
        .nav a {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 14px 16px;
            border-radius: 16px;
            background: rgba(255,255,255,0.03);
            border: 1px solid transparent;
            color: var(--muted);
            transition: 0.2s ease;
        }
        .nav a:hover, .nav a.active {
            background: linear-gradient(135deg, rgba(93,168,255,0.14), rgba(124,146,255,0.14));
            color: var(--text);
            border-color: var(--border);
            transform: translateX(2px);
        }
        .nav small {
            background: rgba(255,255,255,0.08);
            border: 1px solid var(--border);
            padding: 4px 8px;
            border-radius: 999px;
            color: var(--text);
        }
        .sidebar-note {
            margin-top: 18px;
            padding: 16px;
            border-radius: 18px;
            background: rgba(255,255,255,0.04);
            border: 1px solid var(--border);
            color: var(--muted);
            font-size: 13px;
            line-height: 1.55;
        }
        .main {
            padding: 24px;
        }
        .hero {
            display: grid;
            grid-template-columns: 1.7fr 1fr;
            gap: 18px;
            margin-bottom: 22px;
        }
        .card {
            background: linear-gradient(180deg, var(--panel), var(--panel-2));
            border: 1px solid var(--border);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            backdrop-filter: blur(12px);
        }
        .hero-card {
            padding: 26px;
        }
        .hero-card h2 {
            margin: 0 0 12px;
            font-size: clamp(28px, 4vw, 38px);
            line-height: 1.05;
        }
        .hero-card p {
            margin: 0;
            color: var(--muted);
            line-height: 1.6;
            max-width: 820px;
        }
        .hero-status {
            padding: 24px;
            display: grid;
            gap: 14px;
        }
        .live {
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 700;
        }
        .dot {
            width: 12px;
            height: 12px;
            border-radius: 999px;
            background: var(--success);
            box-shadow: 0 0 0 6px rgba(34,197,94,0.12);
        }
        .stats {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 16px;
            margin-bottom: 22px;
        }
        .stat {
            padding: 18px;
        }
        .stat .label { color: var(--muted); font-size: 13px; margin-bottom: 10px; }
        .stat .value { font-size: 30px; font-weight: 800; }
        .stat .sub { color: var(--muted); font-size: 13px; margin-top: 8px; }
        .content-grid {
            display: grid;
            grid-template-columns: 1.4fr 1fr;
            gap: 20px;
        }
        .section {
            padding: 22px;
            margin-bottom: 20px;
        }
        .section h3 {
            margin: 0 0 16px;
            font-size: 22px;
        }
        .muted { color: var(--muted); }
        .alert {
            padding: 14px 16px;
            border-radius: 16px;
            margin-bottom: 18px;
            border: 1px solid var(--border);
            font-weight: 600;
        }
        .alert-success { background: rgba(34,197,94,0.12); color: #d1fadf; }
        .alert-error { background: rgba(239,68,68,0.12); color: #ffd3d3; }
        .toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            margin-bottom: 16px;
            flex-wrap: wrap;
        }
        .filters {
            display: grid;
            grid-template-columns: 1.3fr 1fr 1fr auto;
            gap: 12px;
            width: 100%;
        }
        input, select, textarea, button {
            width: 100%;
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 13px 14px;
            background: rgba(255,255,255,0.05);
            color: var(--text);
            outline: none;
            font: inherit;
        }
        textarea { min-height: 110px; resize: vertical; }
        input::placeholder, textarea::placeholder { color: #8295b8; }
        button {
            cursor: pointer;
            font-weight: 700;
            background: linear-gradient(135deg, var(--primary), var(--primary-2));
            border: none;
            box-shadow: var(--shadow);
        }
        .btn-secondary { background: rgba(255,255,255,0.08); border: 1px solid var(--border); }
        .btn-danger { background: linear-gradient(135deg, #ef4444, #dc2626); }
        .table-wrap { overflow-x: auto; }
        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 820px;
        }
        th, td {
            text-align: left;
            padding: 14px 12px;
            border-bottom: 1px solid rgba(255,255,255,0.08);
            vertical-align: top;
        }
        th {
            color: #b8c6e2;
            font-size: 13px;
            font-weight: 700;
        }
        tr:hover td { background: rgba(255,255,255,0.03); }
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 12px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 800;
            border: 1px solid transparent;
        }
        .badge-success { background: rgba(34,197,94,0.18); color: #e6ffef; border-color: rgba(34,197,94,0.38); }
        .badge-warning { background: rgba(245,158,11,0.18); color: #fff2d8; border-color: rgba(245,158,11,0.38); }
        .badge-danger { background: rgba(239,68,68,0.18); color: #ffe0e0; border-color: rgba(239,68,68,0.38); }
        .badge-primary { background: rgba(93,168,255,0.18); color: #dcebff; border-color: rgba(93,168,255,0.36); }
        .badge-info { background: rgba(6,182,212,0.18); color: #dffbff; border-color: rgba(6,182,212,0.36); }
        .badge-neutral { background: rgba(148,163,184,0.18); color: #ebf2ff; border-color: rgba(148,163,184,0.32); }
        .kpi-list, .entity-list {
            display: grid;
            gap: 12px;
        }
        .entity-item {
            padding: 14px 16px;
            border-radius: 18px;
            background: rgba(255,255,255,0.04);
            border: 1px solid var(--border);
            display: flex;
            align-items: start;
            justify-content: space-between;
            gap: 16px;
        }
        .entity-item strong { display: block; margin-bottom: 4px; }
        .grid-2, .grid-3, .grid-4 {
            display: grid;
            gap: 14px;
        }
        .grid-2 { grid-template-columns: repeat(2, 1fr); }
        .grid-3 { grid-template-columns: repeat(3, 1fr); }
        .grid-4 { grid-template-columns: repeat(4, 1fr); }
        .panel-title {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            align-items: center;
            margin-bottom: 14px;
        }
        .panel-title p { margin: 0; color: var(--muted); }
        .pill-note {
            margin-top: 12px;
            padding: 12px 14px;
            border-radius: 14px;
            background: rgba(255,255,255,0.04);
            border: 1px dashed var(--border);
            color: var(--muted);
            font-size: 13px;
        }
        .actions {
            display: flex;
            gap: 8px;
            align-items: center;
            flex-wrap: wrap;
        }
        .actions form { margin: 0; }
        .actions a, .actions button {
            width: auto;
            padding: 9px 12px;
            border-radius: 12px;
            font-size: 13px;
        }
        .footer-space { height: 12px; }
        @media (max-width: 1200px) {
            .stats { grid-template-columns: repeat(2, 1fr); }
            .content-grid, .hero { grid-template-columns: 1fr; }
            .grid-4 { grid-template-columns: repeat(2, 1fr); }
        }
        @media (max-width: 900px) {
            .app { grid-template-columns: 1fr; }
            .sidebar { position: static; height: auto; }
            .filters, .grid-2, .grid-3, .grid-4 { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
<div class="app">
    <aside class="sidebar">
        <div class="brand">
            <div class="brand-badge">SM</div>
            <div>
                <h1>School Management</h1>
                <p>Administration scolaire complète</p>
            </div>
        </div>

        <nav class="nav">
            <a class="<?= $page === 'dashboard' ? 'active' : '' ?>" href="?page=dashboard"><span>Dashboard</span><small><?= $totalEtudiants ?></small></a>
            <a class="<?= $page === 'ecoles' ? 'active' : '' ?>" href="?page=ecoles"><span>Écoles</span><small><?= $totalEcoles ?></small></a>
            <a class="<?= $page === 'classes' ? 'active' : '' ?>" href="?page=classes"><span>Classes</span><small><?= $totalClasses ?></small></a>
            <a class="<?= $page === 'matieres' ? 'active' : '' ?>" href="?page=matieres"><span>Matières</span><small><?= $totalMatieres ?></small></a>
            <a class="<?= $page === 'etudiants' ? 'active' : '' ?>" href="?page=etudiants"><span>Étudiants</span><small><?= $totalEtudiants ?></small></a>
            <a class="<?= $page === 'notes' ? 'active' : '' ?>" href="?page=notes"><span>Notes</span><small><?= $totalNotes ?></small></a>
        </nav>

        <div class="sidebar-note">
            <div style="display:flex;align-items:center;gap:10px;font-weight:700;margin-bottom:10px;">
                <span class="dot"></span>
                <span>API <?= !empty($health['status']) ? 'connectée' : 'indisponible' ?></span>
            </div>
            Endpoint : <?= h($apiBase) ?><br><br>
            Le site utilise toutes les routes disponibles de l’API actuelle pour ajouter, afficher, modifier et supprimer les étudiants et les notes, ainsi que pour créer écoles, classes et matières.
        </div>
    </aside>

    <main class="main">
        <?php if ($message): ?>
            <div class="alert <?= $messageType === 'error' ? 'alert-error' : 'alert-success' ?>"><?= h($message) ?></div>
        <?php endif; ?>

        <section class="hero">
            <div class="card hero-card">
                <h2>Plateforme de gestion académique</h2>
                <p>
                    ajout de à la ligne 647 pour tester le ci/cd dans le raport Supervise les établissements, organise les classes, suit les étudiants, gère les matières et pilote les notes depuis une interface unique.
                    Toutes les statistiques visibles ici sont alimentées par l’API : moyenne générale, rang, mention, statut, moyenne de classe et indicateurs globaux.
                </p>
            </div>
            <div class="card hero-status">
                <div class="live"><span class="dot"></span><span>Service opérationnel</span></div>
                <div class="entity-item">
                    <div>
                        <strong>Meilleur étudiant</strong>
                        <span class="muted"><?= $bestStudent ? h(($bestStudent['prenom'] ?? '') . ' ' . ($bestStudent['nom'] ?? '')) : '—' ?></span>
                    </div>
                    <span class="badge badge-success"><?= $bestStudent ? number_format((float)$bestStudent['moyenne_generale'], 2, ',', ' ') : '—' ?></span>
                </div>
                <div class="entity-item">
                    <div>
                        <strong>Classe la plus performante</strong>
                        <span class="muted"><?= $topClass ? h($topClass['nom']) : '—' ?></span>
                    </div>
                    <span class="badge badge-primary"><?= $topClass ? number_format((float)$topClass['moyenne_classe'], 2, ',', ' ') : '—' ?></span>
                </div>
            </div>
        </section>

        <section class="stats">
            <div class="card stat"><div class="label">Écoles</div><div class="value"><?= $totalEcoles ?></div><div class="sub">établissements enregistrés</div></div>
            <div class="card stat"><div class="label">Classes</div><div class="value"><?= $totalClasses ?></div><div class="sub">groupes académiques</div></div>
            <div class="card stat"><div class="label">Étudiants</div><div class="value"><?= $totalEtudiants ?></div><div class="sub">profils actifs</div></div>
            <div class="card stat"><div class="label">Moyenne globale</div><div class="value"><?= number_format($globalAverage, 2, ',', ' ') ?></div><div class="sub">sur l’ensemble des étudiants</div></div>
            <div class="card stat"><div class="label">Taux d’admission</div><div class="value"><?= number_format($admissionRate, 1, ',', ' ') ?>%</div><div class="sub">étudiants admis</div></div>
        </section>

        <?php if ($page === 'dashboard'): ?>
            <section class="content-grid">
                <div>
                    <div class="card section">
                        <div class="panel-title">
                            <div>
                                <h3>Top étudiants</h3>
                                <p>Classement par moyenne générale et rang.</p>
                            </div>
                        </div>
                        <div class="table-wrap">
                            <table>
                                <thead>
                                <tr>
                                    <th>Étudiant</th>
                                    <th>Classe</th>
                                    <th>Moyenne</th>
                                    <th>Rang</th>
                                    <th>Mention</th>
                                    <th>Statut</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php $topStudents = $etudiants; usort($topStudents, fn($a, $b) => ($b['moyenne_generale'] <=> $a['moyenne_generale']) ?: ($a['rang'] <=> $b['rang'])); ?>
                                <?php foreach (array_slice($topStudents, 0, 8) as $etudiant): ?>
                                    <tr>
                                        <td><strong><?= h(($etudiant['prenom'] ?? '') . ' ' . ($etudiant['nom'] ?? '')) ?></strong><br><span class="muted"><?= h($etudiant['matricule'] ?? '') ?></span></td>
                                        <td><?= h($classesById[$etudiant['classe_id']]['nom'] ?? '—') ?></td>
                                        <td><?= number_format((float)$etudiant['moyenne_generale'], 2, ',', ' ') ?></td>
                                        <td>#<?= h((string)$etudiant['rang']) ?></td>
                                        <td><span class="<?= badge_class_for_mention((string)($etudiant['mention'] ?? '')) ?>"><?= h($etudiant['mention'] ?? '—') ?></span></td>
                                        <td><span class="<?= badge_class_for_status((string)($etudiant['statut'] ?? '')) ?>"><?= h($etudiant['statut'] ?? '—') ?></span></td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="card section">
                        <div class="panel-title">
                            <div>
                                <h3>Dernières notes</h3>
                                <p>Vue rapide des évaluations les plus récentes.</p>
                            </div>
                        </div>
                        <div class="table-wrap">
                            <table>
                                <thead>
                                <tr>
                                    <th>Étudiant</th>
                                    <th>Matière</th>
                                    <th>Type</th>
                                    <th>Date</th>
                                    <th>Valeur</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach (array_slice(array_reverse($notes), 0, 10) as $note): ?>
                                    <tr>
                                        <td><?= h(($etudiantsById[$note['etudiant_id']]['prenom'] ?? '') . ' ' . ($etudiantsById[$note['etudiant_id']]['nom'] ?? '')) ?></td>
                                        <td><?= h($matieresById[$note['matiere_id']]['nom'] ?? '—') ?></td>
                                        <td><span class="badge badge-info"><?= h($note['type_note'] ?? '') ?></span></td>
                                        <td><?= h($note['date_note'] ?? '') ?></td>
                                        <td><span class="badge badge-primary"><?= number_format((float)$note['valeur'], 2, ',', ' ') ?></span></td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div>
                    <div class="card section">
                        <h3>Classes</h3>
                        <div class="entity-list">
                            <?php foreach ($classes as $classe): ?>
                                <div class="entity-item">
                                    <div>
                                        <strong><?= h($classe['nom']) ?></strong>
                                        <div class="muted"><?= h($classe['niveau']) ?> · <?= h($classe['filiere']) ?></div>
                                        <div class="muted"><?= h($ecolesById[$classe['ecole_id']]['nom'] ?? 'École inconnue') ?></div>
                                    </div>
                                    <div style="text-align:right;">
                                        <div class="badge badge-primary"><?= number_format((float)$classe['moyenne_classe'], 2, ',', ' ') ?></div>
                                        <div class="muted" style="margin-top:8px;"><?= h((string)$classe['nombre_eleves']) ?> élèves</div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="card section">
                        <h3>Matières</h3>
                        <div class="entity-list">
                            <?php foreach ($matieres as $matiere): ?>
                                <div class="entity-item">
                                    <div>
                                        <strong><?= h($matiere['nom']) ?></strong>
                                        <div class="muted"><?= h($matiere['enseignant'] ?? '—') ?></div>
                                    </div>
                                    <span class="badge badge-info">Coef <?= number_format((float)$matiere['coefficient'], 0, ',', ' ') ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </section>
        <?php endif; ?>

        <?php if ($page === 'ecoles'): ?>
            <div class="content-grid">
                <div class="card section">
                    <div class="panel-title">
                        <div>
                            <h3>Liste des écoles</h3>
                            <p>Consultation de tous les établissements enregistrés.</p>
                        </div>
                    </div>
                    <div class="table-wrap">
                        <table>
                            <thead>
                            <tr>
                                <th>Nom</th>
                                <th>Ville</th>
                                <th>Adresse</th>
                                <th>Téléphone</th>
                                <th>Email</th>
                                <th>Directeur</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($ecoles as $ecole): ?>
                                <tr>
                                    <td><strong><?= h($ecole['nom']) ?></strong></td>
                                    <td><?= h($ecole['ville']) ?></td>
                                    <td><?= h($ecole['adresse']) ?></td>
                                    <td><?= h($ecole['telephone']) ?></td>
                                    <td><?= h($ecole['email']) ?></td>
                                    <td><?= h($ecole['directeur']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="pill-note">L’API actuelle permet l’ajout et la consultation des écoles. La modification et la suppression nécessitent des endpoints supplémentaires côté API.</div>
                </div>
                <div class="card section">
                    <h3>Ajouter une école</h3>
                    <form method="post" class="grid-2">
                        <input type="hidden" name="action" value="create_ecole">
                        <input name="nom" placeholder="Nom de l’école" required>
                        <input name="ville" placeholder="Ville" required>
                        <input name="adresse" placeholder="Adresse">
                        <input name="telephone" placeholder="Téléphone">
                        <input type="email" name="email" placeholder="Email">
                        <input name="directeur" placeholder="Directeur">
                        <div style="grid-column:1 / -1;"><button type="submit">Ajouter l’école</button></div>
                    </form>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($page === 'classes'): ?>
            <div class="content-grid">
                <div class="card section">
                    <div class="panel-title">
                        <div>
                            <h3>Liste des classes</h3>
                            <p>Suivi des classes avec leurs statistiques globales.</p>
                        </div>
                    </div>
                    <div class="table-wrap">
                        <table>
                            <thead>
                            <tr>
                                <th>Classe</th>
                                <th>École</th>
                                <th>Niveau</th>
                                <th>Filière</th>
                                <th>Prof principal</th>
                                <th>Élèves</th>
                                <th>Moyenne</th>
                                <th>Min</th>
                                <th>Max</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($classes as $classe): ?>
                                <tr>
                                    <td><strong><?= h($classe['nom']) ?></strong><br><span class="muted">Salle <?= h($classe['salle']) ?> · <?= h($classe['annee_scolaire']) ?></span></td>
                                    <td><?= h($ecolesById[$classe['ecole_id']]['nom'] ?? '—') ?></td>
                                    <td><?= h($classe['niveau']) ?></td>
                                    <td><?= h($classe['filiere']) ?></td>
                                    <td><?= h($classe['professeur_principal']) ?></td>
                                    <td><?= h((string)$classe['nombre_eleves']) ?></td>
                                    <td><?= number_format((float)$classe['moyenne_classe'], 2, ',', ' ') ?></td>
                                    <td><?= number_format((float)$classe['note_min'], 2, ',', ' ') ?></td>
                                    <td><?= number_format((float)$classe['note_max'], 2, ',', ' ') ?></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="pill-note">L’API actuelle permet l’ajout et la consultation des classes. La suppression et la modification nécessitent des routes supplémentaires côté backend.</div>
                </div>
                <div class="card section">
                    <h3>Ajouter une classe</h3>
                    <form method="post" class="grid-2">
                        <input type="hidden" name="action" value="create_classe">
                        <input name="nom" placeholder="Nom de la classe" required>
                        <input name="niveau" placeholder="Niveau" required>
                        <input name="filiere" placeholder="Filière" required>
                        <input name="annee_scolaire" placeholder="Année scolaire" required>
                        <input name="salle" placeholder="Salle">
                        <input name="professeur_principal" placeholder="Professeur principal">
                        <select name="ecole_id" required style="grid-column:1 / -1;">
                            <option value="">Choisir une école</option>
                            <?php foreach ($ecoles as $ecole): ?>
                                <option value="<?= h((string)$ecole['id']) ?>"><?= h($ecole['nom']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div style="grid-column:1 / -1;"><button type="submit">Ajouter la classe</button></div>
                    </form>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($page === 'matieres'): ?>
            <div class="content-grid">
                <div class="card section">
                    <div class="panel-title">
                        <div>
                            <h3>Liste des matières</h3>
                            <p>Catalogue des matières et de leurs enseignants.</p>
                        </div>
                    </div>
                    <div class="table-wrap">
                        <table>
                            <thead>
                            <tr>
                                <th>Matière</th>
                                <th>Coefficient</th>
                                <th>Enseignant</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($matieres as $matiere): ?>
                                <tr>
                                    <td><strong><?= h($matiere['nom']) ?></strong></td>
                                    <td><span class="badge badge-info">Coef <?= number_format((float)$matiere['coefficient'], 0, ',', ' ') ?></span></td>
                                    <td><?= h($matiere['enseignant']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="pill-note">L’API actuelle permet l’ajout et la consultation des matières. Pour les modifier ou les supprimer, il faudra compléter le backend.</div>
                </div>
                <div class="card section">
                    <h3>Ajouter une matière</h3>
                    <form method="post" class="grid-2">
                        <input type="hidden" name="action" value="create_matiere">
                        <input name="nom" placeholder="Nom de la matière" required>
                        <input type="number" step="0.01" min="1" name="coefficient" placeholder="Coefficient" required>
                        <input name="enseignant" placeholder="Enseignant" style="grid-column:1 / -1;">
                        <div style="grid-column:1 / -1;"><button type="submit">Ajouter la matière</button></div>
                    </form>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($page === 'etudiants'): ?>
            <div class="content-grid">
                <div>
                    <div class="card section">
                        <div class="panel-title">
                            <div>
                                <h3>Gestion des étudiants</h3>
                                <p class="muted" style="margin:-6px 0 14px;">Étudiants affichés : <?= count($filteredStudents) ?> / <?= count($etudiants) ?></p>
                                <p>Créer, modifier, filtrer et supprimer les profils étudiants.</p>
                            </div>
                        </div>
                        <form method="get" class="filters" style="margin-bottom:16px;">
                            <input type="hidden" name="page" value="etudiants">
                            <input type="text" name="search" placeholder="Rechercher par nom, prénom ou matricule" value="<?= h($search) ?>">
                            <select name="classe_id">
                                <option value="">Toutes les classes</option>
                                <?php foreach ($classes as $classe): ?>
                                    <option value="<?= h((string)$classe['id']) ?>" <?= $classFilter === (string)$classe['id'] ? 'selected' : '' ?>><?= h($classe['nom']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <select name="statut">
                                <option value="">Tous les statuts</option>
                                <?php foreach (['Admis', 'Rattrapage', 'Ajourné'] as $status): ?>
                                    <option value="<?= h($status) ?>" <?= $statusFilter === $status ? 'selected' : '' ?>><?= h($status) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <div style="display:flex;gap:10px;">
                                <button type="submit">Filtrer</button>
                                <a class="btn-secondary" style="display:inline-flex;align-items:center;justify-content:center;" href="?page=etudiants">Réinitialiser</a>
                            </div>
                        </form>
                        <div class="table-wrap">
                            <table>
                                <thead>
                                <tr>
                                    <th>Étudiant</th>
                                    <th>Classe</th>
                                    <th>Moyenne</th>
                                    <th>Rang</th>
                                    <th>Mention</th>
                                    <th>Statut</th>
                                    <th>Actions</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php if (empty($filteredStudents)): ?>
                                    <tr>
                                        <td colspan="7">Aucun étudiant trouvé avec les filtres actuels.</td>
                                    </tr>
                                <?php endif; ?>
                                <?php foreach ($filteredStudents as $etudiant): ?>
                                    <tr>
                                        <td>
                                            <strong><?= h(($etudiant['prenom'] ?? '') . ' ' . ($etudiant['nom'] ?? '')) ?></strong><br>
                                            <span class="muted"><?= h($etudiant['matricule']) ?> · <?= h($etudiant['email']) ?></span>
                                        </td>
                                        <td><?= h($classesById[$etudiant['classe_id']]['nom'] ?? '—') ?></td>
                                        <td><?= number_format((float)$etudiant['moyenne_generale'], 2, ',', ' ') ?></td>
                                        <td>#<?= h((string)$etudiant['rang']) ?></td>
                                        <td><span class="<?= badge_class_for_mention((string)($etudiant['mention'] ?? '')) ?>"><?= h($etudiant['mention'] ?? '—') ?></span></td>
                                        <td><span class="<?= badge_class_for_status((string)($etudiant['statut'] ?? '')) ?>"><?= h($etudiant['statut'] ?? '—') ?></span></td>
                                        <td>
                                            <div class="actions">
                                                <a class="btn-secondary" href="?page=etudiants&edit_student=<?= h((string)$etudiant['id']) ?>">Modifier</a>
                                                <form method="post" onsubmit="return confirm('Supprimer cet étudiant ?');">
                                                    <input type="hidden" name="action" value="delete_etudiant">
                                                    <input type="hidden" name="id" value="<?= h((string)$etudiant['id']) ?>">
                                                    <button type="submit" class="btn-danger">Supprimer</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div>
                    <div class="card section">
                        <h3><?= $editStudent ? 'Modifier un étudiant' : 'Ajouter un étudiant' ?></h3>
                        <form method="post" class="grid-2">
                            <input type="hidden" name="action" value="<?= $editStudent ? 'update_etudiant' : 'create_etudiant' ?>">
                            <?php if ($editStudent): ?><input type="hidden" name="id" value="<?= h((string)$editStudent['id']) ?>"><?php endif; ?>
                            <input name="nom" placeholder="Nom" required value="<?= h($editStudent['nom'] ?? '') ?>">
                            <input name="prenom" placeholder="Prénom" required value="<?= h($editStudent['prenom'] ?? '') ?>">
                            <input name="matricule" placeholder="Matricule" required value="<?= h($editStudent['matricule'] ?? '') ?>">
                            <input type="email" name="email" placeholder="Email" value="<?= h($editStudent['email'] ?? '') ?>">
                            <input name="telephone" placeholder="Téléphone" value="<?= h($editStudent['telephone'] ?? '') ?>">
                            <input name="date_naissance" placeholder="Date de naissance" value="<?= h($editStudent['date_naissance'] ?? '') ?>">
                            <select name="genre" required>
                                <option value="">Genre</option>
                                <?php foreach (['M', 'F'] as $genre): ?>
                                    <option value="<?= h($genre) ?>" <?= (($editStudent['genre'] ?? '') === $genre) ? 'selected' : '' ?>><?= h($genre) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <select name="classe_id" required>
                                <option value="">Choisir une classe</option>
                                <?php foreach ($classes as $classe): ?>
                                    <option value="<?= h((string)$classe['id']) ?>" <?= ((string)($editStudent['classe_id'] ?? '') === (string)$classe['id']) ? 'selected' : '' ?>><?= h($classe['nom']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <div style="grid-column:1 / -1; display:flex; gap:10px;">
                                <button type="submit"><?= $editStudent ? 'Enregistrer les modifications' : 'Ajouter l’étudiant' ?></button>
                                <?php if ($editStudent): ?><a class="btn-secondary" style="display:inline-flex;align-items:center;justify-content:center;" href="?page=etudiants">Annuler</a><?php endif; ?>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($page === 'notes'): ?>
            <div class="content-grid">
                <div>
                    <div class="card section">
                        <div class="panel-title">
                            <div>
                                <h3>Gestion des notes</h3>
                                <p>Ajouter, modifier et supprimer les notes de toutes les matières.</p>
                            </div>
                        </div>
                        <div class="table-wrap">
                            <table>
                                <thead>
                                <tr>
                                    <th>Étudiant</th>
                                    <th>Matière</th>
                                    <th>Type</th>
                                    <th>Date</th>
                                    <th>Valeur</th>
                                    <th>Actions</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($notes as $note): ?>
                                    <tr>
                                        <td><strong><?= h(($etudiantsById[$note['etudiant_id']]['prenom'] ?? '') . ' ' . ($etudiantsById[$note['etudiant_id']]['nom'] ?? '')) ?></strong><br><span class="muted"><?= h($etudiantsById[$note['etudiant_id']]['matricule'] ?? '') ?></span></td>
                                        <td><?= h($matieresById[$note['matiere_id']]['nom'] ?? '—') ?></td>
                                        <td><span class="badge badge-info"><?= h($note['type_note']) ?></span></td>
                                        <td><?= h($note['date_note']) ?></td>
                                        <td><span class="badge badge-primary"><?= number_format((float)$note['valeur'], 2, ',', ' ') ?></span></td>
                                        <td>
                                            <div class="actions">
                                                <a class="btn-secondary" href="?page=notes&edit_note=<?= h((string)$note['id']) ?>">Modifier</a>
                                                <form method="post" onsubmit="return confirm('Supprimer cette note ?');">
                                                    <input type="hidden" name="action" value="delete_note">
                                                    <input type="hidden" name="id" value="<?= h((string)$note['id']) ?>">
                                                    <button type="submit" class="btn-danger">Supprimer</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div>
                    <div class="card section">
                        <h3><?= $editNote ? 'Modifier une note' : 'Ajouter une note' ?></h3>
                        <form method="post" class="grid-2">
                            <input type="hidden" name="action" value="<?= $editNote ? 'update_note' : 'create_note' ?>">
                            <?php if ($editNote): ?><input type="hidden" name="id" value="<?= h((string)$editNote['id']) ?>"><?php endif; ?>
                            <select name="etudiant_id" required style="grid-column:1 / -1;">
                                <option value="">Choisir un étudiant</option>
                                <?php foreach ($etudiants as $etudiant): ?>
                                    <option value="<?= h((string)$etudiant['id']) ?>" <?= ((string)($editNote['etudiant_id'] ?? '') === (string)$etudiant['id']) ? 'selected' : '' ?>><?= h(($etudiant['prenom'] ?? '') . ' ' . ($etudiant['nom'] ?? '') . ' - ' . ($etudiant['matricule'] ?? '')) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <select name="matiere_id" required style="grid-column:1 / -1;">
                                <option value="">Choisir une matière</option>
                                <?php foreach ($matieres as $matiere): ?>
                                    <option value="<?= h((string)$matiere['id']) ?>" <?= ((string)($editNote['matiere_id'] ?? '') === (string)$matiere['id']) ? 'selected' : '' ?>><?= h($matiere['nom']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <input type="number" step="0.01" name="valeur" placeholder="Valeur" required value="<?= h($editNote['valeur'] ?? '') ?>">
                            <input name="type_note" placeholder="Type de note" required value="<?= h($editNote['type_note'] ?? '') ?>">
                            <input name="date_note" placeholder="Date" style="grid-column:1 / -1;" value="<?= h($editNote['date_note'] ?? '') ?>">
                            <div style="grid-column:1 / -1; display:flex; gap:10px;">
                                <button type="submit"><?= $editNote ? 'Enregistrer les modifications' : 'Ajouter la note' ?></button>
                                <?php if ($editNote): ?><a class="btn-secondary" style="display:inline-flex;align-items:center;justify-content:center;" href="?page=notes">Annuler</a><?php endif; ?>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <div class="footer-space"></div>
    </main>
</div>
</body>
</html>
