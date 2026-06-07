<template>
    <div dir="">
        <div class="flex justify-center items-start bg-gray-200 w-[30%] h-[85vh]  border-style: solid; rounded-s-lg ">
        
            <div class="mt-15 flex flex-col">
                <h1 class="text-3xl font-bold text-center text-orange-600">MODIFICATION</h1>
                <form class="space-y-5 p-10 ">
                        <div >
                            <div>
                                <label for="num"
                                class="block mb-2 text-sm font-medium text-gray-700 text-left"
                                >IDClient : </label>
                                <input type="text" placeholder="CLI001"
                                class="text-left w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                                >
                            </div>
                            <div>
                                <label for="nom" 
                                class="block mb-2 text-sm font-medium text-gray-700 text-left"
                                >Nom du Client :</label>
                                <input type="text" placeholder="Rakoto"
                                class="text-left w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                                >
                            </div>
                            <div>
                                <label for="Contact"
                                class="block mb-2 text-sm font-medium text-gray-700 text-left"
                                >Contact : </label>
                                <input type="text" placeholder=" +2613********"
                                class="text-left w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-transparent"
                                >
                            </div>
                        </div>
                        <button
                            @click="$router.push('/a')"
                            class="w-full py-3 font-semibold text-white 
                            transition bg-gradient-to-r from-green-500 
                            to-green-400 p-8 rounded-lg text-white rounded-lg hover:bg-blue-700"
                        >
                            MODIFIER
                        </button>
                        <span>
                           <button
                          @click="$router.push('/a')"
                            class="w-full py-3 font-semibold text-white 
                            transition bg-gradient-to-r from-red-500 
                            to-orange-600 p-8 rounded-lg text-white rounded-lg hover:bg-blue-700"
                            >
                            ANNULER
                             </button>
                        </span>
                </form>  
                <div id="mesage">
                    <h2>  </h2>
                </div>
            </div>
        </div>
    </div>
</template>