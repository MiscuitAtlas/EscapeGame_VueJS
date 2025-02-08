<template>
  <div class="escape-game">
    <div class="game-layout"> 
      
      <!-- ✅ Classement des Meilleurs Joueurs à gauche -->
      <div class="leaderboard">
        <h2>🏆 Classement des Meilleurs Joueurs</h2>
        <ul>
          <li v-for="(player, index) in leaderboard" :key="index">
            {{ index + 1 }}. {{ player.username }} - {{ player.score }} pts en {{ player.duration }}s
          </li>
        </ul>
      </div>

      <!-- ✅ Zone de Jeu au centre -->
      <div class="game-area">
        <h1>La Bibliothèque Mystérieuse</h1>
        <p>Trouvez des indices pour sortir de la pièce !</p>

        <div class="timer">⏱️ Temps restant : {{ timer }}s</div>

        <div class="image-container">
          <img src="@/assets/bibliotheque.webp" usemap="#escape-map" class="game-image" />
          <map name="escape-map">
            <area shape="rect" coords="150,200,250,300" @click="revealPuzzle('livre')" alt="Livre mystérieux" />
            <area shape="rect" coords="300,100,400,200" @click="revealPuzzle('vitrail')" alt="Vitrail lumineux" />
            <area shape="rect" coords="350,400,500,500" @click="revealPuzzle('coffre')" alt="Coffre verrouillé" />
            <area shape="rect" coords="600,100,700,200" @click="revealPuzzle('bougie')" alt="Bougie fondante" />
            <area shape="rect" coords="450,250,550,350" @click="revealPuzzle('porte')" alt="Porte secrète" />
          </map>

          <div class="magnifier" style="top: 31%; left: 77%;" @click="revealPuzzle('livre')">🔍</div>
          <div class="magnifier" style="top: 15%; left: 49%;" @click="revealPuzzle('vitrail')">🔍</div>
          <div class="magnifier" style="top: 60%; left: 32%;" @click="revealPuzzle('coffre')">🔍</div>
          <div class="magnifier" style="top: 70%; left: 7%;" @click="revealPuzzle('bougie')">🔍</div>
          <div class="magnifier" style="top: 50%; left: 49%;" @click="revealPuzzle('porte')">🔍</div>
        </div>

        <!-- Bloc de feedback affiché sous l'image -->
        <transition name="fade">
          <div v-if="feedbackMessage" class="feedback">{{ feedbackMessage }}</div>
        </transition>

        <div v-if="currentPuzzle" class="puzzle-box">
          <p>{{ currentPuzzle.text }}</p>
          <input v-if="currentPuzzle.answer" v-model="userAnswer" placeholder="Votre réponse ici" />
          <button @click="checkAnswer">Valider</button>
        </div>

        <div v-if="isEscapeComplete && showFinalChoice" class="final-choice">
          <p>🚀 Bien joué ! Vous avez réussi à sortir ! 🎉</p>
          <p>🎯 Score final : {{ score }}</p>
          <button class="action-button" @click="replay">🎮 Rejouer</button>
          <button class="action-button" @click="goGame">🏠 Retour à l'accueil</button>
        </div>
      </div>

      <!-- ✅ Indices à droite -->
      <div class="hint-toggle">
        <button @click="toggleHintTab">🗒️ Indices</button>
        <transition name="fade">
          <div v-if="showHintTab" class="hint-box">
            <h3>Indices :</h3>
            <button @click="showHint(0)">Indice 1</button>
            <button @click="showHint(1)">Indice 2</button>
            <button @click="showHint(2)">Indice 3</button>
            <button @click="showHint(3)">Indice 4</button>
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
      currentPuzzle: null,
      userAnswer: '',
      solvedPuzzles: [],
      isEscapeComplete: false,
      showFinalChoice: false,
      showHintTab: false,
      revealedHints: [],
      hints: [
        "Le livre cache un secret fragile, lisez entre les lignes pour le découvrir.",
        "Le vent souffle sur le vitrail, révélant une inscription cachée.",
        "Le coffre semble verrouillé, mais pensez à ce qui brille dans la nuit.",
        "La bougie fondante révèle un indice caché dans l'ombre. Regardez ce qui disparaît."
      ],
      leaderboard: [],
      isLoggedIn: false,
      timer: 180,
      score: 0,
      hintPenalty: 100,
      timerInterval: null,
      correctSound: new Audio(require('@/assets/correct-answer.mp3')),
      wrongSound: new Audio(require('@/assets/wrong-answer.mp3')),
      backgroundMusic: new Audio(require('@/assets/background-music.mp3')),
      feedbackMessage: ""
    };
  },
  mounted() {
    this.startTimer();
    this.isLoggedIn = !!localStorage.getItem('user');
    this.fetchLeaderboard();
    document.addEventListener('click', this.startBackgroundMusic, { once: true });
    window.addEventListener('popstate', this.cleanup);
    window.addEventListener('beforeunload', this.cleanup);
  },
  beforeUnmount() {
    this.cleanup();
    window.removeEventListener('popstate', this.cleanup);
    window.removeEventListener('beforeunload', this.cleanup);
  },
  methods: {
    cleanup() {
      clearInterval(this.timerInterval);
      this.backgroundMusic.pause();
    },
    startBackgroundMusic() {
      this.backgroundMusic.loop = true;
      this.backgroundMusic.volume = 0.5;
      this.backgroundMusic.play().catch(error => {
        console.warn("Erreur lors de la lecture de la musique :", error);
      });
    },
    async fetchLeaderboard() {
      try {
        const response = await fetch(`http://localhost/escape-game/backend/getLeaderboard.php?game_id=1`);
        this.leaderboard = await response.json();
      } catch (error) {
        console.error('Erreur lors de la récupération du classement :', error);
      }
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
            game_id: 1,
            score: this.score,
            time_remaining: this.timer
          })
        });
        const data = await response.json();
        if (data.success) {
          console.log("✅ Partie enregistrée :", data.message);
        } else {
          console.error("❌ Erreur :", data.message);
        }
      } catch (error) {
        console.error("Erreur lors de l’enregistrement de la partie:", error);
      }
    },
    startTimer() {
      this.timerInterval = setInterval(() => {
        if (this.timer > 0) {
          this.timer--;
        } else {
          clearInterval(this.timerInterval);
          this.feedbackMessage = "⏰ Temps écoulé ! La partie est terminée.";
          setTimeout(() => {
            this.feedbackMessage = "";
            this.replay();
          }, 3000);
        }
      }, 1000);
    },
    playSound(sound) {
      sound.currentTime = 0;
      sound.play();
    },
    revealPuzzle(item) {
      const puzzles = {
        livre: { text: "Un livre ancien : 'Je peux être brisé sans être tenu. Que suis-je ?'", answer: "unsecret" },
        vitrail: { text: "Vitrail lumineux : 'Je suis invisible, mais vous me sentez. Que suis-je ?'", answer: "levent" },
        coffre: { text: "Coffre verrouillé : 'Je commence la nuit et termine le matin.'", answer: "uneetoile" },
        bougie: { text: "Bougie fondante : 'Plus je deviens grand, moins je suis visible.'", answer: "uneombre" },
        porte: {
          text: "La porte est verrouillée. Résolvez toutes les énigmes pour l'ouvrir.",
          action: () => {
            if (this.isEscapeComplete) {
              this.showFinalChoice = true;
              this.cleanup();
              this.calculateScore();
              this.saveGameResult();
            } else {
              this.feedbackMessage = "Vous devez résoudre toutes les énigmes !";
              setTimeout(() => { this.feedbackMessage = ""; }, 2000);
            }
          }
        }
      };

      if (puzzles[item].action) {
        puzzles[item].action();
      } else {
        this.currentPuzzle = puzzles[item];
      }
    },
    checkAnswer() {
      if (this.currentPuzzle && this.normalizeAnswer(this.userAnswer) === this.currentPuzzle.answer) {
        this.playSound(this.correctSound);
        this.feedbackMessage = "✅ Bonne réponse !";
        setTimeout(() => { this.feedbackMessage = ""; }, 1500);
        this.solvedPuzzles.push(this.currentPuzzle);
        this.currentPuzzle = null;
        this.userAnswer = '';
        if (this.solvedPuzzles.length === 4) {
          this.isEscapeComplete = true;
        }
      } else {
        this.playSound(this.wrongSound);
        this.feedbackMessage = "❌ Mauvaise réponse. Essayez encore !";
        setTimeout(() => { this.feedbackMessage = ""; }, 1500);
      }
    },
    normalizeAnswer(answer) {
      return answer.toLowerCase()
        .normalize("NFD")
        .replace(/\p{Diacritic}/gu, "")
        .replace(/\s+/g, '')
        .replace(/[^a-z0-9]/gi, '');
    },
    // Ajout de calculateScore pour corriger l'erreur "this.calculateScore is not a function"
    calculateScore() {
      this.score = (this.timer * 10) - (this.revealedHints.length * this.hintPenalty);
    },
    replay() {
      this.cleanup();
      this.currentPuzzle = null;
      this.userAnswer = '';
      this.solvedPuzzles = [];
      this.isEscapeComplete = false;
      this.showFinalChoice = false;
      this.revealedHints = [];
      this.timer = 180;
      this.startBackgroundMusic();
      this.startTimer();
    },
    goGame() {
      this.cleanup();
      this.$router.push('/game-room');
    },
    toggleHintTab() {
      this.showHintTab = !this.showHintTab;
    },
    showHint(index) {
      const hint = this.hints[index];
      if (!this.revealedHints.includes(hint)) {
        this.revealedHints.push(hint);
        this.score -= this.hintPenalty;
      }
    }
  }
};
</script>

