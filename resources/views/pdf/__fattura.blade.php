<style>
    table {
        width: 100%;
    }

    table.toPay {
        border: solid 1px #000;
        margin: 2rem 0;
    }

    table.toPay thead {
        background: rgba(0, 0, 0, 0.2);
        font-weight: bold;
    }

    table.toPay thead tr,
    table.toPay thead tr th {
        border: none;
    }

    table.toPay tbody {
        background: rgba(0, 0, 0, 0.05);
    }

    table.toPay tbody tr td {
        text-align: center;
    }

    table.toPay tbody tr td:first-child {
        text-align: left;
    }

    table.cliente {
        border: solid 1px #000;
        background: rgba(0, 0, 0, 0.1);
    }

    table.fatturaInfo tr td {
        vertical-align: top;
    }

    p {
        text-align: center;
        font-size: 11pt;
    }

    .intestazione {
        font-weight: bold;
        font-size: 12pt;
    }

    p.pie {
        font-size: 10pt;
    }

    table.lightTable{
        width: auto;
        border: solid;
        text-align: right;
    }
</style>

<table>
    <tr>
        <td style="text-align: center">
            <img src="./images/Logo_record.png" height="72">
        </td>
    </tr>
</table>
<p class="intestazione">
    Record SRL Sede Legale: Via Ugo Braschi, 8 Int. 1 - 47822 - Santarcangelo di Romagna (RN)
</p>
<p>
    P.I./C.F.: 03793080403 - Reg.Impr.RN: 03793080403 - Numero REA 308789 - Capitale Sociale &euro; 100'000 I.V<br>
    Tel.: <a href="tel:0541326219">0541.326219</a> e-mail: <a href="mailto: amministrazione@recorditalia.net">amministrazione@recorditalia.net</a> web: <a href="https://www.recorditalia.net">www.recorditalia.net</a>
</p>

<table class="fatturaInfo">
    <tr>
        <td>
            Fattura n.1972-21/B del 30/06/2021
        </td>
        <td>
            <table class="cliente">
                <tr>
                    <th>Ecomerda</th>
                </tr>
                <tr>
                    <td>Via Cella Raibano 14</td>
                </tr>
                <tr>
                    <td>05043 Masdajsd RN</td>
                </tr>
                <tr>
                    <td>Partita Iva: 398493849</td>
                </tr>
                <tr>
                    <td>Codice Fiscale: 398493849</td>
                </tr>
            </table>
        </td>
    </tr>
</table>
<table class="toPay">
    <thead>
        <tr>
            <th>Descrizione</th>
            <th>Q.t&agrave;</th>
            <th>Prezzo &euro;</th>
            <th>Imponibile &euro;</th>
            <th>Sconto %</th>
            <th>IVA %</th>
        </tr>
    </thead>
    <tbody>
        @for ($i = 0; $i < 10; $i++) <tr>
            <td>Apparato rotto {{$i}}</td>
            <td>1</td>
            <td>0,00</td>
            <td>0,00</td>
            <td>0</td>
            <td>22</td>
            </tr>
            @endfor
    </tbody>
</table>


<?php
$imponibile = "
<table class='lightTable'>
    <thead>
        <tr>
            <th>
                Imponibile &euro;
            </th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>
                478,00
            </td>
        </tr>
    </tbody>
</table>";

$speseIncasso = "
<table class='lightTable'>
    <tr>
        <th>
            Spese Incasso &euro;
        </th>
        <th>
            Imposta &euro;
        </th>
    </tr>
    <tr>
        <td>
            478,00
        </td>
        <td>
            478,00
        </td>
    </tr>
</table>
";

$metodoDiPagamento = "
<table class='lightTable'>
    <tr>
        <th>Metodo di pagamento</th>
        <td>RIBA IT00000000000000000000000000</td>
    </tr>
    <tr>
        <th>Scadenza</th>
        <td>30 gg dffm</td>
    </tr>
    <tr>
        <th>Dettagli scadenze</th>
        <td>31/07/2021: &euro; 394,00</td>
    </tr>
</table>
";
$bancaRecord = "
<table class='lightTable'>
    <thead>
        <tr>
            <th>Banca di Appoggio Record Srl</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>
                Cassa Di Risparmio di Ravenna
            </td>
        </tr>
        <tr>
            <td>
                Agenzia di Santarcangelo di R
            </td>
        </tr>
        <tr>
            <td>
                IBAN: IT00000000000000000000000000
            </td>
        </tr>
    </tbody>
</table>
";
?>

<table>
    <tr>
        <td>
            <?=$imponibile?>
        </td>
    </tr>
    <tr>
        <td>
            <?=$speseIncasso?>
        </td>
    </tr>
    <tr>
        <td>
            <?=$metodoDiPagamento?>
        </td>
        <td>
            <?=$bancaRecord?>
        </td>
    </tr>
</table>
    
<footer>
    <p class="pie">
        Non saranno accettate eventuali contestazioni oltre i 15 gg. dal ricevimento del presente documento.
        In caso di ritardato pagamento verranno applicati gli interessi come da D.L. Nr. 231 del 09/10/02 - Art.11 ex Art.15 Nr. 2D.P:R 633/72.
        <br>Conai Assolto ove dovuto
    </p>
</footer>