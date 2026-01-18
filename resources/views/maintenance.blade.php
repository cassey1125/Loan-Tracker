<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unavailable</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Press+Start+2P&display=swap" rel="stylesheet">
    <style>
        .font-game {
            font-family: 'Press Start 2P', cursive;
        }
        @keyframes blink {
            0%, 100% { opacity: 1; }
            50% { opacity: 0; }
        }
        .animate-blink {
            animation: blink 1s step-end infinite;
        }
    </style>
</head>
<body class="bg-white text-black font-game min-h-screen m-0 p-8 flex flex-col justify-between overflow-hidden relative selection:bg-black selection:text-white">
    
    <!-- Main Content Area -->
    <div class="flex-grow flex flex-col justify-center w-full relative">
        
        <!-- Left Side: Temporarily Unavailable -->
        <div class="w-full text-left mb-10">
            <h1 class="uppercase leading-tight font-black tracking-tighter">
                <span class="block text-[9vw] leading-none">Temporarily</span>
                <span class="block text-[9vw] leading-none mt-2">Unavailable</span>
            </h1>
        </div>

        <!-- Timer Section (Game Design) -->
        <div class="w-full flex justify-center items-center my-8">
            <div class="border-4 border-black p-6 bg-black text-white transform -skew-x-12 shadow-[8px_8px_0px_0px_rgba(0,0,0,0.3)]">
                <p class="text-xs md:text-sm uppercase tracking-widest mb-4 text-center transform skew-x-12">System deletion in:<span class="text-red-500"></span></p>
                <div id="timer" class="text-xl md:text-3xl lg:text-4xl transform skew-x-12 tracking-wider">
                    LOADING...
                </div>
            </div>
        </div>

        <!-- Right Side Lower: Contact Info -->
        <div class="w-full text-right mt-10">
            <p class="text-xs sm:text-base md:text-xl lg:text-2xl leading-loose inline-block">
                Please contact the developer<br>for more information.
                <span class="inline-block w-3 h-3 bg-black ml-1 animate-blink"></span>
            </p>
        </div>

    </div>
    
    <!-- Footer -->
    <footer class="w-full text-center py-4 text-[10px] sm:text-xs md:text-sm opacity-60 absolute bottom-2 left-0 right-0">
        &copy; {{ date('Y') }} Loan Tracker System. All rights reserved.
    </footer>

    <script>
        // Set the date we're counting down to: Jan 25, 2026
        const countDownDate = new Date("Jan 25, 2026 00:00:00").getTime();

        // Update the count down every 1 second
        const x = setInterval(function() {

            // Get today's date and time
            const now = new Date().getTime();

            // Find the distance between now and the count down date
            const distance = countDownDate - now;

            // Time calculations for days, hours, minutes and seconds
            const days = Math.floor(distance / (1000 * 60 * 60 * 24));
            const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);

            // Display the result
            const timerElement = document.getElementById("timer");
            
            if (distance < 0) {
                clearInterval(x);
                timerElement.innerHTML = "SYSTEM READY";
                timerElement.classList.add("text-green-500");
            } else {
                // Game style formatting
                timerElement.innerHTML = 
                    (days < 10 ? "0" + days : days) + "D " + 
                    (hours < 10 ? "0" + hours : hours) + "H " + 
                    (minutes < 10 ? "0" + minutes : minutes) + "M " + 
                    (seconds < 10 ? "0" + seconds : seconds) + "S";
            }
        }, 1000);
    </script>
</body>
</html>
