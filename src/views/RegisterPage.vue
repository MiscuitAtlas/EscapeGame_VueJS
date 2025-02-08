<template>
  <div class="register">
    <div class="register-container">
      <h1>S'inscrire</h1>
      <form @submit.prevent="register" class="register-form">
        <input
          type="text"
          v-model="username"
          placeholder="Nom d'utilisateur"
          required
          class="input-field"
        />
        <input
          type="email"
          v-model="email"
          placeholder="Email"
          required
          class="input-field"
        />
        <input
          type="password"
          v-model="password"
          placeholder="Mot de passe"
          required
          class="input-field"
        />
        <button type="submit" class="btn btn-primary" :disabled="isLoading">
          {{ isLoading ? "Inscription en cours..." : "S'inscrire" }}
        </button>
      </form>
      <p v-if="message" :class="messageClass">{{ message }}</p>
      <div class="login-link">
        <p>Déjà inscrit ? <router-link to="/login">Se connecter ici</router-link></p>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'RegisterPage',
  data() {
    return {
      username: '',
      email: '',
      password: '',
      message: '',
      messageClass: '',
      isLoading: false,
    };
  },
  methods: {
    async register() {
      this.isLoading = true;
      this.message = '';
      try {
        const response = await fetch('http://localhost/escape-game/backend/register.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
          },
          body: JSON.stringify({
            username: this.username,
            email: this.email,
            password: this.password,
          }),
        });

        const data = await response.json();
        console.log('Réponse du serveur:', data);

        if (data.success) {
          this.message = data.message || 'Inscription réussie !';
          this.messageClass = 'success';

          const user = {
            username: this.username,
            email: this.email,
          };

          // ✅ Sauvegarde de l'utilisateur dans le localStorage
          localStorage.setItem('user', JSON.stringify(user));

          // ✅ Mise à jour de l'état global de l'utilisateur
          this.$root.isLoggedIn = true;
          this.$root.username = user.username;

          // ✅ Redirection vers la Game Room
          this.$router.push('/game-room');
        } else {
          this.message = data.error || 'Une erreur est survenue.';
          this.messageClass = 'error';
        }
      } catch (error) {
        console.error('Erreur lors de l’inscription :', error);
        this.message = 'Une erreur est survenue. Veuillez réessayer.';
        this.messageClass = 'error';
      } finally {
        this.isLoading = false;
      }
    },
  },
};
</script>

<style scoped>
/* ✅ Styles pour la page d'inscription */
.register {
  display: flex;
  justify-content: center;
  align-items: center;
  height: 100vh;
  background: linear-gradient(135deg, #00c6ff, #0072ff);
  font-family: 'Arial', sans-serif;
  color: white;
}

.register-container {
  background-color: rgba(0, 0, 0, 0.6);
  padding: 40px 50px;
  border-radius: 10px;
  box-shadow: 0px 4px 15px rgba(0, 0, 0, 0.3);
  width: 100%;
  max-width: 400px;
  text-align: center;
}

h1 {
  font-size: 2.5rem;
  margin-bottom: 20px;
}

.register-form {
  display: flex;
  flex-direction: column;
  gap: 15px;
}

.input-field {
  padding: 12px;
  font-size: 1rem;
  border-radius: 8px;
  border: 1px solid #ccc;
  margin-bottom: 20px;
  width: 100%;
  background-color: #fff;
}

.input-field:focus {
  outline: none;
  border-color: #0072ff;
  box-shadow: 0 0 8px rgba(0, 114, 255, 0.5);
}

.btn {
  padding: 15px;
  font-size: 1rem;
  font-weight: bold;
  border-radius: 8px;
  color: white;
  border: none;
  transition: background-color 0.3s;
  cursor: pointer;
}

.btn-primary {
  background-color: #0072ff;
}

.btn-primary:hover {
  background-color: #005bb5;
}

.btn[disabled] {
  background-color: #999;
  cursor: not-allowed;
}

p {
  font-size: 1.1rem;
  margin-top: 20px;
}

.success {
  color: #28a745;
}

.error {
  color: #dc3545;
}

.login-link {
  margin-top: 20px;
  font-size: 0.9rem;
}

.login-link a {
  color: #ffd700;
  font-weight: bold;
  text-decoration: none;
}

.login-link a:hover {
  text-decoration: underline;
}

/* ✅ Responsive Design */
@media (max-width: 768px) {
  .register-container {
    padding: 30px 40px;
  }

  h1 {
    font-size: 2rem;
  }
}
</style>
