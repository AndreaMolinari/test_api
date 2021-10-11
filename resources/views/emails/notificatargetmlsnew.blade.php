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

    <a class="img" href="https://cnew.mechanicallinesolutions.net/auth/login">
        <img src="https://www.mechanicallinesolutions.net/wp-content/uploads/2019/03/MLS_logo_sito-01-1-1.png" height=80>
    </a>
    <address>© 2021 Mechanical Line Solutions srl – Via Lodovico Ariosto, 6 – 20145 Milano (MI) – Tel. +39 02 45076781 – P.IVA 06597240966 - <a href="mailto:info@mlssrl.it">info@mlssrl.it</a></address>
</div>
