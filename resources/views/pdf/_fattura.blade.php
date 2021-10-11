<style>
    a {
        color: #000;
    }

    table {
        width: 100%;
        font-size: 10pt;
    }

    th{
        white-space: nowrap;
    }

    table thead tr th {
        padding-bottom: 15px;
        vertical-align: top;
    }

    .fatturaInfo .cliente {
        background: rgba(0, 148, 216, 0.05);
        text-align: left;
        border: solid 1px rgba(15, 37, 117, 1);
    }

    table.cliente tr th {
        text-align: left;
    }

    table thead {
        text-align: center;
        font-weight: normal;
        font-size: 12pt;
    }

    .infoRecord {
        font-size: 10pt;
    }

    table tfoot {
        text-align: center;
        font-size: 10pt;
    }

    table.toPay thead {
        background: rgba(0, 148, 216, 0.2);
        font-weight: bold;
    }

    table.toPay thead tr,
    table.toPay thead tr th {
        border: none;
        padding: 5px;
        font-size: 11pt;
        font-weight: bold;
    }

    table.toPay tbody tr {
        background: rgba(0, 148, 216, 0.05);
        font-size: 11pt;
        font-weight: normal;
    }

    table.toPay tbody tr:nth-child(2n) {
        background: rgba(0, 148, 216, 0.025);
    }

    table.toPay tbody tr td {
        text-align: center;
    }

    table.toPay tbody tr td:first-child {
        text-align: left;
    }

    table.imponibile {
        width: auto;
        text-align: right;
    }

    table.lightTable {
        width: auto;
    }

    table tr td.borderTd{
        border: solid 1px rgba(15, 37, 117, .25);
        text-align: right;
    }
    table tr td.borderTd table{
        border: solid 1px red;
    }
</style>
<?php
$fatturaParts = "
    <table class='toPay'>
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
";
for ($i = 0; $i < 1000; $i++) {
    $fatturaParts .= "<tr>
        <td>Apparato rotto {{$i}}</td>
        <td>1</td>
        <td>0,00</td>
        <td>0,00</td>
        <td>0</td>
        <td>22</td>
    </tr>
    ";
}
$fatturaParts .= "
        </tbody>
    </table>
";
?>
<table>
    <thead>
        <tr>
            <th>
                <img src="./images/Logo_record_small.png" height="72">
            </th>
        </tr>
        <tr>
            <th>
                Record SRL Sede Legale: Via Ugo Braschi, 8 Int. 1 - 47822 - Santarcangelo di Romagna (RN)
            </th>
        </tr>
        <tr>
            <th class="infoRecord">
                P.I./C.F.: 03793080403 - Reg.Impr.RN: 03793080403 - Numero REA 308789 - Capitale Sociale &euro; 100'000 I.V<br>
                Tel.: <a href="tel:+390541326219">0541.326219</a> e-mail: <a href="mailto: amministrazione@recorditalia.net">amministrazione@recorditalia.net</a> web: <a href="https://www.recorditalia.net">www.recorditalia.net</a>
            </th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>
                <table class="fatturaInfo">
                    <tr>
                        <td style="vertical-align: top; font-weight: bold">
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
            </td>
        </tr>
        <tr>
            <td><?= $fatturaParts ?></td>
        </tr>
        <tr>
            <td class="borderTd">
                <table class="imponibile">
                    <tr>
                        <th>
                            Imponibile &euro;
                        </th>
                    </tr>
                    <tr>
                        <td>
                            567,00
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td class="borderTd">
                <table class='lightTable imponibile'>
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
            </td>
        </tr>
        <tr>
            <td class="borderTd">
                <table>
                    <tr>
                        <td>
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
                        </td>
                        <td>
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
                        </td>
                        <td>
                            <table class="imponibile">
                                <tr>
                                    <th>
                                        Totale &euro;
                                    </th>
                                </tr>
                                <tr>
                                    <td>
                                        567,00
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </tbody>
    <tfoot>
        <tr>
            <td>
                Non saranno accettate eventuali contestazioni oltre i 15 gg. dal ricevimento del presente documento.
                In caso di ritardato pagamento verranno applicati gli interessi come da D.L. Nr. 231 del 09/10/02 - Art.11 ex Art.15 Nr. 2D.P:R 633/72.
                <br>Conai Assolto ove dovuto
            </td>
        </tr>
    </tfoot>
</table>