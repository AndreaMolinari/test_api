#!/bin/bash

build=true
push=false
help=false

while getopts v:nph flag
do
    case "${flag}" in
        v) version=${OPTARG};;
        p) push=true;;
        n) build=false;;
        h) help=true;;
    esac
done

if [ "$help" == true ]
then
    echo "Ciao, questo script serve per buildare più facilmente tutte le immagini delle API di Record.
    
Usage:

-h  Help - This help
-n  No Build images (puoi usarlo se decidi di pushare le immagini che hai già buildato in precedenza)
-p  Push builded images (le immagini verranno pushate subito dopo il build)
-v  Define Version of images to build - REQUIRED

Un ciaone..
Andrea"
    exit
fi

if [ "$build" == true ]
then

    if [ "$1" = "" ] || [ "$version" = "" ]
    then
        echo "Utilizzo: $0 -v <versione>"
        exit
    fi

    IFS='.' read -ra VRS <<< "$version" #split di string in array

    if [ ! -d "./docker" ] && [ ! -f "./artisan" ]
    then
        echo "Devi essere nella root del progetto per generare le immagini"
        exit
    fi

    cd ./docker

    docker image build -f api-fpm.dockerfile -t asrecorditalia/record_api:fpm-${VRS[0]}.${VRS[1]}.${VRS[2]} -t asrecorditalia/record_api:fpm-${VRS[0]}.${VRS[1]} -t asrecorditalia/record_api:fpm-${VRS[0]} -t asrecorditalia/record_api:fpm-latest --build-arg PRODUCTION=true ..

    docker image build -f api-worker.dockerfile -t asrecorditalia/record_api:worker-${VRS[0]}.${VRS[1]}.${VRS[2]} -t asrecorditalia/record_api:worker-${VRS[0]}.${VRS[1]} -t asrecorditalia/record_api:worker-${VRS[0]} -t asrecorditalia/record_api:worker-latest --build-arg PRODUCTION=true ..

    if [ "$push" == true ]
    then
        docker image push -a asrecorditalia/record_api
        # echo "pusho"
    fi
fi
