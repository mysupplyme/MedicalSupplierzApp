<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Applications Management</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        .approve-form { display: inline; }
        .approve-btn { 
            background: #10b981; 
            color: white; 
            border: none; 
            padding: 0.5rem 1rem; 
            border-radius: 4px; 
            cursor: pointer; 
        }
        .approve-btn:hover { background: #059669; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Applications Management</h1>
        
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($applications as $application)
                <tr>
                    <td>{{ $application->id }}</td>
                    <td>{{ $application->name }}</td>
                    <td>{{ $application->status }}</td>
                    <td>
                        @if($application->status !== 'approved')
                        <!-- Fixed: Use POST form instead of GET link -->
                        <form action="/applications/{{ $application->id }}/approve" method="POST" class="approve-form">
                            @csrf
                            <button type="submit" class="approve-btn">Approve</button>
                        </form>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</body>
</html>