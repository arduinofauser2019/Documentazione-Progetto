<?php
// Richiesta di inserimento nella richiesta (GET o POST) di uno o due Laboratori validi per la visualizzazione delle misurazioni
$LAB1 = "LS";
$LAB2 = "";
if (empty($_GET["lab1"]) == false) {
    $labUtente = $_GET["lab1"];
    // Validazione della richiesta
    if ($labUtente == "LS" || $labUtente == "LM" || $labUtente == "LI" || $labUtente == "LE" || $labUtente == "MB" || $labUtente == "N3") {
        $LAB1 = $labUtente;
    } else {
        echo "<p>Laboratorio 1 indicato non valido. Riprova.</p>";
        return;
    }
} else {
    echo "<p>Indicare almeno un laboratorio valido. Riprova.</p>";
    return;
}
if (empty($_GET["lab2"]) == false) {
    $labUtente = $_GET["lab2"];
    if (($labUtente == "LS" || $labUtente == "LM" || $labUtente == "LI" || $labUtente == "LE" || $labUtente == "MB" || $labUtente == "N3") && $labUtente != $LAB1) {
        $LAB2 = $labUtente;
    } else {
        echo "<p>Laboratorio 2 indicato non valido. Riprova.</p>";
        return;
    }
} else
    $LAB2 = false;
?>
<!-- Componente di indicizzazione -->
<!DOCTYPE html>
<html lang="it">
    <head>
        <meta charset="UTF-8">
        <title>Progetto in Arduino - ITT G. Fauser - OpenDay 18 gennaio 2020</title>
        <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
        <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
        <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
        <link rel="manifest" href="/site.webmanifest">
        <link rel="mask-icon" href="/safari-pinned-tab.svg" color="#5bbad5">
        <meta name="msapplication-TileColor" content="#ffc40d">
        <meta name="theme-color" content="#ffffff">
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    </head>
    <body>
        <h3 class="row align-items-center justify-content-center" style="padding-top: 40px; padding-bottom: 40px;">
            Scegli il Laboratorio di cui visualizzare le misurazioni:
        </h3>
        <!-- Generazione dinamica della tabella con le immagini rappresentanti i laboratori indicati -->
        <table border="0" class="row align-items-center justify-content-center">
            <tr>
                <td>
                    <a href="misurazioni.php?lab=<?php echo $LAB1 ?>">
                        <img src="<?php echo $LAB1 ?>.jpg" alt="<?php echo $LAB1 ?>" height="300" width="300"/>
                    </a>
                    <?php
                    if ($LAB2 != false) {
                        echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
                    }
                    ?>
                </td>
                <?php
                if ($LAB2 != false) {
                    echo "<td>";
                    echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
                    echo "<a href=\"misurazioni.php?lab=" . $LAB2 . "\">";
                    echo "<img src=\"" . $LAB2 . ".jpg\" alt=\"" . $LAB2 . "\" height=\"300\" width=\"300\"/>";
                    echo "</a>";
                    echo "</td>";
                }
                ?>
            </tr>
        </table>
        <!-- Immagine rappresentante il meccanismo di funzionamento -->
        <h3 style="display: flex; justify-content: center">
            Schema del meccanismo di funzionamento:
        </h3>
        <figure style="display: flex; justify-content: center;">
            <img src="schema.png" alt="Schema del meccanismo di funzionamento" height="50%" width="50%"/>
        </figure>
        <!-- Link alla documentazione -->
        <div style="display: flex; justify-content: center; padding-top: 50px; padding-bottom: 50px;">
            <a href="https://github.com/arduinofauser2019/Documentazione-Progetto">Documentazione del progetto</a>
        </div>
    </body>
</html>