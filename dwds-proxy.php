<?php
header('Content-Type: application/json');
if(isset($_GET['term'])) {
  $term = urlencode($_GET['term']);
  // Ersetze die URL durch den korrekten DWDS-API-Endpunkt
  $url = "https://www.dwds.de/api/definition?term={$term}";
  
  // Mit file_get_contents oder cURL die Daten abrufen
  $result = file_get_contents($url);
  if($result === false) {
    echo json_encode(["error" => "Fehler beim Abruf"]);
  } else {
    echo $result;
  }
} else {
  echo json_encode(["error" => "Kein Begriff übergeben"]);
}
?>
