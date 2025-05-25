<?php
require_once './models/InternshipModel.php';

class InternshipsController {
    public function index() {
        $model = new InternshipModel();
        $internships = $model->getAllInternships();
        require './views/internships/index.php';
    }
}
