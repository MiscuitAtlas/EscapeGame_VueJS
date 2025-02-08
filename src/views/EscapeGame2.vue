<template>
  <div class="lab-game">
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
        <h1>Le Laboratoire Abandonné</h1>
        <p>Collectez des indices, expérimentez des formules et trouvez la bonne potion pour vous échapper !</p>

        <div class="timer">⏱️ Temps restant : {{ timer }}s</div>

        <div class="image-container">
          <img src="@/assets/laboratoire.webp" class="lab-image" alt="Laboratoire Abandonné" />
          <div class="magnifier" style="top: 70%; left: 26%;" @click="searchForIngredient(1)">🔍</div>
          <div class="magnifier" style="top: 45%; left: 41%;" @click="searchForIngredient(2)">🔍</div>
          <div class="magnifier" style="top: 53%; left: 78%;" @click="searchForIngredient(3)">🔍</div>
          <div class="magnifier" style="top: 44%; left: 17%;" @click="searchForIngredient(4)">🔍</div>
          <div class="magnifier" style="top: 20%; left: 63%;" @click="searchForIngredient(5)">🔍</div>
        </div>

        <div v-if="currentIngredient" class="ingredient-box">
          <p><strong>Ingrédient trouvé :</strong> {{ currentIngredient.name }}</p>
          <button @click="addToInventory(currentIngredient)">Ajouter à l'inventaire</button>
        </div>

        <div class="inventory">
          <h3>Inventaire des Ingrédients :</h3>
          <ul>
            <li v-for="item in inventory" :key="item.id">
              🧪 {{ item.name }} 
              <button @click="addToPotion(item)">➕ Ajouter à la potion</button>
            </li>
          </ul>
        </div>

        <div v-if="potionIngredients.length" class="potion-box">
          <h2>Créez votre potion :</h2>
          <p>Glissez-déposez les ingrédients dans le bon ordre :</p>
          <ul>
            <li v-for="(item, index) in potionIngredients" :key="item.id" 
                draggable="true" 
                @dragstart="startDrag(index)" 
                @dragover.prevent 
                @drop="dropItem(index)">
              {{ item.name }}
              <button @click="removeFromPotion(index)">❌</button>
            </li>
          </ul>
          <button @click="createPotion">Préparer la potion</button>
        </div>

        <div v-if="potionResult" class="final-choice">
          <h2>{{ potionResult }}</h2>
          <!-- Le score est affiché uniquement si la partie est gagnée -->
          <p v-if="isEscaped">🎯 Score final : {{ score }}</p>
          <button class="action-button" v-if="!isEscaped" @click="retryPotion">🔄 Réessayer</button>
          <button class="action-button" v-if="isEscaped" @click="restartGame">🎮 Rejouer</button>
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
      currentIngredient: null,
      inventory: [],
      potionIngredients: [],
      potionResult: '',
      isEscaped: false,
      correctPotion: ["Élixir rouge", "Poudre d'or", "Essence mystique"],
      draggedIndex: null,
      showHintTab: false,
      revealedHints: [],
      hints: [
        "L'élixir rouge est le premier ingrédient clé.",
        "La poudre d'or stabilise les réactions chimiques.",
        "L'essence mystique est nécessaire pour activer la magie."
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
        const response = await fetch(`http://localhost/escape-game/backend/getLeaderboard.php?game_id=2`);
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
            game_id: 2,
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
        console.error("Erreur lors de l'enregistrement de la partie:", error);
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
            this.restartGame();
          }, 3000);
        }
      }, 1000);
    },

    stopGame() {
      clearInterval(this.timerInterval);
      this.backgroundMusic.pause();
    },

    goGame() {
      this.stopGame();
      this.$router.push('/game-room');
    },

    toggleHintTab() {
      this.showHintTab = !this.showHintTab;
    },

    showHint(index) {
      if (!this.revealedHints.includes(this.hints[index])) {
        this.revealedHints.push(this.hints[index]);
        this.score -= this.hintPenalty;
      }
    },

    searchForIngredient(id) {
      const ingredients = [
        { id: 1, name: "Élixir rouge" },
        { id: 2, name: "Poudre d'or" },
        { id: 3, name: "Essence mystique" },
        { id: 4, name: "Fragment de cristal" },
        { id: 5, name: "Herbes fanées" }
      ];
      this.currentIngredient = ingredients.find(item => item.id === id);
    },

    addToInventory(ingredient) {
      if (!this.inventory.some(item => item.id === ingredient.id)) {
        this.inventory.push(ingredient);
      }
    },

    addToPotion(item) {
      if (!this.potionIngredients.includes(item)) {
        this.potionIngredients.push(item);
      }
    },

    startDrag(index) {
      this.draggedIndex = index;
    },

    dropItem(index) {
      const draggedItem = this.potionIngredients.splice(this.draggedIndex, 1)[0];
      this.potionIngredients.splice(index, 0, draggedItem);
      this.draggedIndex = null;
    },

    removeFromPotion(index) {
      this.potionIngredients.splice(index, 1);
    },

    createPotion() {
      const ingredientNames = this.potionIngredients.map(item => item.name);
      if (JSON.stringify(ingredientNames) === JSON.stringify(this.correctPotion)) {
        this.potionResult = "🚀 Bravo ! Vous avez créé la potion de l'évasion ultime ! 🎉";
        this.isEscaped = true;
        this.backgroundMusic.pause();
        this.correctSound.play();
        clearInterval(this.timerInterval);
        this.calculateScore();
        this.saveGameResult();
      } else {
        this.potionResult = "❌ Ce n'était pas la bonne potion. Essayez encore ! ❌";
        this.isEscaped = false;
        this.wrongSound.play();
      }
    },

    calculateScore() {
      this.score = (this.timer * 10) - (this.revealedHints.length * this.hintPenalty);
    },

    retryPotion() {
      this.potionIngredients = [];
      this.potionResult = '';
    },

    restartGame() {
      this.stopGame();
      this.currentIngredient = null;
      this.inventory = [];
      this.potionIngredients = [];
      this.potionResult = '';
      this.isEscaped = false;
      this.revealedHints = [];
      this.timer = 180;
      this.backgroundMusic.play();
      this.startTimer();
    }
  },

  beforeRouteLeave(to, from, next) {
    this.stopGame();
    next();
  }
};
</script>

<style scoped>
/* ✅ Conteneur principal */
.lab-game {
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

/* ✅ Zone de Jeu (centrée) */
.game-area {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  text-align: center;
  flex-grow: 1;
}

/* ✅ Conteneur de l'image */
.image-container {
  position: relative;
  display: flex;
  justify-content: center;
  align-items: center;
  max-width: 800px;
  width: 100%;
  margin: auto;
}

.lab-image {
  width: 100%;
  max-width: 800px;
  border: 3px solid white;
  border-radius: 10px;
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

/* ✅ Classement des Meilleurs Joueurs */
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
