import { createRouter, createWebHistory } from 'vue-router';

// Import des vues
import HomePage from '../views/HomePage.vue';
import GameRoom from '../views/GameRoom.vue';
import LoginPage from '../views/LoginPage.vue';
import RegisterPage from '../views/RegisterPage.vue';
import EscapeGame1 from '../views/EscapeGame1.vue';
import EscapeGame2 from '../views/EscapeGame2.vue';
import EscapeGame3 from '../views/EscapeGame3.vue';

const routes = [
  {
    path: '/',
    name: 'home',
    component: HomePage,
  },
  {
    path: '/game-room',
    name: 'game-room',
    component: GameRoom,
    meta: { requiresAuth: true },
  },
  {
    path: '/login',
    name: 'login',
    component: LoginPage,
  },
  {
    path: '/register',
    name: 'register',
    component: RegisterPage,
  },
  {
    path: '/escape-game-1',
    name: 'escape-game-1',
    component: EscapeGame1,
    meta: { requiresAuth: true },
  },
  {
    path: '/escape-game-2',
    name: 'escape-game-2',
    component: EscapeGame2,
    meta: { requiresAuth: true },
  },
  {
    path: '/escape-game-3',
    name: 'escape-game-3',
    component: EscapeGame3,
    meta: { requiresAuth: true },
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

router.beforeEach((to, from, next) => {
  const user = localStorage.getItem('user');

  if (user) {
    try {
      const parsedUser = JSON.parse(user);
      if (!parsedUser) {
        throw new Error('Données utilisateur invalides.');
      }
    } catch (error) {
      console.error('Erreur lors du parsing de l’utilisateur :', error);
      localStorage.removeItem('user'); // Nettoyage des données corrompues
    }
  }

  next();
});

export default router;
