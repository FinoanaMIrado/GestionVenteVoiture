<template>
  <div class="flex items-center justify-center min-h-screen bg-gray-100">
    <div class="w-full max-w-md p-8 bg-white rounded-xl shadow-lg">
      <div className="flex justify-center items-start">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" 
        width="80"
        height="80"
        strokeWidth={1.5} stroke="currentColor" className="size-3">
        <path strokeLinecap="round" strokeLinejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
        </svg><br>


      </div>
      <h1 class="mb-6 text-3xl font-bold text-center text-orange-600">
        Authentification
      </h1>

      <form class="space-y-5"> 
        <div>
          <label
            for="nomUser"
            class="block mb-2 text-sm font-medium text-gray-700"
          >
            Nom d'utilisateur
          </label>
          <input
            type="text"
            id="nomUser"
            required
            placeholder="Rakotobe"
            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
          />
        </div>

        <div>
          <label
            for="password"
            class="block mb-2 text-sm font-medium text-gray-700"
          >
            Mot de passe
          </label>
          <input
            type="password"
            id="password"
            required
            placeholder="********"
            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
          />
        </div>

        <button
          @click="$router.push('/a')"
          class="w-full py-3 font-semibold text-white transition bg-gradient-to-r from-yellow-500 to-orange-600 p-8 rounded-lg text-white rounded-lg hover:bg-blue-700"
        >
          Se connecter
        </button>
      </form>
    </div>
  </div>
</template>