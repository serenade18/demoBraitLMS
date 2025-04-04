<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Pay Certificate Fee</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Kumbh+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Kumbh Sans', sans-serif;
        }
        /* Define your custom purple color */
        :root {
            --custom-purple: #803e90; /* Replace this with your desired purple hex code */
        }
        .bg-custom-purple {
            background-color: var(--custom-purple);
        }
        .text-custom-purple {
            color: var(--custom-purple);
        }
        .hover\:bg-custom-purple:hover {
            background-color: var(--custom-purple);
        }
    </style>
</head>
<body class="bg-gray-100 leading-normal tracking-normal overflow-hidden h-screen flex flex-col">

    <!-- Main Container -->
    <div class="flex-grow flex items-center justify-center">
        <!-- Payment Card -->
        <div class="bg-white shadow-lg rounded-lg p-8 max-w-md w-full">
            <!-- Logo -->
            <div class="flex justify-center mb-6">
                <img src="{{ asset('store/1/default_images/newlogo.png') }}" alt="Logo" class="h-12 w-auto rounded-full">
            </div>

            <!-- Header -->
            <h1 class="text-3xl font-semibold text-center text-gray-800 mb-6">Pay Certificate Fee</h1>

            <!-- Certificate Details -->
            <div class="bg-gray-50 p-4 rounded-lg mb-6">
                <p class="text-lg text-gray-700 mb-2">
                    You are about to pay for the certificate: 
                    <strong class="font-semibold text-custom-purple">{{ $certificate->title }}</strong>
                </p>
                <p class="text-lg text-gray-700 mb-4">
                    Fee Amount: 
                    <strong class="text-custom-purple font-semibold">KES 500</strong>
                </p>
            </div>

            <!-- Payment Form -->
            <form method="POST" action="{{ route('certificates.processPayment', $certificate->id) }}">
                @csrf
                <!-- Submit Button -->
                <button type="submit" class="w-full py-3 px-4 bg-custom-purple text-white rounded-lg hover:bg-custom-purple transition duration-300 ease-in-out transform hover:scale-105 mb-4">
                    Pay Now
                </button>
            </form>

            <!-- Cancel Button -->
            <a href="{{ url()->previous() }}" class="w-full py-3 px-4 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition duration-300 ease-in-out transform hover:scale-105 text-center block">
                Cancel
            </a>
        </div>
    </div>

</body>
</html>
