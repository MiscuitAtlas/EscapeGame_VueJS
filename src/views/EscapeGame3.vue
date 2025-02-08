<template>
  <div class="escape-game">
    <div class="game-layout">
      
      <!-- Classement des Meilleurs Joueurs -->
      <div class="leaderboard">
        <h2>🏆 Classement des Meilleurs Joueurs</h2>
        <ul>
          <li v-for="(player, index) in leaderboard" :key="index">
            {{ index + 1 }}. {{ player.username }} - {{ player.score }} pts en {{ player.duration }}s
          </li>
        </ul>
      </div>

      <!-- Zone de Jeu -->
      <div class="game-area" style="position: relative;">
        <h1>Le Vaisseau Perdu</h1>
        <p>Ramassez les objets et utilisez-les au bon endroit pour réparer le vaisseau !</p>

        <div class="timer">⏱️ Temps restant : {{ timer }}s</div>

        <!-- Overlay qui désactive les interactions dans la zone de jeu lorsque l'énigme est active -->
        <div v-if="currentPuzzle" class="overlay"></div>

        <div class="image-container">
          <img src="@/assets/vaisseau.webp" class="spaceship-image" alt="Vaisseau Spatial" />

          <!-- Items sur la carte -->
          <transition name="fade">
            <img
              v-if="!collectedItems.includes('Carte d\'accès') && !usedItems['Carte d\'accès'] && selectedItem !== 'Carte d\'accès'"
              class="item"
              src="@/assets/card.webp"
              style="top: 70%; left: 20%;"
              @click="collectItem('Carte d\'accès')"
              alt="Carte d'accès"
            />
          </transition>
          <transition name="fade">
            <img
              v-if="!collectedItems.includes('Fusible') && !usedItems['Fusible'] && selectedItem !== 'Fusible'"
              class="item"
              src="@/assets/fuse.png"
              style="top: 45%; left: 64%;"
              @click="collectItem('Fusible')"
              alt="Fusible"
            />
          </transition>
          <transition name="fade">
            <img
              v-if="!collectedItems.includes('Clé de sécurité') && !usedItems['Clé de sécurité'] && selectedItem !== 'Clé de sécurité'"
              class="item"
              src="@/assets/key.webp"
              style="top: 80%; left: 64%;"
              @click="collectItem('Clé de sécurité')"
              alt="Clé de sécurité"
            />
          </transition>

          <!-- Zones interactives invisibles -->
          <div class="hidden-interaction" style="top: 45%; left: 23%;" @click="useItem('Fusible', 'électricité')"></div>
          <div class="hidden-interaction" style="top: 75%; left: 93%;" @click="useItem('Carte d\'accès', 'panneau de contrôle')"></div>
          <div class="hidden-interaction" style="top: 55%; left: 55%;" @click="useItem('Clé de sécurité', 'demarrage')"></div>

          <!-- Items validés -->
          <transition name="fade">
            <img
              v-if="usedItems['Carte d\'accès']"
              class="item used"
              src="@/assets/card.webp"
              :style="usedItems['Carte d\'accès']"
              alt="Carte d'accès"
            />
          </transition>
          <transition name="fade">
            <img
              v-if="usedItems['Fusible']"
              class="item used"
              src="@/assets/fuse.png"
              :style="usedItems['Fusible']"
              alt="Fusible"
            />
          </transition>
          <transition name="fade">
            <img
              v-if="usedItems['Clé de sécurité']"
              class="item used"
              src="@/assets/key.webp"
              :style="usedItems['Clé de sécurité']"
              alt="Clé de sécurité"
            />
          </transition>
        </div>

        <!-- Bloc de feedback affiché sous l'image -->
        <transition name="fade">
          <div v-if="feedbackMessage" class="feedback">{{ feedbackMessage }}</div>
        </transition>

        <!-- Inventaire -->
        <div class="inventory">
          <h3>Inventaire des Objets :</h3>
          <ul>
            <li v-for="item in collectedItems" :key="item">
              🛠️ {{ item }}
              <button @click="selectItem(item)">🔧 Sélectionner</button>
            </li>
          </ul>
        </div>

        <!-- Modal d'énigme : affichant uniquement la question (les indices ne s'y affichent plus) -->
        <transition name="fade">
          <div v-if="currentPuzzle" class="puzzle-modal">
            <div class="puzzle-content">
              <h3>{{ currentPuzzle.question }}</h3>
              <input type="text" v-model="currentPuzzleAnswer" placeholder="Votre réponse" />
              <div class="puzzle-buttons">
                <button @click="submitPuzzleAnswer">Valider</button>
                <button @click="cancelPuzzle">Annuler</button>
              </div>
            </div>
          </div>
        </transition>

        <!-- Statut de la Mission -->
        <div v-if="missionStatus" class="final-choice">
          <h2>{{ missionStatus }}</h2>
          <p>🎯 Score final : {{ score }}</p>
          <button class="action-button" v-if="isEscaped" @click="restartGame">🎮 Rejouer</button>
          <button class="action-button" @click="goGame">🏠 Retour à l'accueil</button>
        </div>
      </div>

      <!-- Box d'Indices (les indices s'affichent uniquement ici) -->
      <div class="hint-toggle">
        <button @click="toggleHintTab">🗒️ Indices</button>
        <transition name="fade">
          <div v-if="showHintTab" class="hint-box">
            <h3>Indices :</h3>
            <button @click="showHint(0)">Indice 1</button>
            <button @click="showHint(1)">Indice 2</button>
            <button @click="showHint(2)">Indice 3</button>
            <ul>
              <li v-for="hint in revealedHints" :key="hint">💡 {{ hint }}</li>
            </ul>
          </div>
        </transition>
      </div>

    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      // Objets collectés (non encore utilisés)
      collectedItems: [],
      // Objets validés (utilisés correctement) et leur style d'affichage
      usedItems: {},
      // Objet actuellement sélectionné pour être utilisé
      selectedItem: null,
      // État de la mission
      missionStatus: '',
      isEscaped: false,
      // État de l'énigme en cours
      currentPuzzle: null,
      currentPuzzleAnswer: "",
      currentPuzzleItem: null,
      // Définition des actions requises pour chaque objet,
      // incluant la zone d'utilisation, le style d'affichage et l'énigme associée
      requiredActions: {
        "Carte d'accès": {
          location: "panneau de contrôle",
          usedStyle: { top: "75%", left: "93%" },
          puzzle: {
            question: "Combien de planètes orbitent autour du Soleil ?",
            answer: "8",
            clue: "Pensez au système solaire moderne."
          }
        },
        "Fusible": {
          location: "électricité",
          usedStyle: { top: "45%", left: "23%" },
          puzzle: {
            question: "Quel est le nom de la première sonde à quitter le système solaire ?",
            answer: "voyager 1",
            clue: "Elle a été lancée en 1977."
          }
        },
        "Clé de sécurité": {
          location: "demarrage",
          usedStyle: { top: "55%", left: "55%" },
          puzzle: {
            question: "Quel est le nom de notre galaxie ?",
            answer: "voie lactée",
            clue: "C'est le chemin lacté dans le ciel."
          }
        }
      },
      // Indices simplifiés pour aider à résoudre les énigmes (en rapport avec l'espace)
      hints: [
        "Suite à la reclassification de Pluton, le système solaire compte une planete en moins.",
        "Lancée en 1977, c'est la première sonde à avoir quitté notre système solaire.",
        "Notre galaxie fait référence à une vaste traînée lumineuse visible dans le ciel nocturne."
      ],
      // Liste des indices révélés (lorsqu'un indice est cliqué)
      revealedHints: [],
      // Affichage de la box d'indices
      showHintTab: false,
      // Autres états
      leaderboard: [],
      isLoggedIn: false,
      timer: 180,
      score: 0,
      hintPenalty: 100,
      timerInterval: null,
      feedbackMessage: "",
      correctSound: new Audio(require('@/assets/correct-answer.mp3')),
      wrongSound: new Audio(require('@/assets/wrong-answer.mp3')),
      backgroundMusic: new Audio(require('@/assets/background-music.mp3'))
    };
  },
  mounted() {
    this.startTimer();
    this.isLoggedIn = !!localStorage.getItem('user');
    this.fetchLeaderboard();
    document.addEventListener('click', this.startBackgroundMusic, { once: true });
    window.addEventListener('beforeunload', this.stopGame);
  },
  methods: {
    startBackgroundMusic() {
      this.backgroundMusic.loop = true;
      this.backgroundMusic.volume = 0.5;
      this.backgroundMusic.play().catch(error => {
        console.warn("Erreur lors de la lecture de la musique :", error);
      });
    },
    async fetchLeaderboard() {
      try {
        const response = await fetch(`http://localhost/escape-game/backend/getLeaderboard.php?game_id=3`);
        this.leaderboard = await response.json();
      } catch (error) {
        console.error('Erreur lors de la récupération du classement :', error);
      }
    },
    // Collecte d'un objet sur la carte avec feedback visuel
    collectItem(item) {
      if (!this.collectedItems.includes(item)) {
        this.collectedItems.push(item);
        this.feedbackMessage = `${item} récupéré !`;
        setTimeout(() => {
          this.feedbackMessage = "";
        }, 1500);
      }
    },
    // Sélection d'un objet depuis l'inventaire (sans alerte)
    selectItem(item) {
      this.selectedItem = item;
      this.collectedItems = this.collectedItems.filter(i => i !== item);
    },
    // Lancement de l'énigme associée à l'objet utilisé
    useItem(item, location) {
      if (this.selectedItem === item && this.requiredActions[item].location === location) {
        this.currentPuzzle = this.requiredActions[item].puzzle;
        this.currentPuzzleItem = item;
        this.currentPuzzleAnswer = "";
      } else {
        this.wrongSound.play();
        this.feedbackMessage = `Erreur dans l'utilisation de ${item}`;
        setTimeout(() => {
          this.feedbackMessage = "";
        }, 1500);
        if (this.selectedItem) {
          this.collectedItems.push(this.selectedItem);
        }
        this.selectedItem = null;
      }
    },
    // Validation de la réponse à l'énigme
    submitPuzzleAnswer() {
      if (this.currentPuzzleAnswer.trim().toLowerCase() === this.currentPuzzle.answer.trim().toLowerCase()) {
        this.correctSound.play();
        this.feedbackMessage = `${this.currentPuzzleItem} utilisé correctement !`;
        setTimeout(() => {
          this.feedbackMessage = "";
        }, 1500);
        // L'objet est validé et passe dans usedItems
        this.usedItems[this.currentPuzzleItem] = this.requiredActions[this.currentPuzzleItem].usedStyle;
        // Réinitialisation de l'état de l'énigme
        this.currentPuzzle = null;
        this.currentPuzzleItem = null;
        this.currentPuzzleAnswer = "";
        this.checkEscape();
      } else {
        this.wrongSound.play();
        this.feedbackMessage = `Réponse incorrecte. Réessayez.`;
        setTimeout(() => {
          this.feedbackMessage = "";
        }, 1500);
        this.currentPuzzleAnswer = "";
      }
    },
    // Annulation de l'énigme en cours : l'objet est remis dans l'inventaire
    cancelPuzzle() {
      if (this.currentPuzzleItem) {
        this.collectedItems.push(this.currentPuzzleItem);
      }
      this.currentPuzzle = null;
      this.currentPuzzleItem = null;
      this.currentPuzzleAnswer = "";
    },
    // Fin de la mission lorsque tous les objets ont été validés
    checkEscape() {
      if (Object.keys(this.usedItems).length === Object.keys(this.requiredActions).length) {
        clearInterval(this.timerInterval);
        this.backgroundMusic.pause();
        this.isEscaped = true;
        this.missionStatus = "🚀 Félicitations ! Vous avez réparé le vaisseau et démarré l'engin ! 🎉";
        this.calculateScore();
        this.saveGameResult();
      }
    },
    calculateScore() {
      this.score = (this.timer * 10) - (this.hints.length * this.hintPenalty);
    },
    async saveGameResult() {
      const user = JSON.parse(localStorage.getItem('user'));
      if (!user || !user.id) {
        console.error("Utilisateur non connecté ou ID manquant.");
        return;
      }
      try {
        const response = await fetch('http://localhost/escape-game/backend/saveGameSession.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({
            user_id: user.id,
            game_id: 3,
            score: this.score,
            time_remaining: this.timer
          })
        });
        const data = await response.json();
        console.log("✅ Partie enregistrée :", data.message);
      } catch (error) {
        console.error("Erreur lors de l'enregistrement de la partie :", error);
      }
    },
    startTimer() {
      this.timerInterval = setInterval(() => {
        if (this.timer > 0) {
          this.timer--;
        } else {
          clearInterval(this.timerInterval);
          alert("⏰ Temps écoulé !");
          this.restartGame();
        }
      }, 1000);
    },
    stopGame() {
      clearInterval(this.timerInterval);
      this.backgroundMusic.pause();
    },
    toggleHintTab() {
      this.showHintTab = !this.showHintTab;
    },
    // Affichage d'un indice correspondant (Indice 1, 2 ou 3)
    showHint(index) {
      const hint = this.hints[index];
      if (!this.revealedHints.includes(hint)) {
        this.revealedHints.push(hint);
        this.score -= this.hintPenalty;
      }
    },
    retryMission() {
      this.collectedItems = [];
      this.selectedItem = null;
      this.missionStatus = '';
      this.isEscaped = false;
      this.timer = 180;
      this.usedItems = {};
      this.currentPuzzle = null;
      this.currentPuzzleItem = null;
      this.currentPuzzleAnswer = "";
      this.startTimer();
      this.backgroundMusic.currentTime = 0;
      this.backgroundMusic.play();
    },
    restartGame() {
      this.stopGame();
      this.collectedItems = [];
      this.selectedItem = null;
      this.missionStatus = '';
      this.isEscaped = false;
      this.timer = 180;
      this.usedItems = {};
      this.currentPuzzle = null;
      this.currentPuzzleItem = null;
      this.currentPuzzleAnswer = "";
      this.backgroundMusic.currentTime = 0;
      this.backgroundMusic.play();
      this.startTimer();
    },
    goGame() {
      this.stopGame();
      this.$router.push('/game-room');
    }
  },
  beforeRouteLeave(to, from, next) {
    this.stopGame();
    next();
  }
};
</script>

