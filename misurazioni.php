<?php
// Richiesta di inserimento nella richiesta (GET o POST) di uno o due Laboratori validi per la visualizzazione delle misurazioni
$LABORATORIO = "LS";
if (empty($_GET["lab"]) == false) {
    $labUtente = $_GET["lab"];
    // Validazione della richiesta
    if ($labUtente == "LS" || $labUtente == "LM" || $labUtente == "LI" || $labUtente == "LE" || $labUtente == "MB" || $labUtente == "N3") {
        $LABORATORIO = $labUtente;
    } else {
        echo "<p>Laboratorio indicato non valido. Riprova.</p>";
        return;
    }
}
try {
    if (file_exists("misure" . $LABORATORIO . ".txt")) {
        $misureFile = fopen("misure" . $LABORATORIO . ".txt", "r");
        $misure = "";
        while (!feof($misureFile)) {
            $linea = fgets($misureFile);
            if (empty($linea) == false)
                $misure .= $linea;
        }
    } else {
        echo "<p>Laboratorio non abilitato o misure inesistenti. Riprova.</p>";
        return;
    }
} catch (Exception $e) {

}
?>
<!-- Sostituzione con la variabile "LABORATORIO" nelle zone in cui viene indicato -->
<!-- Laboratorio <?php echo $LABORATORIO ?> -->
<!DOCTYPE html>
<html lang="it">
    <head>
        <meta charset="UTF-8">
        <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate"/>
        <meta http-equiv="Pragma" content="no-cache"/>
        <meta http-equiv="Expires" content="0"/>
        <title>Laboratorio <?php echo $LABORATORIO ?> - Progetto in Arduino - ITT G. Fauser - OpenDay 18 gennaio
            2020</title>
        <link rel="apple-touch-icon" sizes="180x180" href="apple-touch-icon.png">
        <link rel="icon" type="image/png" sizes="32x32" href="favicon-32x32.png">
        <link rel="icon" type="image/png" sizes="16x16" href="favicon-16x16.png">
        <link rel="manifest" href="site.webmanifest">
        <link rel="mask-icon" href="safari-pinned-tab.svg" color="#5bbad5">
        <meta name="msapplication-TileColor" content="#ffc40d">
        <meta name="theme-color" content="#ffffff">
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
        <style type="text/css">
            /* Classi di definizione dello stile della pagina */
            .table {
                width: auto !important;
                background: #FFFFFF;
            }

            table.tabella th {
                background-color: #5289D6;
                color: white;
            }

            body {
                background: white;
            }
        </style>
        <script type="text/javascript">
            // Funzione non più adottata: utilizzata per ricaricare la pagina "manualmente" ogni 10 secondi.
            /* setTimeout(function () {
                location.reload();
            }, 10000); */

            function avviaAggiornamento() {
                var placeholderMisure = document.getElementById("placeholder").innerText.replace("<br/>", "");
                // Se PHP non è riuscito a leggere il file, se ne occupa Javascript
                if (placeholderMisure.length == 0) {
                    document.getElementById("sottotitolo").innerHTML = "Aggiornamento in corso...";
                    const URL = "misure<?=$LABORATORIO?>.txt";
                    var filehttp = new XMLHttpRequest();
                    // Richiesta "GET" del file
                    filehttp.open("GET", URL, true);
                    filehttp.send();
                    filehttp.onreadystatechange = function () {
                        // Se ricevo Status Code 200
                        if (this.readyState == 4 && this.status == 200) {
                            Main(tornaArrayMisure(filehttp.responseText));
                            document.getElementById("sottotitolo").innerHTML = "";
                        }
                    };
                } else {
                    // Altrimenti, passo direttamente alla funzione "Main" i parametri fondamentali per iniziare
                    Main(tornaArrayMisure(placeholderMisure));
                    document.getElementById("sottotitolo").innerHTML = "";
                }
            }

            // Funzione che restituisce l'array completo delle misurazioni (stringhe), divise per "new line"
            function tornaArrayMisure(mis) {
                return mis.split('\n');
            }

            // Funzione che restituisce la singola riga, effettuando lo split del file CSV
            function tornaSingolaRiga(mis, pos) {
                return mis[pos].split(';');
            }

            // Funzione che restituisce l'ultima riga, effettuando lo split del file CSV
            function tornaUltimaMisura(arrayMis, maxCiclo) {
                return tornaSingolaRiga(arrayMis, maxCiclo - 1);
            }

            function Main(arrayMisure) {
                var tabellaStr = "";
                var ultimaMisura;
                // Generazione della tabella nella variabile "tabellaStr"
                tabellaStr += '<table class="table table-bordered table-sm shadow tabella">';
                tabellaStr += '<thead><tr><th></th><th scope="col">&nbsp;&nbsp;Umidit&agrave;&nbsp;&nbsp;</th><th scope="col">&nbsp;&nbsp;Temperatura&nbsp;&nbsp;</th><th scope="col">&nbsp;&nbsp;Indice di calore&nbsp;&nbsp;</th><th scope="col">&nbsp;&nbsp;Luminosit&agrave;&nbsp;&nbsp;</th></tr></thead><tbody>';
                for (var i = arrayMisure.length - 2; i >= 0; i--) {
                    var riga = tornaSingolaRiga(arrayMisure, i);
                    tabellaStr += '<tr class="text-center">';
                    tabellaStr += "<th>&nbsp;" + (arrayMisure.length - 2 - i + 1) + "&nbsp;</th>";
                    tabellaStr += "<td>" + riga[0] + "&nbsp;&percnt;</td>";
                    tabellaStr += "<td>" + riga[1] + "&nbsp;&deg;C</td>";
                    tabellaStr += "<td>" + riga[2] + "&nbsp;&deg;C</td>";
                    tabellaStr += "<td>" + riga[3] + "&nbsp;&percnt;</td>";
                    tabellaStr += "</tr>";
                }
                tabellaStr += "</tbody></table>";
                // Scrittura nella pagina della tabella
                document.getElementById("tabella").innerHTML = tabellaStr;
                // Analisi dell'ultima Umidità e Luminosità
                ultimaMisura = tornaUltimaMisura(arrayMisure, arrayMisure.length - 1);
                var ultimaLuminosita = parseInt(ultimaMisura.slice(3, 4));
                ultimaMisura = parseInt(ultimaMisura.slice(0, 1));
                // In base al Range della misurazione, si adotta colore, frase e immagine adeguata
                if (ultimaMisura >= 40 && ultimaMisura <= 65) {
                    document.getElementById("immagine").innerHTML = '<img src="good1.png" height="400" width="400">';
                    document.getElementById("condizione").innerHTML = '<p> Umidit&#224; ideale! </p>';
                    document.getElementById("condizione").style.color = "#339966";
                } else if (ultimaMisura > 65 && ultimaMisura <= 70) {
                    document.getElementById("immagine").innerHTML = '<img src="good2.png" height="400" width="400">';
                    document.getElementById("condizione").innerHTML = '<p> L&#8217;umidit&#224; si sta alzando! </p>';
                    document.getElementById("condizione").style.color = "#FF8855";
                } else if (ultimaMisura > 70 && ultimaMisura <= 80) {
                    document.getElementById("immagine").innerHTML = '<img src="good3.png" height="400" width="400">';
                    document.getElementById("condizione").innerHTML = '<p> Sta iniziando a fare caldo! </p>';
                    document.getElementById("condizione").style.color = "#FF8855";
                } else if (ultimaMisura > 80) {
                    document.getElementById("immagine").innerHTML = '<img src="umido.png" height="400" width="400">';
                    document.getElementById("condizione").innerHTML = '<p> Troppo Umido! </p>';
                    document.getElementById("condizione").style.color = "#FF7070";
                } else if (ultimaMisura < 40) {
                    document.getElementById("immagine").innerHTML = '<img src="secco.png" height="400" width="400">';
                    document.getElementById("condizione").innerHTML = '<p> Troppo Secco! </p>';
                    document.getElementById("condizione").style.color = "#5289D6";
                }
                // Scrittura dell'ultima misurazione
                document.getElementById("ultimaUmidita").innerHTML = "<p> L'ultimo valore di umidit&#224; misurato &#232; pari al " + ultimaMisura + " %.</p>";
                document.getElementById("ultimaLuminosita").innerHTML = "<p> L'ultimo valore di lumonosit&#224; misurato &#232; pari al " + ultimaLuminosita + " %.</p>";
                // Variazione del background del documento in funzione della luminosità
                if (ultimaLuminosita >= 55) {
                    document.body.style.backgroundColor = "white";
                    document.getElementById("scritte").style.color = "#000000";
                    document.getElementById("ultimaUmidita").style.color = "#000000";
                    document.getElementById("ultimaLuminosita").style.color = "#000000";
                } else if (ultimaLuminosita >= 30) {
                    document.body.style.background = "#A7A69D";
                    document.getElementById("scritte").style.color = "#FFFFFF";
                    document.getElementById("ultimaUmidita").style.color = "#FFFFFF";
                    document.getElementById("ultimaLuminosita").style.color = "#FFFFFF";
                } else {
                    document.body.style.backgroundColor = "#6A6C6E";
                    document.getElementById("scritte").style.color = "#FFFFFF";
                    document.getElementById("ultimaUmidita").style.color = "#FFFFFF";
                    document.getElementById("ultimaLuminosita").style.color = "#FFFFFF";
                }
            }
        </script>
    </head>
    <body onload="avviaAggiornamento()">
        <div id="placeholder" style="display: none;"><?php echo $misure ?></div>
        <div id="scritte">
            <h2 style="display:flex;justify-content: center;padding-top:20px">
                ITT G. Fauser - OpenDay 18 gennaio 2020
            </h2>
            <h5 style="display:flex;justify-content: center;" id="h5">
                Progetto in Arduino: Misurazioni dei parametri ambientali
            </h5>
            <h2 style="display:flex;justify-content: center;">Laboratorio <?php echo $LABORATORIO ?></h2>
            <h4 style="display:flex;justify-content: center;" id="sottotitolo">
                Lettura dati in corso... Attendi...
            </h4>
        </div>
        <div class="container">
            <h1 id="condizione" class="row align-items-center justify-content-center"></h1>
            <h5 id="ultimaUmidita" class="row align-items-center justify-content-center"></h5>
            <h5 id="ultimaLuminosita" class="row align-items-center justify-content-center"></h5>
            <div id="immagine" class="row align-items-center justify-content-center"></div>
            <div id="tabella" class="row align-items-center justify-content-center"></div>
        </div>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
        <script type="text/javascript">
            // Funzione JQuerry di aggiornamento dei dati e aggiornamento dinamico (ogni 2 secondi), per evitare il ricaricamento della pagina
            $(document).ready(function () {
                function aggiornaDati() {
                    $.ajax({
                        type: 'GET', // Richiesta GET allo script "richiedi.php"
                        url: 'richiedi.php?nomeFile=misure<?=$LABORATORIO?>.txt',
                        success: function (data) {
                            $('#placeholder').html(data);
                            var placeholderMisure = document.getElementById("placeholder").innerText.replace("<br/>", "");
                            Main(tornaArrayMisure(placeholderMisure));
                        }
                    });
                }

                aggiornaDati();
                setInterval(function () {
                    aggiornaDati();
                }, 2000);
            });
        </script>
    </body>
</html>