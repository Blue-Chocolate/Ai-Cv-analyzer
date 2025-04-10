<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>AI CV Analyzer</title>

    <!-- Bootstrap CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Vite CSS (for custom styles) -->
    @vite('resources/css/app.css')
</head>
<body class="bg-light text-dark">
    <div class="container my-5">
        <h1 class="display-4 text-center mb-5">AI CV Analyzer</h1>

        <!-- Upload Form -->
        <div class="card shadow-lg p-4 mb-5">
            <h2 class="h4 text-primary mb-4">Upload New CV</h2>
            <form action="{{ route('cvs.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <label for="cv" class="form-label">Select PDF CV</label>
                    <input type="file" name="cv" accept=".pdf" required class="form-control">
                </div>
                <button type="submit" class="btn btn-primary">Upload & Analyze</button>
            </form>
        </div>

        <!-- CV List -->
        <div class="card shadow-lg p-4">
            <h2 class="h4 text-success mb-4">Analyzed CVs</h2>
            @if($cvs->isEmpty())
                <p class="text-muted">No CVs uploaded yet.</p>
            @else
                <div class="list-group">
                    @foreach($cvs as $cv)
                        <div class="list-group-item d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h5 class="mb-1">{{ $cv->name }}</h5>

                                <!-- 🤖 AI Summary -->
                                <div class="bg-light p-3 rounded mt-3">
                                    <div class="d-flex align-items-center">
                                        <span class="fs-3 me-3">🤖</span>
                                        <div>
                                            <h6 class="h6 mb-2">AI Summary</h6>
                                            <p class="mb-0 text-muted">{{ $cv->summary }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex gap-2">
                                <a href="{{ Storage::url($cv->path) }}" target="_blank" class="btn btn-outline-secondary btn-sm">View PDF</a>
                                <form action="{{ route('cvs.destroy', $cv) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger btn-sm">Delete</button>
                                </form>
                            </div>
                        </div>

                        <!-- Scores -->
                        <div class="row g-3 mt-4">
                            <div class="col-6 col-md-4 col-lg-2">
                                <div class="card bg-primary text-white text-center">
                                    <div class="card-body">
                                        <h6 class="card-title">Experience</h6>
                                        <p class="card-text">{{ $cv->experience_years }} years</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6 col-md-4 col-lg-2">
                                <div class="card bg-success text-white text-center">
                                    <div class="card-body">
                                        <h6 class="card-title">Skills</h6>
                                        <p class="card-text">{{ $cv->skill_score }}%</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6 col-md-4 col-lg-2">
                                <div class="card bg-info text-white text-center">
                                    <div class="card-body">
                                        <h6 class="card-title">Soft Skills</h6>
                                        <p class="card-text">{{ $cv->soft_skills }}%</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6 col-md-4 col-lg-2">
                                <div class="card bg-warning text-white text-center">
                                    <div class="card-body">
                                        <h6 class="card-title">Education</h6>
                                        <p class="card-text">{{ $cv->education_score }}%</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6 col-md-4 col-lg-2">
                                <div class="card bg-danger text-white text-center">
                                    <div class="card-body">
                                        <h6 class="card-title">Relevance</h6>
                                        <p class="card-text">{{ $cv->relevant_experience }}%</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6 col-md-4 col-lg-2">
                                <div class="card bg-dark text-white text-center">
                                    <div class="card-body">
                                        <h6 class="card-title">Overall Fit</h6>
                                        <p class="card-text">{{ $cv->fit_score }}%</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <!-- Success Message -->
    @if(session('success'))
        <div class="position-fixed bottom-0 end-0 mb-3 me-3">
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    @endif

    <!-- Bootstrap JS and Popper.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>
</body>
</html>