<style scoped>
/* Overlay qui bloque les interactions dans la zone de jeu */
.overlay {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.5);
  z-index: 10;
}

/* Style de la fenêtre modale de l'énigme - Box spécifique avec position plus basse et style distinct */
.puzzle-modal {
  position: absolute;
  top: 60%; /* Descendu pour être sous l'image */
  left: 50%;
  transform: translate(-50%, -50%);
  background: linear-gradient(135deg, #ffffff, #f0f0f0);
  padding: 30px;
  z-index: 20;
  border: 3px solid #ffd700;
  border-radius: 12px;
  box-shadow: 0 8px 16px rgba(0, 0, 0, 0.5);
  width: 350px;
  color: #000; /* Texte en noir pour une bonne lisibilité */
}

/* Exemple de transition fade */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.5s;
}
.fade-enter,
.fade-leave-to {
  opacity: 0;
}

/* Feedback visuel (validation / erreur) */
.feedback {
  margin: 10px 0;
  padding: 5px 10px;
  background: #eef;
  border: 1px solid #99c;
  border-radius: 4px;
  color: #000; /* Texte en noir pour le contraste */
}

/* Conteneur principal */
.escape-game {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 100vh;
  background-color: #1c1c1c;
  color: white;
  padding: 20px;
  box-sizing: border-box;
}

