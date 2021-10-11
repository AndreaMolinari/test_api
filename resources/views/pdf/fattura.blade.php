<!DOCTYPE html>
<html>

<head>
    <style>
        @page {
            margin: .8cm 1cm;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            margin: 5cm 0 1.5cm 0;
            font-size: 10pt;
        }

        header {
            position: fixed;
            top: 0px;
            height: 5cm;
            line-height: normal;
            margin-bottom: 5mm;
            text-align: center;
        }

        header .logo{
            margin-bottom: 5mm;
        }

        header .paginator {
            text-align: right;
            font-size: 90%;
        }

        .info>.heading {
            font-size: 110%;
            font-weight: bold;
            margin: 0;
        }

        footer {
            position: fixed;
            height: 1.5cm;
            bottom: 0;
            text-align: center;
            font-size: 7pt;
            line-height: normal;
        }

        a {
            color: #000;
            text-decoration: none;
        }

        table {
            width: 100%;
        }

        table thead {
            text-align: center;
            font-weight: normal;
        }

        table thead tr th {
            padding-bottom: 15px;
            vertical-align: top;
            white-space: nowrap;
        }

        table tfoot {
            text-align: center;
            font-size: 10pt;
        }

        main>p.page {
            page-break-after: always;
        }

        main>p.page:last-child {
            page-break-after: never;
        }

        .pagenum::before {
            content: counter(page)
        }

        .fatturaInfo tr .fatturaName {
            vertical-align: top;
            font-weight: bold;
            width: 100%
        }

        .fatturaInfo .cliente {
            width: auto;
            background: rgba(0, 148, 216, 0.05);
            text-align: left;
            border: solid 1px rgba(15, 37, 117, 1);
            padding: 2.5mm;
        }

        .fatturaInfo .cliente tr td {
            white-space: nowrap;
        }

        table.cliente tr th {
            text-align: left;
        }

        table.toPay {
            margin: 5mm 0;
        }

        table.toPay thead {
            background: rgba(0, 148, 216, 0.35);
            font-weight: bold;
        }

        table.toPay thead tr {
            border: none;
        }

        table.toPay thead tr th {
            font-size: 8pt;
            font-weight: bold;
            padding: 2mm;
        }
        table.toPay tbody tr {
            background: rgba(0, 148, 216, 0.20);
        }

        table.toPay tbody tr:nth-child(2n -1) {
            background: rgba(0, 148, 216, 0.025);
        }

        table.toPay tbody tr td {
            text-align: center;
            font-size: 8pt;
            padding: 1mm;
            font-weight: normal;
        }

        table.toPay tbody tr td:first-child {
            text-align: left;
        }

        .consuntivi .consuntivo:last-child table {
            width: 100%;
        }

        .table_footer {
            margin-top: 5mm;
            border: solid 1px rgba(15, 37, 117, 1);
            border-collapse: collapse;
            border-spacing: 0;
        }

        .table_footer td {
            padding: 1mm;
            border-bottom: solid 1px rgba(15, 37, 117, 1);
        }
    </style>
</head>

