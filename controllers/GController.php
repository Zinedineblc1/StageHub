<?php

class PanelController
{
    public function __construct()
    {
        // Ensure session is started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Redirect to dashboard if not logged in
        if (!isset($_SESSION['student'])) {
            header('Location: ../dashboard');
            exit;
        }

        // Validate user role in session
        if (!isset($_SESSION['student']['role'])) {
            echo "Session error: User role not set.";
            var_dump($_SESSION);
            exit;
        }
    }

    /**
     * Display the firms management page (mentors/admins)
     */
    public function showEntreprisesPage()
    {
        $this->checkPanelAccess(['mentor', 'admin']);
        require './views/admin/adminEntreprises.php';
    }

    /**
     * Display the applicants management page (mentors/admins)
     */
    public function showApplicantsPage()
    {
        $this->checkPanelAccess(['mentor', 'admin']);
        require './views/admin/adminApplicants.php';
    }

    /**
     * Display the internships management page (mentors/admins)
     */
    public function showInternshipsPage()
    {
        $this->checkPanelAccess(['mentor', 'admin']);
        require './views/admin/adminInternships.php';
    }

    /**
     * Display the mentors management page (admins only)
     */
    public function showMentorsPage()
    {
        $this->checkPanelAccess(['admin']);
        require './views/admin/adminMentors.php';
    }

    /**
     * Display the applications management page (mentors/admins)
     */
    public function showApplicationsPage()
    {
        $this->checkPanelAccess(['mentor', 'admin']);
        require './views/admin/adminApplications.php';
    }

    /**
     * Display the favorites management page (all roles)
     */
    public function showFavoritesPage()
    {
        $this->checkPanelAccess(['mentor', 'admin', 'student']);
        require './views/admin/adminFavorites.php';
    }

    /**
     * Display the evaluations management page (admins only)
     */
    public function showEvaluationsPage()
    {
        $this->checkPanelAccess(['admin']);
        require './views/admin/adminEvaluations.php';
    }

    /**
     * Display the candidatures management page (mentors/admins)
     */
    public function showCandidaturesPage()
    {
        $this->checkPanelAccess(['mentor', 'admin']);
        require './views/admin/adminCandidatures.php';
    }

    /**
     * Check if the user has the required role for the panel
     * @param array $allowedRoles
     */
    private function checkPanelAccess(array $allowedRoles)
    {
        if (!isset($_SESSION['student']['role']) || !in_array($_SESSION['student']['role'], $allowedRoles)) {
            $this->panelAccessDenied();
        }
    }

    /**
     * Show access denied message and exit
     */
    private function panelAccessDenied()
    {
        echo "Access denied: You do not have permission to access this panel page.";
        exit;
    }
}
