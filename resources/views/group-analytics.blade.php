<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#3b82f6">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <title>Group Analytics - {{ config('app.name', 'Church Management System') }}</title>
    
    <!-- PWA Support -->
    <link rel="manifest" href="/pwa/manifest.json">
    <link rel="apple-touch-icon" href="/pwa/icons/icon-192x192.png">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom Styles -->
    <style>
        body {
            font-family: 'Figtree', sans-serif;
            background-color: #f8f9fa;
            padding-bottom: env(safe-area-inset-bottom);
        }
        .card {
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            overflow: hidden; /* Prevent content overflow on small screens */
        }
        .card-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid #e9ecef;
            padding: 15px 20px;
        }
        .display-4 {
            font-size: 2.5rem;
            font-weight: 700;
        }
        @media (max-width: 768px) {
            .display-4 {
                font-size: 1.8rem;
            }
            h1 {
                font-size: 1.8rem;
            }
            .btn-group .btn {
                padding: 0.25rem 0.5rem;
                font-size: 0.75rem;
            }
        }
        .chart-placeholder {
            background-color: #f8f9fa;
            height: 250px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 5px;
            border: 1px dashed #dee2e6;
        }
        @media (max-width: 768px) {
            .chart-placeholder {
                height: 200px;
            }
        }
        .progress {
            height: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
        }
        .progress-bar {
            border-radius: 5px;
        }
        /* Bottom navigation for mobile */
        .mobile-nav {
            display: none;
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: white;
            box-shadow: 0 -2px 10px rgba(0,0,0,0.1);
            z-index: 1000;
            padding-bottom: env(safe-area-inset-bottom);
        }
        @media (max-width: 768px) {
            .mobile-nav {
                display: flex;
            }
            .container {
                padding-bottom: 70px;
            }
        }
        .mobile-nav-item {
            flex: 1;
            text-align: center;
            padding: 10px 0;
            color: #6c757d;
            text-decoration: none;
        }
        .mobile-nav-item.active {
            color: #3b82f6;
        }
        .mobile-nav-item i {
            font-size: 1.2rem;
            display: block;
            margin-bottom: 2px;
        }
        .mobile-nav-item span {
            font-size: 0.7rem;
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="row mb-4">
            <div class="col-12">
                <h1 class="mb-4">Group Analytics Dashboard</h1>
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Group Analytics</h5>
                        <div>
                            <div class="btn-group">
                                <button class="btn btn-sm btn-outline-primary active">Month</button>
                                <button class="btn btn-sm btn-outline-primary">Quarter</button>
                                <button class="btn btn-sm btn-outline-primary">Year</button>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Summary Cards -->
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <div class="card h-100 border-0 bg-light">
                                    <div class="card-body text-center">
                                        <div class="display-4 fw-bold text-primary mb-2">24</div>
                                        <div class="text-muted">Total Members</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card h-100 border-0 bg-light">
                                    <div class="card-body text-center">
                                        <div class="display-4 fw-bold text-success mb-2">78%</div>
                                        <div class="text-muted">Average Attendance</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card h-100 border-0 bg-light">
                                    <div class="card-body text-center">
                                        <div class="display-4 fw-bold text-info mb-2">3</div>
                                        <div class="text-muted">New Members</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card h-100 border-0 bg-light">
                                    <div class="card-body text-center">
                                        <div class="display-4 fw-bold text-warning mb-2">8</div>
                                        <div class="text-muted">Total Events</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Attendance Trends -->
                        <div class="row mb-4">
                            <div class="col-md-8">
                                <div class="card h-100">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0">Attendance Trends</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="chart-placeholder">
                                            <div class="text-center text-muted">
                                                <i class="fas fa-chart-line fa-3x mb-3"></i>
                                                <p>Attendance trend visualization</p>
                                                <p class="small">Using actual attendance data from the database</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card h-100">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0">Member Engagement</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="chart-placeholder">
                                            <div class="text-center text-muted">
                                                <i class="fas fa-users fa-3x mb-3"></i>
                                                <p>Engagement metrics</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Growth & Demographics -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card h-100">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0">Growth Over Time</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="chart-placeholder">
                                            <div class="text-center text-muted">
                                                <i class="fas fa-chart-bar fa-3x mb-3"></i>
                                                <p>Growth visualization</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card h-100">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0">Member Demographics</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <h6 class="text-muted mb-3">Age Distribution</h6>
                                                <div class="mb-2">
                                                    <div class="d-flex justify-content-between mb-1">
                                                        <span>18-24</span>
                                                        <span>15%</span>
                                                    </div>
                                                    <div class="progress">
                                                        <div class="progress-bar bg-info" style="width: 15%"></div>
                                                    </div>
                                                </div>
                                                <div class="mb-2">
                                                    <div class="d-flex justify-content-between mb-1">
                                                        <span>25-34</span>
                                                        <span>30%</span>
                                                    </div>
                                                    <div class="progress">
                                                        <div class="progress-bar bg-info" style="width: 30%"></div>
                                                    </div>
                                                </div>
                                                <div class="mb-2">
                                                    <div class="d-flex justify-content-between mb-1">
                                                        <span>35-44</span>
                                                        <span>25%</span>
                                                    </div>
                                                    <div class="progress">
                                                        <div class="progress-bar bg-info" style="width: 25%"></div>
                                                    </div>
                                                </div>
                                                <div class="mb-2">
                                                    <div class="d-flex justify-content-between mb-1">
                                                        <span>45-54</span>
                                                        <span>20%</span>
                                                    </div>
                                                    <div class="progress">
                                                        <div class="progress-bar bg-info" style="width: 20%"></div>
                                                    </div>
                                                </div>
                                                <div class="mb-2">
                                                    <div class="d-flex justify-content-between mb-1">
                                                        <span>55+</span>
                                                        <span>10%</span>
                                                    </div>
                                                    <div class="progress">
                                                        <div class="progress-bar bg-info" style="width: 10%"></div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <h6 class="text-muted mb-3">Gender Distribution</h6>
                                                <div class="mb-2">
                                                    <div class="d-flex justify-content-between mb-1">
                                                        <span>Male</span>
                                                        <span>45%</span>
                                                    </div>
                                                    <div class="progress">
                                                        <div class="progress-bar bg-primary" style="width: 45%"></div>
                                                    </div>
                                                </div>
                                                <div class="mb-2">
                                                    <div class="d-flex justify-content-between mb-1">
                                                        <span>Female</span>
                                                        <span>55%</span>
                                                    </div>
                                                    <div class="progress">
                                                        <div class="progress-bar bg-danger" style="width: 55%"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between">
                    <a href="{{ route('dashboard') }}" class="btn btn-secondary">Back to Dashboard</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Mobile Navigation -->
    <div class="mobile-nav">
        <a href="{{ route('dashboard') }}" class="mobile-nav-item">
            <i class="fas fa-home"></i>
            <span>Home</span>
        </a>
        <a href="{{ route('group.analytics') }}" class="mobile-nav-item active">
            <i class="fas fa-chart-bar"></i>
            <span>Analytics</span>
        </a>
        <a href="{{ route('group.communication') }}" class="mobile-nav-item">
            <i class="fas fa-comments"></i>
            <span>Messages</span>
        </a>
        <a href="#" class="mobile-nav-item">
            <i class="fas fa-users"></i>
            <span>Members</span>
        </a>
        <a href="#" class="mobile-nav-item">
            <i class="fas fa-cog"></i>
            <span>Settings</span>
        </a>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- PWA Service Worker Registration -->
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function() {
                navigator.serviceWorker.register('/pwa/sw.js').then(function(registration) {
                    console.log('ServiceWorker registration successful with scope: ', registration.scope);
                }, function(err) {
                    console.log('ServiceWorker registration failed: ', err);
                });
            });
        }
    </script>
</body>
</html>
