@extends('layouts.app')

@section('title', 'Add Attendance - Innobles HRMS')

@section('sidebar')
    @include('partials.sidebar')
@endsection

@section('header')
    @include('partials.header')
@endsection

@section('content')
    <div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h1 class="mb-1">Add Attendance</h1>
            <p class="text-muted mb-0">Mark employee attendance with face verification</p>
        </div>
        <a href="{{ route('attendances.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Back to List
        </a>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-calendar-check me-2"></i>Attendance Information</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('attendances.store') }}">
                @csrf
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label for="personal_information_id" class="form-label">Employee</label>
                        @if(auth()->user()->role === 'admin')
                            <select name="personal_information_id" id="personal_information_id" class="form-select" required>
                                <option value="">Select Employee</option>
                                @foreach($employees as $employee)
                                    <option value="{{ $employee->id }}">
                                        {{ $employee->first_name }} {{ $employee->last_name }} ({{ $employee->employee_code }})
                                    </option>
                                @endforeach
                            </select>
                        @else
                            <select name="personal_information_id" id="personal_information_id" class="form-select" required disabled>
                                @foreach($employees as $employee)
                                    @if($employee->id === auth()->user()->personal_information_id)
                                        <option value="{{ $employee->id }}" selected>
                                            {{ $employee->first_name }} {{ $employee->last_name }} ({{ $employee->employee_code }})
                                        </option>
                                    @endif
                                @endforeach
                            </select>
                            <input type="hidden" name="personal_information_id" value="{{ auth()->user()->personal_information_id }}">
                        @endif
                    </div>
                    <div class="col-md-6">
                        <label for="attendance_date" class="form-label">Attendance Date</label>
                        <input type="date" name="attendance_date" id="attendance_date" class="form-control" readonly>
                    </div>
                </div>

                <!-- Face Verification Section -->
                <div class="border rounded p-3 p-md-4 mb-4 bg-light">
                    <h5 class="mb-3"><i class="bi bi-camera me-2"></i>Face Verification</h5>
                    <p class="text-muted mb-3">Minimum 30% match required for verification</p>
                    
                    <div class="text-center">
                        <video id="video" autoplay playsinline class="img-fluid rounded mb-3" style="max-width: 400px; width: 100%; display: none;"></video>
                        <canvas id="canvas" style="display: none;"></canvas>
                        <img id="captured-image" alt="Captured Face" class="img-fluid rounded mb-3" style="max-width: 200px; display: none;">
                        
                        <div class="d-flex justify-content-center gap-2 mb-3">
                            <button type="button" class="btn btn-success" onclick="startCamera()">
                                <i class="bi bi-camera-video me-2"></i>Start Camera
                            </button>
                            <button type="button" class="btn btn-primary" onclick="capturePhoto()">
                                <i class="bi bi-camera me-2"></i>Capture Photo
                            </button>
                        </div>
                        
                        <div id="face-match-status" class="alert" style="display: none;"></div>
                        <input type="hidden" name="captured_face" id="captured-face">
                        <input type="hidden" name="face_match_score" id="face-match-score">
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-outline-secondary" onclick="window.location.href='{{ route('attendances.index') }}'">
                        <i class="bi bi-x-circle me-2"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-primary" id="btn-save-attendance" disabled>
                        <i class="bi bi-check-circle me-2"></i>Save Attendance
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('styles')
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
<script>
    let stream = null;
    let modelsLoaded = false;

    // Set current date as default
    document.addEventListener('DOMContentLoaded', function() {
        const today = new Date().toISOString().split('T')[0];
        document.getElementById('attendance_date').value = today;

        // Add event listener to employee select
        const employeeSelect = document.querySelector('select[name="personal_information_id"]');
        const saveButton = document.getElementById('btn-save-attendance');

        if (employeeSelect) {
            // Prevent non-admin users from changing employee selection
            @if(auth()->user()->role !== 'admin')
                employeeSelect.addEventListener('change', function() {
                    this.value = '{{ auth()->user()->personal_information_id }}';
                });
            @endif

            employeeSelect.addEventListener('change', function() {
                if (!this.value) {
                    saveButton.disabled = true;
                }
            });
        }
    });

    async function loadModels() {
        try {
            await faceapi.nets.tinyFaceDetector.loadFromUri('https://cdn.jsdelivr.net/npm/@vladmandic/face-api@1.7.12/model/');
            await faceapi.nets.faceLandmark68Net.loadFromUri('https://cdn.jsdelivr.net/npm/@vladmandic/face-api@1.7.12/model/');
            await faceapi.nets.faceRecognitionNet.loadFromUri('https://cdn.jsdelivr.net/npm/@vladmandic/face-api@1.7.12/model/');
            modelsLoaded = true;
            console.log('Face recognition models loaded');
        } catch (error) {
            console.error('Error loading face recognition models:', error);
        }
    }

    async function startCamera() {
        try {
            stream = await navigator.mediaDevices.getUserMedia({ video: true });
            const video = document.getElementById('video');
            video.srcObject = stream;
            video.style.display = 'block';

            if (!modelsLoaded) {
                await loadModels();
            }

            document.getElementById('btn-save-attendance').disabled = true;
        } catch (error) {
            console.error('Error accessing camera:', error);
            alert('Unable to access camera. Please ensure camera permissions are granted.');
        }
    }

    async function capturePhoto() {
        const video = document.getElementById('video');
        const canvas = document.getElementById('canvas');
        const capturedImage = document.getElementById('captured-image');
        const faceMatchStatus = document.getElementById('face-match-status');
        const saveButton = document.getElementById('btn-save-attendance');

        if (!stream) {
            alert('Please start the camera first');
            return;
        }

        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        canvas.getContext('2d').drawImage(video, 0, 0);

        const imageData = canvas.toDataURL('image/jpeg');
        capturedImage.src = imageData;
        capturedImage.style.display = 'block';

        document.getElementById('captured-face').value = imageData;
        saveButton.disabled = false;

        await performFaceMatch(imageData);
    }

    async function performFaceMatch(capturedImageData) {
        const faceMatchStatus = document.getElementById('face-match-status');
        const employeeId = document.querySelector('select[name="personal_information_id"]').value;
        const saveButton = document.getElementById('btn-save-attendance');

        if (!employeeId) {
            faceMatchStatus.textContent = 'Please select an employee first';
            faceMatchStatus.className = 'alert face-match-fail';
            faceMatchStatus.style.display = 'block';
            saveButton.disabled = true;
            return;
        }

        faceMatchStatus.textContent = 'Analyzing face...';
        faceMatchStatus.className = 'alert face-match-loading';
        faceMatchStatus.style.display = 'block';

        try {
            const response = await fetch(`/api/employee-photo/${employeeId}`);
            const data = await response.json();

            if (!data.photo) {
                faceMatchStatus.textContent = 'Employee photo not found. Face verification skipped.';
                faceMatchStatus.className = 'alert face-match-fail';
                document.getElementById('face-match-score').value = '0';
                return;
            }

            const capturedImg = await faceapi.fetchImage(capturedImageData);
            const referenceImg = await faceapi.fetchImage(data.photo);

            const detected1 = await faceapi.detectSingleFace(capturedImg, new faceapi.TinyFaceDetectorOptions())
                .withFaceLandmarks()
                .withFaceDescriptor();

            const detected2 = await faceapi.detectSingleFace(referenceImg, new faceapi.TinyFaceDetectorOptions())
                .withFaceLandmarks()
                .withFaceDescriptor();

            if (!detected1 || !detected2) {
                faceMatchStatus.textContent = 'Face not detected in one or both images';
                faceMatchStatus.className = 'alert face-match-fail';
                document.getElementById('face-match-score').value = '0';
                return;
            }

            const distance = faceapi.euclideanDistance(detected1.descriptor, detected2.descriptor);
            const matchPercentage = Math.max(0, (1 - distance) * 100);

            document.getElementById('face-match-score').value = matchPercentage.toFixed(2);

            if (matchPercentage >= 30) {
                faceMatchStatus.textContent = `Face Match: ${matchPercentage.toFixed(1)}% - Verified ✓`;
                faceMatchStatus.className = 'alert face-match-success';
                saveButton.disabled = false;
            } else {
                faceMatchStatus.textContent = `Face Match: ${matchPercentage.toFixed(1)}% - Below 30% threshold ✗`;
                faceMatchStatus.className = 'alert face-match-fail';
            }

        } catch (error) {
            console.error('Error during face matching:', error);
            faceMatchStatus.textContent = 'Error during face verification. Please try again.';
            faceMatchStatus.className = 'alert face-match-fail';
            document.getElementById('face-match-score').value = '0';
        }
    }

    window.addEventListener('beforeunload', () => {
        if (stream) {
            stream.getTracks().forEach(track => track.stop());
        }
    });

    document.addEventListener('DOMContentLoaded', () => {
        loadModels();
    });
</script>
@endpush
