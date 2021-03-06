# cameroun-server
Centralisation des données provenant des différents hôpitaux utilisant un logiciel de suivi patient.

Chaque hôpital a un numéro d'identification unique et il remonte ses données au serveur en arrière plan dès que le réseau est disponible.

Il est possible d'extraire des données à des fins statistiques depuis *cameroun-server*.

## Installation
### Client
[Téléchargez le client ici](https://github.com/tolokoban/cameroun).

### Serveur
#### Production
Il vous faut un hébergeur qui propose du PHP7 et du MySQL.
Déposez-y les [fichiers de l'archive](https://github.com/tolokoban/cameroun-server/archive/gh-pages.zip) et allez sur le site avec votre navigateur. A la première connexion, il vous sera demandé l'adresse du serveur de base de données, le nom de la base à utiliser pour cette application et le couple utilisateur/mot de passe.

#### Développement
```
git clone https://github.com/tolokoban/cameroun-server.git
cd cameroun-server
npm update
cd submoidules/tfw
git submodules init
git submodules update
git checkout master
npm update
cd ../..
npm run watch
```

## Documentation

* [Base de données](man/db.md)
