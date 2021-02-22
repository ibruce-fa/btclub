<div>
    {{-- Be like water. --}}
    <h2 class="text-center bg-white rounded p-4">Wus hannin <br> the winner is {{$winner}}!!!<br> {{$winningNumbers}}</h2>


    <div class="bg-green-500">
        @for($r=0; $r<5; $r++)
            <div class="flex justify-center my-3">
                {{--        <td class="text-center">--}}
                {{--            <button class="font-bold text-gray-700 rounded-full bg-white flex items-center justify-center font-mono ml-auto mr-auto text-md p-2" style=" font-size: .5rem;">{{"@someUser"}}</button>--}}
                {{--        </td>--}}
                @for($i = 1; $i <=15; $i++)
                    <div class="text-center" id="board_number_{{$i}}" style="width: 6% !important;">
                        <button class="font-bold rounded-full {{isset($gameNumbersArray[($r*15)+$i - 1]) ? "bg-white text-gray-700" : "bg-yellow-700 text-white" }} flex items-center justify-center font-mono ml-auto mr-auto text-md p-2" style="height: 1rem; width: 1rem; font-size: .5rem;">{{($r*15)+$i}}</button>
                    </div>
                @endfor
            </div>
        @endfor
    </div>


</div>