.game-layout {
  display: flex;
  justify-content: center;
  align-items: flex-start;
  gap: 20px;
  max-width: 1200px;
  width: 100%;
  position: relative;
}

/* Zone de Jeu (centrée) */
.game-area {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  text-align: center;
  flex-grow: 1;
}

/* Conteneur de l'image */
.image-container {
  position: relative;
  display: flex;
  justify-content: center;
  align-items: center;
  max-width: 800px;
  width: 100%;
  margin: auto;
}

.spaceship-image {
  width: 100%;
  max-width: 800px;
  border: 3px solid white;
  border-radius: 10px;
}

.object {
  position: absolute;
  font-size: 1.5rem;
  cursor: pointer;
  transition: transform 0.3s ease-in-out;
}

.magnifier {
  position: absolute;
  cursor: pointer;
  font-size: 24px;
  transform: translate(-50%, -50%);
  animation: pulse 1.5s infinite;
}

@keyframes pulse {
  0% {
    transform: scale(1);
  }
  50% {
    transform: scale(1.2);
  }
  100% {
    transform: scale(1);
  }
}

.hint-toggle {
  position: fixed;
  top: 200px;
  right: 50px;
  background: rgba(0, 0, 0, 0.8);
  border: 2px solid #ffd700;
  border-radius: 8px;
  padding: 10px;
  z-index: 1000;
}

