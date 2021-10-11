<style>
    .container>.custom-message {
        background-color: rgba(0, 0, 0, 0.05);
        padding: 0.5rem;
        border-radius: 4px;
    }

    .container>p.non-important,
    .container>div.non-important {
        color: rgba(0, 0, 0, 0.9);
        font-size: normal;
    }

    .container>p.non-important ul li,
    .container>div.non-important ul li {
        color: rgba(0, 0, 0, 0.9);
        font-size: normal;
    }

    .img {
        display: block;
        padding: 1rem;
    }

    .container hr {
        color: rgba(0, 0, 0, 0.15)
    }

    .container address {
        font-size: small;
    }
</style>
<div class="container">
    <h2 class="custom-message">{{$notifica->messaggioCustom}}</h2>

    <!-- <hr> -->
    <br>

    @if ($servizio->mezzo[0])
    <p class="non-important">
        Mezzo interessato:
    <ul>
        <li>Identificativo: {{$servizio->mezzo[0]->targa ?? $servizio->mezzo[0]->telaio}}</li>
        @if ($servizio->mezzo[0]->modello)
        @if ($servizio->mezzo[0]->modello->brand)
        <li>Marca: {{$servizio->mezzo[0]->modello->brand->marca}}</li>
        @endif
        <li>Modello: {{$servizio->mezzo[0]->modello->modello}}</li>
        @endif
    </ul>
    </p>
    @endif
    <p class="non-important">
        Unitcode periferica: {{$servizio->gps[0]->unitcode}}
    </p>

    <p class="non-important">
        Soglia {{$notifica->trigger->tipologia->tipologia}} interessata:
    <ul>
        <li>Valori soglia: [{{$notifica->trigger->inizio}}-{{$notifica->trigger->fine}}]</li>
        <li>Valore effettivo: {{$currentValue}}</li>
    </ul>
    </p>

    <a class="img" href="https://web.recorditalia.net/auth/login">
        <img src="https://web.recorditalia.net/assets/Logo_record.png" height=131>
    </a>
    <address>© 2021 Record SRL – Via Ugo Braschi, 8 – 47822 Santarcangelo di R. (RN) – Tel. 0541-626892 – P.IVA 03793080403</addressw>
</div>
