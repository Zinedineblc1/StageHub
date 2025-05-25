<?php

require_once './controllers/InternshipsController.php';
require_once './controllers/EntreprisesController.php';
require_once './controllers/FavorisController.php';
require_once './controllers/DashboardController.php';
require_once './controllers/PanelController.php';

// Get the URI
$uri = $_GET["uri"];

// Routing
if ($uri === 'internships') {
    $controller = new InternshipsController();
    $controller->index();
} elseif ($uri === 'entreprises') {
    $controller = new EntreprisesController();
    $controller->index();
} elseif ($uri === 'favoris') {
    $controller = new FavorisController();
    $controller->index();
} elseif ($uri === 'dashboard') {
    $controller = new DashboardController();
    $controller->index();
} elseif ($uri === 'admin') {
    $controller = new PanelController();
    $controller->showFirmsPage(); // Default admin landing page
} elseif ($uri === 'admin/entreprises') {
    $controller = new EntreprisesController();
    $controller->index();
} elseif ($uri === 'admin/applicants') {
    $controller = new PanelController();
    $controller->showApplicantsPage();
} elseif ($uri === 'admin/internships') {
    $controller = new PanelController();
    $controller->showInternshipsPage();
} elseif ($uri === 'admin/mentors') {
    $controller = new PanelController();
    $controller->showMentorsPage();
} elseif ($uri === 'admin/candidatures') {
    $controller = new PanelController();
    $controller->showCandidaturesPage();
} elseif ($uri === 'login') {
    require './views/students/login.php';
} elseif ($uri === 'views/students/err_login.php') {
    require './views/students/err_login.php';
} elseif ($uri === 'views/students/logout.php') {
    require './views/students/logout.php';
} else {
    echo "404 - Page not found";
}
