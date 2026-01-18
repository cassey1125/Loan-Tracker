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
    </style>
</head>
<body class="bg-white text-black font-game min-h-screen m-0 p-8 flex flex-col justify-between overflow-hidden relative">
    
    <!-- Main Content Area -->
    <div class="flex-grow flex flex-col justify-center w-full relative">
        
        <!-- Left Side: Temporarily Unavailable -->
        <div class="w-full text-left mb-20">
            <h1 class="uppercase leading-tight font-black tracking-tighter">
                <span class="block text-[7vw] leading-none mt-2">Temporarily</span>
                <span class="block text-[7vw] leading-none mt-2"> Unavailable</span>
            </h1>
        </div>

        <!-- Right Side Lower: Contact Info -->
        <div class="w-full text-right mt-10">
            <p class="text-xs sm:text-base md:text-xl lg:text-2xl leading-loose inline-block">
                Please contact the developer<br>for more information.
            </p>
        </div>

    </div>
    
    <!-- Footer -->
    <footer class="w-full text-center py-4 text-[10px] sm:text-xs md:text-sm opacity-60 absolute bottom-2 left-0 right-0">
        &copy; {{ date('Y') }} Loan Tracker System. All rights reserved.
    </footer>
</body>
</html>
