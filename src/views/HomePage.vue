<template>
  <div class="home">
    <div class="home-section">
      <h1 class="title">Bienvenue sur l'Escape Game !</h1>
      <p class="subtitle">Testez vos compétences en résolution d'énigmes dans des jeux immersifs et palpitants !</p>
      <div class="button-container">
        <router-link to="/game-room" class="btn btn-primary btn-lg">Choisir un jeu</router-link>
        <router-link to="/login" class="btn btn-secondary btn-lg">Se connecter</router-link>
        <router-link to="/register" class="btn btn-info btn-lg">S'inscrire</router-link>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'HomePage',
};
</script>

<style scoped>
/* Styles pour la page d'accueil */
.home {
  display: flex;
  flex-direction: column;
  height: 100vh;
  justify-content: space-between;
  align-items: center;
  background: linear-gradient(135deg, #f06, #00c6ff);
  color: white;
  font-family: 'Arial', sans-serif;
  text-align: center;
  padding: 20px;
}

/* Home section */
.home-section {
  margin-top: 50px;
  text-align: center;
}

.title {
  font-size: 3rem;
  font-weight: bold;
  margin-bottom: 20px;
}

.subtitle {
  font-size: 1.25rem;
  margin-bottom: 40px;
  opacity: 0.8;
}

.button-container {
  display: flex;
  justify-content: center;
  gap: 20px;
}

.btn {
  padding: 15px 30px;
  font-size: 1rem;
  font-weight: bold;
  border-radius: 8px;
  transition: transform 0.3s ease;
  box-shadow: 0px 4px 12px rgba(0, 0, 0, 0.2);
}

.btn:hover {
  transform: scale(1.1);
}

.btn-primary {
  background-color: #4CAF50;
  border: none;
  color: white;
}

.btn-primary:hover {
  background-color: #45a049;
}

.btn-secondary {
  background-color: #f0ad4e;
  border: none;
  color: white;
}

.btn-secondary:hover {
  background-color: #ec971f;
}

.btn-info {
  background-color: #5bc0de;
  border: none;
  color: white;
}

.btn-info:hover {
  background-color: #31b0d5;
}

/* Responsive */
@media (max-width: 768px) {
  .title {
    font-size: 2.5rem;
  }
  
  .subtitle {
    font-size: 1rem;
  }

  .btn {
    font-size: 0.9rem;
    padding: 12px 25px;
  }
}
</style>
