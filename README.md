# 🔐 Escape Game en ligne - Projet Vue.js, JavaScript, CSS, PHP, MySQL

## 📖 Description
Ce projet est un Escape Game en ligne immersif, conçu pour offrir une expérience captivante à travers plusieurs scénarios et niveaux de difficulté. Chaque joueur devra résoudre des énigmes dans un temps imparti. Des indices sont disponibles, mais attention : leur utilisation entraîne des pénalités sur le score final !

Le jeu intègre également un système de classement en temps réel, la gestion des comptes utilisateurs et un historique des parties. Les joueurs peuvent participer sans avoir de compte, mais certaines fonctionnalités, comme l’historique personnel et l’enregistrement du score, sont réservées aux utilisateurs connectés.

👉 **Accéder au jeu en ligne** : [Escape Game en ligne](http://miscuitatlas-escapegame.rf.gd/)

## 📌 Fonctionnalités
* Scénarios multiples avec différentes ambiances et niveaux de difficulté.
  
* Participation avec ou sans compte :
  * Sans compte : accès aux scénarios, possibilité de jouer librement et visualisation du classement.
  * Avec compte : suivi des scores, classement personnalisé et historique des parties.

* Système de Timer pour mettre la pression aux joueurs.
  
* Indices avec pénalité pour aider en cas de blocage.
  
* Classement en temps réel des meilleurs joueurs pour chaque scénario.
  
* Historique des parties jouées pour chaque utilisateur connecté.
  
* Effets visuels et sonores immersifs.
  
* Base de données sécurisée pour la gestion des comptes utilisateurs et des scores MySQL.

## 🛠️ Technologies utilisées
* Frontend : Vue.js, JavaScript, CSS

* Backend : PHP

* Base de données : MySQL

## 📦 Installation et configuration
1. Cloner le projet :
```
git clone https://github.com/MiscuitAtlas/EscapeGame_VueJS
cd EscapeGame_VueJS
```
2. Installer les dépendances :
```
npm install
```
3. Lancer le serveur de développement :
```
npm run serve
```
4. Configurer la base de données :
```
Importer le fichier database.sql dans votre base de données.
```
5. Configurer les variables d’environnement :
```
VUE_APP_API_URL=http://localhost:8000
DB_HOST=localhost
DB_NAME=escape_game
DB_USER=root
DB_PASS=password
```

## 🎮 Instructions de jeu
1. Créer un compte ou se connecter pour profiter de toutes les fonctionnalités, ou jouer directement sans compte.

2. Choisir un scénario parmi ceux proposés.

3. Résoudre les énigmes dans le temps imparti.

4. Utiliser les indices en cas de besoin (avec pénalité de score).

5. Terminer le jeu pour voir votre score et votre position dans le classement.

## 📊 Classement et Historique
* Classement : Affichage en temps réel du top des joueurs pour chaque scénario.

* Historique des parties : Accessible pour chaque utilisateur afin de suivre ses performances passées.

## 📧 Contact
Pour toute question ou suggestion, contactez-moi :

* Email : maxencefran@gmail.com

* GitHub : [MiscuitAtlas](https://github.com/MiscuitAtlas)

* Linkedin : [MaxenceFrançais](https://www.linkedin.com/in/maxence-fran%C3%A7ais-a039a2307/)
