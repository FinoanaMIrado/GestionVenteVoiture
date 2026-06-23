import { createRouter, createWebHistory } from 'vue-router'
import Acceuil from '../src/views/acceuille.vue'
import Voiture from '../src/views/voiture.vue'
import Client from '../src/views/client.vue'
import Achat from '../src/views/achat.vue'
import Facture from '../src/views/facture.vue'
// import Bilan from '../src/views/bilan.vue'
import Login from '../src/views/login.vue'
import Layoutpardefaut from '../layout/layoutpardefaut.vue'
// import { Children } from 'vue'
import Layoutloginy from '../layout/layoutloginy.vue'


const routes = [
  //Loginy
    {path:'/',
    component: Layoutloginy,
    children:[
        {
         path:'',
         component:Login,
        }
    ]
    }   ,
    //Afisazy
   {

    path: '/a',
    component: Layoutpardefaut,
    children :
     [{
      path: '',
      redirect: '/acc'   
      },
      {
      path: '/acc',
      component: Acceuil,
      },
      {
      path: '/voiture',
      component: Voiture,
      },
      {
      path: '/client',
      component: Client,
      },
      {
      path: '/achat',
      component: Achat,
      },
      {
      path: '/facture/:id',
      component: Facture,
      },
      
     ]
   }
]


const router = createRouter({
  history: createWebHistory(),
  routes,
})


export default router