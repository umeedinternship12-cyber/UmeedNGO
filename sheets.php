<?php
// ── Google Sheets Configuration ───────────────────────────────────────────────
// After deploying google-apps-script.gs as a Web App, fill in both values below.
//
// SHEETS_SCRIPT_URL : the "Web App URL" shown after deployment
// SPREADSHEET_ID    : the long ID in your sheet's browser URL
//                     e.g. https://docs.google.com/spreadsheets/d/THIS_PART/edit
//
// Also share the sheet as "Anyone with the link can view" so the admin
// dashboard can read the CSV export.
// ─────────────────────────────────────────────────────────────────────────────
define('SHEETS_SCRIPT_URL', 'https://script.google.com/macros/s/AKfycbwl6knEUIXGyKNmTbMXWktHM8BCajCsD2Auqb4bxSCdT9vCpGRykFAC9F3HMl6CfvH8uw/exec');
define('SPREADSHEET_ID',    '1pkpKn7xInrV_EinCZuzBpqWBr9orlFRkggAJeVoKPeU');

function postToSheets(array $payload): bool {
    $ch = curl_init(SHEETS_SCRIPT_URL);
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode($payload),
        CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT        => 10,
        CURLOPT_SSL_VERIFYPEER => true,
    ]);
    $body  = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);

    if ($error) return false;
    $result = json_decode($body, true);
    return !empty($result['ok']);
}

function fetchSheetCsv(string $sheetName): array {
    $url = 'https://docs.google.com/spreadsheets/d/' . urlencode(SPREADSHEET_ID)
         . '/gviz/tq?tqx=out:csv&sheet=' . urlencode($sheetName);

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT        => 10,
        CURLOPT_SSL_VERIFYPEER => true,
    ]);
    $csv   = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);

    if ($error || !$csv) return [];

    $rows = [];
    foreach (explode("\n", trim($csv)) as $line) {
        $line = trim($line);
        if ($line !== '') {
            $rows[] = str_getcsv($line);
        }
    }
    return $rows;
}

function csvToAssoc(array $rows): array {
    if (count($rows) < 2) return [];
    $headers = array_shift($rows);
    $result  = [];
    foreach ($rows as $row) {
        $row      = array_pad($row, count($headers), '');
        $result[] = array_combine($headers, $row);
    }
    return array_reverse($result); // newest first
}