.hint-box {
  background: rgba(255, 255, 255, 0.9);
  padding: 10px;
  border-radius: 5px;
  color: black;
  margin-top: 5px;
}

.final-choice button {
  background-color: #007bff;
  color: white;
  border: none;
  padding: 10px 20px;
  margin: 5px;
  cursor: pointer;
  border-radius: 5px;
  font-weight: bold;
}

.action-button:hover {
  background-color: #45a049;
}

button {
  background-color: #ffd700;
  padding: 8px 12px;
  border: none;
  border-radius: 5px;
  cursor: pointer;
  font-weight: bold;
  margin: 5px;
}

.action-button {
  background-color: #4caf50;
  color: white;
  padding: 10px 20px;
  margin: 10px;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  transition: background 0.3s;
}

.timer {
  font-size: 1.5em;
  color: #ffd700;
  margin: 10px 0;
}

/* Classement des Meilleurs Joueurs */
.leaderboard {
  background-color: rgba(0, 0, 0, 0.8);
  color: white;
  padding: 15px;
  border: 2px solid #ffd700;
  border-radius: 8px;
  width: 250px;
  text-align: left;
  max-height: 80vh;
  overflow-y: auto;
  z-index: 1000;
  position: fixed;
  top: 200px;
  left: 50px;
}

.leaderboard h2 {
  color: #ffd700;
  font-size: 1.3rem;
  text-align: center;
  margin-bottom: 10px;
}

.leaderboard ul {
  list-style: none;
  padding: 0;
  margin: 0;
}

.leaderboard li {
  padding: 8px 0;
  border-bottom: 1px solid rgba(255, 255, 255, 0.2);
  font-size: 0.9rem;
}

.item {
  width: 20px;
  height: auto;
  position: absolute;
  cursor: pointer;
  transition: transform 0.3s ease-in-out;
}

.item:hover {
  transform: scale(1.2);
}

.hidden-interaction {
  position: absolute;
  width: 10px;
  height: 10px;
  cursor: pointer;
  opacity: 0;
}
</style>
