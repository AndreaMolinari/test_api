# CERTBOT 
https://turbolab.it/apache-server-web-1212/guida-ottenere-certificato-https-wildcard-gratis-let-s-encrypt-.miosito.com-1690

# Guida

```shell

cd docker;
docker compose up -d --build api worker nginx redis-webui elastichsearch

```

esegui le migrazioni per
DDT e bersagli

## ElasticSearch

Ricorda di indicizzare i modelli Searchable!!

```shell
php artisan scout:import "App\Models\Anagrafica"
```

select m1.*
from TT_Modello as m1
join TT_Modello as m2 on m1.modello = m2.modello
WHERE m1.id <> m2.id AND m1.idTipologia = m2.idTipologia AND m1.idBrand = m2.idBrand;

select t1.*
from TC_UtenteFlotta as t1
join TC_UtenteFlotta as t2 on t1.idRiferimento = t2.idRiferimento and t1.idUtente = t2.idUtente
WHERE t1.id <> t2.id;

select t1.*
from TT_Componente as t1
join TT_Componente as t2 on t1.unitcode = t2.unitcode
where t1.id <> t2.id;