<style scoped>
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

/* Image centrée */
.game-image {
  width: 100%;
  max-width: 800px;
  border: 3px solid #fff;
  border-radius: 10px;
  display: block;
  margin: 0 auto;
}

/* Timer */
.timer {
  font-size: 1.5em;
  color: #ffd700;
  margin: 10px 0;
  text-align: center;
}

.magnifier {
  position: absolute;
  cursor: pointer;
  font-size: 24px;
  transform: translate(-50%, -50%);
  animation: pulse 1.5s infinite;
}

@keyframes pulse {
  0% { transform: scale(1); }
  50% { transform: scale(1.2); }
  100% { transform: scale(1); }
}

.puzzle-box {
  background-color: rgba(0, 0, 0, 0.8);
  padding: 15px;
  border: 2px solid #ffd700;
  border-radius: 8px;
  margin-top: 20px;
  display: inline-block;
  max-width: 400px;
}

.puzzle-box input {
  margin-top: 10px;
  padding: 8px;
  border: none;
  border-radius: 5px;
  width: 80%;
}

.puzzle-box button {
  background-color: #ffd700;
  color: black;
  border: none;
  padding: 10px 15px;
  margin-top: 10px;
  cursor: pointer;
  border-radius: 5px;
  font-weight: bold;
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

/* Overlay pour bloquer les interactions */
.overlay {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.5);
  z-index: 10;
}

/* Feedback visuel */
.feedback {
  margin: 10px 0;
  padding: 5px 10px;
  background: #eef;
  border: 1px solid #99c;
  border-radius: 4px;
  color: #000;
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

/* Leaderboard */
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

/* Transitions */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.5s;
}
.fade-enter,
.fade-leave-to {
  opacity: 0;
}
</style>
