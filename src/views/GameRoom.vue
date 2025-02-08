<template>
  <div class="game-page">
    <h1 class="title">Choisissez votre Escape Game</h1>

    <div class="user-info" v-if="isLoggedIn">
      👤 Connecté en tant que : {{ username }}
    </div>

    <!-- ✅ Liste des jeux disponibles -->
    <div class="game-content">
      <div class="game-container">
        <div 
          v-for="(game, index) in games" 
          :key="game.id" 
          class="game-bubble-wrapper"
        >
          <!-- Label de version -->
          <div class="game-version">
            <p>{{ getVersionLabel(index) }}</p>
          </div>
          <div class="game-bubble" :style="{ backgroundImage: `url(${game.image})` }">
            <router-link :to="game.path" class="game-link">
              <div class="overlay">
                <h2>{{ game.title }}</h2>
                <p>{{ game.description }}</p>
              </div>
            </router-link>
          </div>
        </div>
      </div>

      <!-- ✅ Historique des parties de l'utilisateur connecté -->
      <div v-if="userHistory.length" class="history">
        <h2>📜 Votre historique de parties</h2>
        <ul>
          <li v-for="(game, index) in userHistory" :key="index">
            🎮 {{ game.game_name }} - {{ game.score }} points en {{ game.duration }}s - Joué le {{ new Date(game.played_at).toLocaleDateString() }}
          </li>
        </ul>
      </div>
    </div>
  </div>
</template>

<script>
import bibliothequeImage from "@/assets/bibliotheque.webp";
import laboratoireImage from "@/assets/laboratoire.webp";
import vaiseauImage from "@/assets/vaisseau.webp";

export default {
  name: "GameRoom",
  data() {
    return {
      games: [
        {
          id: 1,
          title: "La Bibliothèque Mystérieuse",
          description: "Trouvez les indices cachés pour sortir.",
          path: "/escape-game-1",
          image: bibliothequeImage,
        },
        {
          id: 2,
          title: "Le Laboratoire Abandonné",
          description: "Créez la potion parfaite pour vous échapper.",
          path: "/escape-game-2",
          image: laboratoireImage,
        },
        {
          id: 3,
          title: "Le Vaisseau Perdu",
          description: "Trouvez un moyen de rentrer sur Terre.",
          path: "/escape-game-3",
          image: vaiseauImage,
        },
      ],
      userHistory: [],
      isLoggedIn: false,
      username: "",
    };
  },
  mounted() {
    this.checkUserSession();
    if (this.isLoggedIn) {
      this.fetchUserHistory();
    }
  },
  methods: {
    // Vérifie si l'utilisateur est connecté
    checkUserSession() {
      const user = JSON.parse(localStorage.getItem("user"));
      if (user) {
        this.isLoggedIn = true;
        this.username = user.username;
      }
    },
    // Récupère l'historique des parties de l'utilisateur
    async fetchUserHistory() {
      const user = JSON.parse(localStorage.getItem("user"));
      if (!user || !user.id) {
        console.error("Utilisateur non connecté ou ID manquant.");
        return;
      }
      try {
        const response = await fetch(
          `http://localhost/escape-game/backend/getUserHistory.php?user_id=${user.id}`
        );
        const data = await response.json();
        if (Array.isArray(data)) {
          this.userHistory = data;
        } else {
          console.error("Erreur : données inattendues", data);
        }
      } catch (error) {
        console.error("Erreur lors de la récupération de l’historique :", error);
      }
    },
    // Retourne un label de version en fonction de l'index du jeu
    getVersionLabel(index) {
      switch (index) {
        case 0:
          return "Version 1 - Développement simple";
        case 1:
          return "Version 2 - Développement moyen";
        case 2:
          return "Version 3 - Développement avancé";
        default:
          return "";
      }
    },
  },
};
</script>

<style scoped>
/* ✅ Informations utilisateur */
.user-info {
  background: rgba(255, 255, 255, 0.1);
  padding: 10px 20px;
  border-radius: 8px;
  margin-bottom: 20px;
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
  font-weight: bold;
}

/* ✅ Fond général */
.game-page {
  background: linear-gradient(to bottom, #1a1a2e, #16213e);
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 20px;
  color: #ffffff;
  transition: background 0.5s ease-in-out;
  position: relative;
}

/* ✅ Conteneur principal */
.game-content {
  display: flex;
  justify-content: center;
  align-items: flex-start;  /* Alignement en haut */
  gap: 40px;
  width: 100%;
  max-width: 1400px;
  position: relative;
  margin-top: 200px; /* ✅ Décale les bulles vers le bas */
}

/* ✅ Conteneur des bulles */
.game-container {
  display: flex;
  gap: 20px;
  justify-content: center;
  align-items: center;
  flex: 1;
}

/* ✅ Bulles interactives */
.game-bubble {
  width: 220px;
  height: 220px;
  background-size: cover;
  background-position: center;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  position: relative;
  overflow: hidden;
  box-shadow: 0px 6px 15px rgba(0, 0, 0, 0.6);
  transition: transform 0.4s ease, box-shadow 0.4s ease;
}

.game-bubble:hover {
  transform: scale(1.15);
  box-shadow: 0px 8px 20px rgba(0, 0, 0, 0.8);
}

/* ✅ Historique des parties */
.history {
  background-color: rgba(255, 255, 255, 0.1);
  padding: 15px;
  border-radius: 8px;
  width: 300px;
  text-align: left;
  color: white;
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.5);
  position: absolute;
  right: -100px;               /* ✅ Décale l'historique un peu plus à droite */
  top: 50%;
  transform: translateY(-50%); /* ✅ Centre verticalement même si la hauteur change */
}

.history h2 {
  font-size: 1.4rem;
  margin-bottom: 10px;
  border-bottom: 2px solid #ffd700;
  text-align: center;
}

.history ul {
  list-style: none;
  padding: 0;
  margin: 0;
}

.history li {
  background: rgba(0, 0, 0, 0.6);
  padding: 10px;
  border-radius: 5px;
  margin-bottom: 5px;
  font-size: 1rem;
  text-align: left;
}

/* ✅ Effet au survol */
.overlay {
  background: rgba(0, 0, 0, 0.75);
  color: white;
  text-align: center;
  padding: 20px;
  border-radius: 50%;
  opacity: 0;
  transition: opacity 0.4s ease-in-out, transform 0.4s ease;
  width: 100%;
  height: 100%;
  display: flex;
  flex-direction: column;
  justify-content: center;
  transform: scale(0.9);
}

.game-bubble:hover .overlay {
  opacity: 1;
  transform: scale(1);
}

/* ✅ Titre du jeu */
.overlay h2 {
  font-size: 1.4rem;
  margin: 0;
  text-transform: uppercase;
  letter-spacing: 1px;
}

/* ✅ Description du jeu */
.overlay p {
  font-size: 0.9rem;
  margin-top: 5px;
  opacity: 0.9;
}

/* ✅ Liens */
.game-link {
  text-decoration: none;
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
}

/* ✅ Responsive Design */
@media (max-width: 768px) {
  .game-container {
    flex-direction: column;
    gap: 1.5rem;
  }

  .game-bubble {
    width: 180px;
    height: 180px;
  }

  .overlay h2 {
    font-size: 1.2rem;
  }

  .overlay p {
    font-size: 0.8rem;
  }

  .game-content {
    flex-direction: column;
  }

  .history {
    position: static;
    width: 90%;
    margin: 20px auto;
    transform: none;
  }
}
</style>