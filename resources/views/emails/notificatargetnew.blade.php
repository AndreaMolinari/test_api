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
    <h2 class="custom-message">{{$messaggioCustom}}</h2>

    <!-- <hr> -->
    <br>

    @if (isset($mezzo))
    <p class="non-important">
        Mezzo interessato:
    <ul>
        <li>Identificativo: {{$mezzo->targa ?? $mezzo->telaio}}</li>
        @if ($mezzo->modello)
            @if ($mezzo->modello->brand)
            <li>Marca: {{$mezzo->modello->brand->marca}}</li>
            @endif
        <li>Modello: {{$mezzo->modello->modello}}</li>
        @endif
    </ul>
    </p>
    @endif
    @if (isset($servizio->gps[0]))
    <p class="non-important">
        Unitcode periferica: {{$servizio->gps[0]->unitcode}}
    </p>
    @endif

    <p class="non-important">
        Bersaglio interessato:
    <ul>
        <li>Identificativo: {{$nomeBersaglio}}</li>
    </ul>
    </p>

    <a class="img" href="https://web.recorditalia.net/auth/login">
        <img src="https://web.recorditalia.net/assets/Record/Logo_record.png" height=80>
    </a>
    <address>© 2021 Record SRL – Via Ugo Braschi, 8 – 47822 Santarcangelo di R. (RN) – Tel. 0541-626892 – P.IVA 03793080403</address>
</div>