<body>
    <header>
        <div class="logo">
            <img src="./images/Logo_record_small.png" height="72">
        </div>
        <div class="info">
            <di class="heading">Record SRL Sede Legale: Via Ugo Braschi, 8 Int. 1 - 47822 - Santarcangelo di Romagna (RN)</di>
            <p class="contatti">
                P.I./C.F.: 03793080403 - Reg.Impr.RN: 03793080403 - Numero REA 308789 - Capitale Sociale &euro; 100'000 I.V<br>
                Tel.: <a href="tel:+390541326219">0541.326219</a> e-mail: <a href="mailto: amministrazione@recorditalia.net">amministrazione@recorditalia.net</a> web: <a href="https://www.recorditalia.net">www.recorditalia.net</a>
            </p>
        </div>
        <div class="paginator">
            Pagina <span class="pagenum"></span>
        </div>
    </header>
    <footer>
        Non saranno accettate eventuali contestazioni oltre i 15 gg. dal ricevimento del presente documento.
        In caso di ritardato pagamento verranno applicati gli interessi come da D.L. Nr. 231 del 09/10/02 - Art.11 ex Art.15 Nr. 2D.P:R 633/72.
        <br>Conai Assolto ove dovuto
    </footer>
    <main>
        <table class="fatturaInfo">
            <tr>
                <td class="fatturaName">
                    Fattura n.{{$fattura['anno']}}-{{$fattura['numero']}} del {{$fattura['created_at']->format('d/m/Y')}}
                </td>
                <td>
                    <table class="cliente">
                        <tr>
                            <th>{{$fattura['cliente']['nominativo']}}</th>
                        </tr>
                        <tr>
                            <td>{{$fattura['cliente']['sede_legale']['via']}}
                                {{$fattura['cliente']['sede_legale']['civico']}}
                            </td>
                        </tr>
                        <tr>
                            <td>
                                {{$fattura['cliente']['sede_legale']['cap']}}
                                {{$fattura['cliente']['sede_legale']['comune']}}
                                ({{$fattura['cliente']['sede_legale']['provincia']}})
                            </td>
                        </tr>
                        @if( $fattura['cliente']['pIva'] )
                        <tr>
                            <td>Partita Iva: {{$fattura['cliente']['pIva']}}</td>
                        </tr>
                        @endif
                        @if( $fattura['cliente']['codFisc'] )
                        <tr>
                            <td>Codice Fiscale: {{$fattura['cliente']['codFisc']}}</td>
                        </tr>
                        @endif
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
                @foreach ($fattura['servizi'] ?? [] as $servizio)
                <tr>
                    <td>
                        Canone di servizio {{$servizio['periodo']['nome']}}<br>
                        Periodo: da
                            {{($test = (new \Carbon\Carbon($fattura['created_at']))->addMonthsNoOverflow(1)->startOfMonth())->format('d/m/Y')}} a
                            {{$test->addMonthsNoOverflow($servizio['periodo']['data']['months'])->endOfMonth()->format('d/m/Y')}}<br>
                            Riferimento Mezzi: {{ implode(' - ', $servizio['identificativi']->toArray()) }}
                    </td>
                    <td>{{count($servizio['identificativi'])}}</td>
                    <td>{{$servizio['prezzoUnitario']}}</td>
                    <td>{{$servizio['prezzoImponibile']}}</td>
                    <td>0</td>
                    <td>22</td>
                </tr>
                @endforeach

                @foreach ($fattura['ddts'] ?? [] as $ddt)
                <tr>
                    <td>
                        @if ($ddt['modello'])
                        Apparato {{$ddt['modello']['nome']}}<br>
                        @endif

                        Rif. DDT: Nr.{{$ddt['riferimentoDDT']}} del {{$ddt['dataSpedizione']->format('d/m/Y')}}
                        <br>
                        {{$ddt['identificativo']}}
                    </td>
                    <td>1</td>
                    <td>{{$ddt['prezzo']}}</td>
                    <td>{{$ddt['prezzo']}}</td>
                    <td>{{$ddt['sconto']}}</td>
                    <td>{{$ddt['iva']}}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <table class="table_footer">
            <tbody>
                <tr>
                    <td></td>
                    <td></td>
                    <td align="right">
                        <b>Imponibile &euro;</b><br>
                        {{$fattura['imponibile']}}
                    </td>
                </tr>
                <tr>
                    <td></td>
                    <td align="right">
                        <b>Spese Incasso &euro;</b><br>
                        {{$fattura['speseIncasso']}}
                    </td>
                    <td align="right">
                        <b>Imposta &euro;</b><br>
                        {{$fattura['imposta']}}
                    </td>
                </tr>
                <tr>
                    <td>
                        <b>Metodo di pagamento</b>: {{$fattura['cliente']['fatturazione']['modalita']['nome']}}<br>
                        {{$fattura['cliente']['fatturazione']['iban']}}<br>
                        <b>Scadenze</b>: {{$fattura['cliente']['fatturazione']['periodo']['nome']}}<br>
                        <b>Dettagli Scadenze</b>: <?=nl2br(implode("\n", $fattura['scadenze']))?>
                    </td>
                    <td>
                        <b>Banca Appoggio Record Srl</b><br>
                        Cassa di Risparmio di Ravenna<br>
                        Agenzia di Santarcangelo di R<br>
                        IBAN: IT32T0627068020CC0710184718
                    </td>
                    <td align="right">
                        <b>Totale &euro;</b><br>
                        {{$fattura['totale']}}
                    </td>
                </tr>
            </tbody>
        </table>
    </main>
</body>

</html>