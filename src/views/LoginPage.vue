<template>
  <div class="login">
    <div class="login-container">
      <h1>Se connecter</h1>
      <form @submit.prevent="login" class="login-form">
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
          {{ isLoading ? "Connexion en cours..." : "Se connecter" }}
        </button>
      </form>

      <!-- Message d'erreur ou de succès -->
      <p v-if="message" :class="messageClass">{{ message }}</p>

      <div class="register-link">
        <p>Pas encore inscrit ? <router-link to="/register">S'inscrire ici</router-link></p>
        <p>
          <a href="#" @click.prevent="forgotPassword" class="forgot-password">Mot de passe oublié ?</a>
        </p>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'LoginPage',
  data() {
    return {
      email: '',
      password: '',
      message: '',
      messageClass: '',
      isLoading: false,
    };
  },
  methods: {
    async login() {
      this.isLoading = true;
      this.message = '';
      try {
        const response = await fetch('http://localhost/escape-game/backend/login.php', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
          },
          body: JSON.stringify({
            email: this.email,
            password: this.password,
          }),
        });

        const data = await response.json();
        console.log('Réponse du serveur:', data);

        if (data.success) {
          this.message = data.message || 'Connexion réussie !';
          this.messageClass = 'success';

          const user = {
              id: data.id,                  // ✅ Stocker l'ID de l'utilisateur
              username: data.username,
              email: this.email
          };

          // ✅ Sauvegarde de l'utilisateur dans le localStorage
          localStorage.setItem('user', JSON.stringify(user));

          // ✅ Mise à jour de l'état global
          this.$root.isLoggedIn = true;
          this.$root.username = user.username;

          // ✅ Redirection vers la Game Room
          this.$router.push('/game-room');
        } else {
          this.message = data.error || 'Email ou mot de passe incorrect.';
          this.messageClass = 'error';
        }
      } catch (error) {
        console.error('Erreur lors de la connexion :', error);
        this.message = 'Une erreur est survenue. Veuillez réessayer.';
        this.messageClass = 'error';
      } finally {
        this.isLoading = false;
      }
    },

    async forgotPassword() {
      const userEmail = prompt("Veuillez entrer votre adresse email :");

      if (userEmail) {
        const newPassword = prompt("Veuillez entrer votre nouveau mot de passe :");

        if (newPassword) {
          try {
            const response = await fetch('http://localhost/escape-game/backend/reset_password.php', {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json',
              },
              body: JSON.stringify({
                email: userEmail,
                newPassword: newPassword,
              }),
            });

            const data = await response.json();
            console.log('Réponse du serveur:', data);

            if (data.success) {
              alert("Mot de passe réinitialisé avec succès !");
            } else {
              alert(data.error || "Erreur lors de la réinitialisation du mot de passe.");
            }
          } catch (error) {
            console.error('Erreur lors de la réinitialisation :', error);
            alert("Une erreur est survenue. Veuillez réessayer.");
          }
        }
      }
    },
  },
};
</script>

<style scoped>
/* ✅ Styles pour la page de login */
.login {
  display: flex;
  justify-content: center;
  align-items: center;
  height: 100vh;
  background: linear-gradient(135deg, #00c6ff, #0072ff);
  font-family: 'Arial', sans-serif;
  color: white;
}

.login-container {
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

.login-form {
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
  margin-top: 15px;
}

.success {
  color: #28a745;
}

.error {
  color: #dc3545;
}

.register-link {
  margin-top: 20px;
  font-size: 0.9rem;
}

.register-link a {
  color: #ffd700;
  font-weight: bold;
  text-decoration: none;
  cursor: pointer;
}

.register-link a:hover {
  text-decoration: underline;
}

.forgot-password {
  color: #ffa500;
  cursor: pointer;
}

/* Responsive */
@media (max-width: 768px) {
  .login-container {
    padding: 30px 40px;
  }

  h1 {
    font-size: 2rem;
  }
}
</style>
