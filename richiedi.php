<?php
try {
    $misure = "";
    // Viene restituito il file richiesto, se non contiene caratteri "/" (per evitare tentativi di accesso ad altre cartelle)
    if (file_exists($_REQUEST["nomeFile"]) && (strpos($_REQUEST["nomeFile"], '/') !== false) == false) {
        $misureFile = fopen($_REQUEST["nomeFile"], "r");
        while (!feof($misureFile)) {
            $linea = fgets($misureFile);
            if (empty($linea) == false)
                $misure .= $linea;
        }
    } else {
        echo "<p>File non specificato o inesistente.</p>";
        return;
    }
} catch (Exception $e) {

}
echo $misure;