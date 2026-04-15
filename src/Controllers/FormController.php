<?php
namespace Evasystem\Controllers;

use Evasystem\Services\AIAnalyzer;

class FormController {
    public function handleRequest() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'denumire' => $_POST['denumire'] ?? '',
                'idno'     => $_POST['idno'] ?? '',
                'website'  => $_POST['website'] ?? '',
                'domeniu'  => $_POST['domeniu'] ?? '',
            ];

            $ai = new AIAnalyzer();
            $report = $ai->generateReport($data);
            echo $report;

        } else {
            include __DIR__ . '/../../Templates/form.html';
        }
    }
}

