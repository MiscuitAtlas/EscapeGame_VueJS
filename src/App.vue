<template>
  <div id="app">
    <!-- ✅ Navbar avec menu déroulant pour Game Room -->
    <header class="navbar">
      <nav>
        <router-link to="/" class="nav-link" @click="logout">Accueil</router-link>

        <!-- ✅ Menu déroulant pour Game Room -->
        <div class="dropdown">
          <router-link to="/game-room" class="nav-link">Game Room ▾</router-link>
          <div class="dropdown-content">
            <router-link to="/escape-game-1" class="dropdown-link">La Bibliothèque Mystérieuse</router-link>
            <router-link to="/escape-game-2" class="dropdown-link">Le Laboratoire Abandonné</router-link>
            <router-link to="/escape-game-3" class="dropdown-link">Le Vaisseau Perdu</router-link>
          </div>
        </div>

        <!-- ✅ Connexion/Déconnexion à droite -->
        <div class="auth-buttons">
          <template v-if="isLoggedIn">
            👤 {{ username }}
            <button @click="logout" class="btn-logout">Se déconnecter</button>
          </template>
          <template v-else>
            <router-link to="/login" class="nav-link">Connexion</router-link>
            <router-link to="/register" class="nav-link">Inscription</router-link>
          </template>
        </div>
      </nav>
    </header>

    <!-- ✅ Contenu principal -->
    <main class="content">
      <router-view></router-view>
    </main>

    <!-- ✅ Footer -->
    <footer class="footer">
      <p>&copy; 2024 Escape Game - Tous droits réservés.</p>
    </footer>
  </div>
</template>

<script>
export default {
  name: 'App',
  data() {
    return {
      isLoggedIn: false,
      username: ''
    };
  },
  mounted() {
    this.checkUserSession();
  },
  methods: {
    checkUserSession() {
      const user = localStorage.getItem('user');
      if (user) {
        try {
          const parsedUser = JSON.parse(user);
          if (parsedUser && parsedUser.username) {
            this.isLoggedIn = true;
            this.username = parsedUser.username;
          }
        } catch (error) {
          console.error('Erreur lors de la récupération de l’utilisateur :', error);
          localStorage.removeItem('user'); // Nettoyage des données corrompues
        }
      }
    },

    logout() {
      localStorage.removeItem('user'); // ✅ Suppression de la session utilisateur
      this.isLoggedIn = false;
      this.username = '';
      this.$router.push('/'); // ✅ Redirection vers la page d'accueil
    }
  }
};
</script>

<style scoped>
/* ✅ Styles globaux */
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

body {
  font-family: 'Arial', sans-serif;
  background-color: #f4f4f4;
  display: flex;
  flex-direction: column;
  min-height: 100vh;
}

/* ✅ Navbar */
.navbar {
  background-color: #004d99;
  color: white;
  padding: 15px 20px;
  display: flex;
  justify-content: space-between;
  align-items: center;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.5);
  position: relative;
  width: 100%;
  z-index: 1000;
}

.navbar nav {
  display: flex;
  align-items: center;
  justify-content: space-between;
  width: 100%;
}

/* ✅ Liens de navigation */
.nav-link {
  color: white;
  text-decoration: none;
  font-weight: bold;
  transition: background-color 0.3s, color 0.3s;
  padding: 8px 12px;
  border-radius: 5px;
  position: relative;
}

.nav-link:hover {
  background-color: #007acc;
  color: white;
}

/* ✅ Menu déroulant */
.dropdown {
  position: relative;
  display: inline-block;
}

.dropdown-content {
  display: none;
  position: absolute;
  background-color: #004d99;
  min-width: 200px;
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.5);
  z-index: 1;
  border-radius: 5px;
  overflow: hidden;
  transition: all 0.3s ease-in-out;
}

.dropdown-link {
  color: white;
  padding: 10px 15px;
  display: block;
  text-decoration: none;
  transition: background-color 0.3s ease;
}

.dropdown-link:hover {
  background-color: #007acc;
}

/* ✅ Affichage du menu au survol */
.dropdown:hover .dropdown-content {
  display: block;
}

/* ✅ Connexion à droite */
.auth-buttons {
  margin-left: auto;
  display: flex;
  align-items: center;
  gap: 10px;
}

/* ✅ Bouton de déconnexion */
.btn-logout {
  background-color: #e74c3c;
  border: none;
  color: white;
  padding: 8px 15px;
  border-radius: 5px;
  cursor: pointer;
  transition: background-color 0.3s;
}

.btn-logout:hover {
  background-color: #c0392b;
}

/* ✅ Contenu principal */
.content {
  flex: 1;
  padding: 20px;
  min-height: 80vh;
}

/* ✅ Footer */
.footer {
  background-color: #004d99;
  color: white;
  text-align: center;
  padding: 15px 0;
  width: 100%;
  font-size: 0.9rem;
  box-shadow: 0 -2px 4px rgba(0, 0, 0, 0.5);
}

/* ✅ Responsive Design */
@media (max-width: 768px) {
  .navbar nav {
    flex-direction: column;
    gap: 10px;
  }

  .auth-buttons {
    margin-top: 10px;
  }
}
</style>
