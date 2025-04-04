<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>
<body>


<div class="container mx-auto p-4">
    <h1 class="text-2xl font-bold mb-4">My Certificates</h1>
    
    @if(session('success'))
        <div class="bg-green-100 text-green-700 p-4 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    @if($certificates->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @foreach($certificates as $certificate)
                <div class="p-4 bg-white shadow rounded">
                    <h2 class="text-lg font-bold">{{ $certificate->title }}</h2>
                    <p class="text-gray-700">Issued on: {{ $certificate->created_at->format('M d, Y') }}</p>

                    @if($certificate->certificate_fee_paid)
                        <a href="{{ route('certificates.download', $certificate->id) }}" class="text-blue-500 hover:underline">
                            Download Certificate
                        </a>
                    @else
                        <a href="{{ route('certificates.payment', $certificate->id) }}" class="text-red-500 hover:underline">
                            Pay Certificate Fee
                        </a>
                    @endif
                </div>
            @endforeach
        </div>

        <div class="mt-4">
            {{ $certificates->links() }}
        </div>
    @else
        <p class="text-gray-500">You have no certificates yet.</p>
    @endif
</div>
@endsection

    
</body>
</html>