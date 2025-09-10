<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#3b82f6">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <title>Group Communication - {{ config('app.name', 'Church Management System') }}</title>
    
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
        @media (max-width: 768px) {
            h1 {
                font-size: 1.8rem;
            }
            .btn-group .btn {
                padding: 0.25rem 0.5rem;
                font-size: 0.75rem;
            }
            .card-header {
                padding: 10px 15px;
            }
            .card-body {
                padding: 0.75rem;
            }
        }
        .avatar-sm {
            width: 40px;
            height: 40px;
            overflow: hidden;
        }
        .avatar-placeholder {
            width: 100%;
            height: 100%;
            background-color: #e9ecef;
            color: #6c757d;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            border-radius: 50%;
        }
        .message-content, .prayer-content {
            white-space: pre-line;
        }
        .attachment {
            background-color: #f8f9fa;
            padding: 0.5rem;
            border-radius: 0.25rem;
            margin-bottom: 0.5rem;
        }
        .attachment a {
            color: inherit;
            text-decoration: none;
        }
        .attachment a:hover {
            text-decoration: underline;
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
        /* Floating action button for mobile */
        .floating-action-btn {
            position: fixed;
            bottom: 80px;
            right: 20px;
            width: 56px;
            height: 56px;
            border-radius: 50%;
            background-color: #3b82f6;
            color: white;
            display: none;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
            z-index: 999;
        }
        @media (max-width: 768px) {
            .floating-action-btn {
                display: flex;
            }
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="row mb-4">
            <div class="col-12">
                <h1 class="mb-4">Group Communication</h1>
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Group Communication</h5>
                        <div>
                            <div class="btn-group">
                                <button class="btn btn-sm btn-outline-primary active">Messages</button>
                                <button class="btn btn-sm btn-outline-primary">Announcements</button>
                                <button class="btn btn-sm btn-outline-primary">Prayer Requests</button>
                                <button class="btn btn-sm btn-outline-primary">Documents</button>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Messages Tab -->
                        <div>
                            <div class="d-flex justify-content-between mb-3">
                                <h6>Group Messages</h6>
                                <button class="btn btn-sm btn-primary">
                                    <i class="fas fa-plus"></i> New Message
                                </button>
                            </div>
                            
                            <div class="message-list">
                                <!-- Message 1 -->
                                <div class="card mb-3">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-sm me-2">
                                                    <div class="avatar-placeholder">
                                                        JD
                                                    </div>
                                                </div>
                                                <div>
                                                    <div class="fw-bold">John Doe</div>
                                                    <div class="small text-muted">May 23, 2025, 10:30 AM</div>
                                                </div>
                                            </div>
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-link text-muted" type="button" data-bs-toggle="dropdown">
                                                    <i class="fas fa-ellipsis-v"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end">
                                                    <li><a class="dropdown-item" href="#">Edit</a></li>
                                                    <li><a class="dropdown-item text-danger" href="#">Delete</a></li>
                                                </ul>
                                            </div>
                                        </div>
                                        <div class="message-content">
                                            Hello everyone! Just a reminder that we have our weekly Bible study tomorrow at 7 PM. Looking forward to seeing you all there!
                                        </div>
                                        <div class="mt-2">
                                            <div class="attachment">
                                                <a href="#" class="d-flex align-items-center">
                                                    <i class="fas fa-paperclip me-2"></i>
                                                    <span>bible_study_notes.pdf</span>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Message 2 -->
                                <div class="card mb-3">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-sm me-2">
                                                    <div class="avatar-placeholder">
                                                        JS
                                                    </div>
                                                </div>
                                                <div>
                                                    <div class="fw-bold">Jane Smith</div>
                                                    <div class="small text-muted">May 22, 2025, 3:45 PM</div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="message-content">
                                            I'll bring some refreshments for everyone. Does anyone have any dietary restrictions I should be aware of?
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Message 3 -->
                                <div class="card mb-3">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-sm me-2">
                                                    <div class="avatar-placeholder">
                                                        MJ
                                                    </div>
                                                </div>
                                                <div>
                                                    <div class="fw-bold">Michael Johnson</div>
                                                    <div class="small text-muted">May 21, 2025, 5:15 PM</div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="message-content">
                                            I'm gluten-free, but please don't go out of your way for me. I can bring something for myself.
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
                    <a href="{{ route('group.analytics') }}" class="btn btn-primary">View Group Analytics</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Floating Action Button for Mobile -->
    <a href="#" class="floating-action-btn" id="newMessageBtn">
        <i class="fas fa-plus"></i>
    </a>

    <!-- Mobile Navigation -->
    <div class="mobile-nav">
        <a href="{{ route('dashboard') }}" class="mobile-nav-item">
            <i class="fas fa-home"></i>
            <span>Home</span>
        </a>
        <a href="{{ route('group.analytics') }}" class="mobile-nav-item">
            <i class="fas fa-chart-bar"></i>
            <span>Analytics</span>
        </a>
        <a href="{{ route('group.communication') }}" class="mobile-nav-item active">
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
        
        // Mobile-specific JavaScript
        document.addEventListener('DOMContentLoaded', function() {
            // Handle floating action button click
            document.getElementById('newMessageBtn').addEventListener('click', function(e) {
                e.preventDefault();
                // Show new message modal or form
                alert('New message feature will be implemented here');
            });
        });
    </script>
</body>
</html>
