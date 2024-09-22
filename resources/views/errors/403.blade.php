<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

</head>

<body>
    <section class="flex items-center h-screen p-16 bg-gray-50 dark:bg-gray-700">
        <div class="container flex flex-col items-center ">
            <div class="flex flex-col gap-6 max-w-md text-center">
                <h2 class="font-extrabold text-9xl text-gray-600 dark:text-gray-100">
                    <span class="sr-only">Error</span>404
                </h2>
                <p class="text-2xl md:text-3xl dark:text-gray-300">Sorry, we couldn't find this page.</p>
                <a href="{{route('filament.app.pages.dashboard')}}" class="px-8 py-4 text-xl font-semibold rounded bg-purple-600 text-gray-50 hover:text-gray-200">Back to home</a>
            </div>
        </div>
    </section>
</body>

</html>