<div>
    {{-- Stop trying to control. --}}
    <div class="{{$winner ? "" : "hidden"}} fixed z-10 inset-0 overflow-y-auto">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">

            <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
            </div>

            <!-- This element is to trick the browser into centering the modal contents. -->
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full" role="dialog" aria-modal="true" aria-labelledby="modal-headline">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-green-100 sm:mx-0 sm:h-10 sm:w-10">
                            <!-- Heroicon name: outline/exclamation -->
                            <i class="fa fa-play text-green-700"></i>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-headline">
                                {{$winner}} won!!! Thanks everyone for playing
                            </h3>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <a href="/home" type="button" id="start-game" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-900 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Go Home
                    </a>
                </div>
            </div>
        </div>
    </div>
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

        <div class="mt-10 sm:mt-0">
            <div class="md:grid md:grid-cols-3 md:gap-6">

                <div class="mt-5 md:mt-0 md:col-span-3">

                    <div class="justify-center w-100 p-5 bg-white grid">
                        @if($isOwner)
                            <form action="/game/complete/{{$game->id}}" method="POST">
                                @csrf
                                <div class="shadow overflow-hidden sm:rounded-md">
                                    <div class="px-4 py-5 bg-white sm:p-6">
                                        <div class="grid grid-cols-6 gap-6">
                                            <div class="col-span-6 sm:col-span-3">
                                                <label for="winning_number" class="block text-sm font-medium text-purple-700">Please enter the winning number for this game after it's complete. The winner will be notified.</label>
                                                <input id="winning-number" type="number" min="1" max="15" name="winning_number" placeholder="1-15" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" required>

                                            </div>

                                        </div>
                                    </div>
                                    <div class="px-4 py-3 bg-gray-50 text-left sm:px-6">
                                        <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                            End Game
                                        </button>
                                    </div>
                                </div>
                            </form>
                        @endif

                        @if(empty($link))
                            <div class="loader justify-self-center"></div>
                            @if(!$isOwner)
                                <h2 class="text-center">The game will start shortly. Your game will appear shortly</h2><br><br>
                            @endif
                        @else
                            <p class="text-center">Video</p>
                            <div id="fb-root"></div>
                            <script async defer src="https://connect.facebook.net/en_US/sdk.js#xfbml=1&version=v3.2"></script>
                            <div class="fb-video" data-href="{{$link}}" data-allowfullscreen="true" data-width="500"></div>
                        @endif
                    </div>





                </div>
            </div>
        </div>


    </div>

    <style>
        .loader {
            border: 16px solid #f3f3f3; /* Light grey */
            border-top: 16px solid #3498db; /* Blue */
            border-radius: 50%;
            width: 120px;
            height: 120px;
            animation: spin 2s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
    <script>
        Echo.private(`update-game.{{$game->id}}`)
            .listen('UpdateGame', (e) => {
                window.location.reload()
            });
    </script>
</div>
