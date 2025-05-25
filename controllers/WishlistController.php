<?php
require_once './models/FavoritesModel.php';

class FavorisController
{
    public function __construct()
    {
        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Check login
        if (!isset($_SESSION['student'])) {
            header('Location: ../InternMate/dashboard');
            exit;
        }

        // Check if 'role' is set in session
        if (!isset($_SESSION['student']['role'])) {
            echo "Error: 'role' not set in session.";
            var_dump($_SESSION);
            exit;
        }

        // Check access rights for 'student' role
        $this->checkAccess(['student']);
    }

    /**
     * Display the favorites page (only for students)
     */
    public function index()
    {
        require './views/favorites/index.php';
    }

    /**
     * Check if the user has the required role
     * @param array $allowedRoles
     */
    private function checkAccess(array $allowedRoles)
    {
        if (!isset($_SESSION['student']['role']) || !in_array($_SESSION['student']['role'], $allowedRoles)) {
            $this->accessDenied();
        }
    }

    /**
     * Show access denied message and exit
     */
    private function accessDenied()
    {
        echo "Access denied: You do not have permission to access this page.";
        exit;
    }
}
