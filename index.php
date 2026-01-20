<?php
// Configuration
$deployDir = 'deployments';

// Ensure deployment directory exists
if (!is_dir($deployDir)) {
    mkdir($deployDir, 0755, true);
}

// Get list of deployments
$deployments = [];
$scanned = array_diff(scandir($deployDir), array('..', '.'));

foreach ($scanned as $folder) {
    $fullPath = $deployDir . '/' . $folder;
    if (is_dir($fullPath)) {
        $deployments[] = [
            'name' => $folder,
            'url' => $fullPath . '/index.php',
            'date' => date("M d, Y H:i", filemtime($fullPath))
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="en" class="h-full bg-gray-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Infinity Deployer</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Heroicons -->
    <script src="https://unpkg.com/heroicons@1.0.6/outline"></script>
    <style>
        .loader {
            border-top-color: #4F46E5;
            -webkit-animation: spinner 1.5s linear infinite;
            animation: spinner 1.5s linear infinite;
        }
        @keyframes spinner { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
        /* Hide scrollbar for clean look */
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: #f1f1f1; }
        ::-webkit-scrollbar-thumb { background: #c7c7c7; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #a8a8a8; }
    </style>
</head>
<body class="h-full font-sans antialiased text-gray-900">

    <!-- Loading Overlay -->
    <div id="loadingOverlay" class="fixed inset-0 bg-white bg-opacity-90 z-50 hidden flex flex-col items-center justify-center">
        <div class="loader ease-linear rounded-full border-4 border-t-4 border-gray-200 h-12 w-12 mb-4"></div>
        <h2 class="text-xl font-semibold text-gray-700">Deploying Application...</h2>
        <p class="text-gray-500 text-sm mt-2">Unzipping files and setting up the environment.</p>
    </div>

    <div class="min-h-full">
        <!-- Navbar -->
        <nav class="bg-white shadow-sm border-b border-gray-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex items-center">
                        <svg class="h-8 w-8 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.384-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                        </svg>
                        <span class="ml-3 text-xl font-bold tracking-tight text-gray-900">Infinity<span class="text-indigo-600">Deployer</span></span>
                    </div>
                    <div class="flex items-center">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            <span class="w-2 h-2 mr-1 bg-green-400 rounded-full"></span>
                            System Operational
                        </span>
                    </div>
                </div>
            </div>
        </nav>

        <main class="py-10">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    
                    <!-- Left Column: Deploy Form -->
                    <div class="lg:col-span-1">
                        <div class="bg-white overflow-hidden shadow rounded-lg sticky top-6">
                            <div class="px-4 py-5 sm:p-6">
                                <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4">🚀 New Deployment</h3>
                                
                                <form id="deployForm" action="deploy.php" method="POST" enctype="multipart/form-data">
                                    
                                    <!-- Project Name -->
                                    <div class="mb-4">
                                        <label for="project_name" class="block text-sm font-medium text-gray-700">Project ID / Slug</label>
                                        <div class="mt-1 relative rounded-md shadow-sm">
                                            <input type="text" name="project_name" id="project_name" 
                                                   class="focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-3 sm:text-sm border-gray-300 rounded-md py-2 border" 
                                                   placeholder="e.g. weather-bot-v1" 
                                                   pattern="[a-zA-Z0-9-]+" 
                                                   required>
                                        </div>
                                        <p class="mt-1 text-xs text-gray-500">Only letters, numbers, and hyphens.</p>
                                    </div>

                                    <!-- Drop Zone -->
                                    <div class="mb-6">
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Source Code (.zip)</label>
                                        <div id="drop-zone" class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md hover:border-indigo-500 hover:bg-gray-50 transition cursor-pointer relative">
                                            <div class="space-y-1 text-center">
                                                <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                                                    <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                                </svg>
                                                <div class="text-sm text-gray-600">
                                                    <span class="font-medium text-indigo-600 hover:text-indigo-500">Upload a file</span>
                                                    <p class="pl-1">or drag and drop</p>
                                                </div>
                                                <p class="text-xs text-gray-500">ZIP up to 64MB</p>
                                                <p id="file-name" class="text-sm font-bold text-indigo-600 mt-2 h-5"></p>
                                            </div>
                                            <input id="file-upload" name="zip_file" type="file" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" accept=".zip" required>
                                        </div>
                                    </div>

                                    <button type="submit" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition">
                                        Deploy to Production
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Dashboard -->
                    <div class="lg:col-span-2">
                        <div class="bg-white shadow rounded-lg overflow-hidden min-h-[500px]">
                            <!-- Header & Search -->
                            <div class="px-4 py-5 border-b border-gray-200 sm:px-6 bg-gray-50 flex flex-col sm:flex-row justify-between items-center gap-4">
                                <div>
                                    <h3 class="text-lg leading-6 font-medium text-gray-900">Active Deployments</h3>
                                    <p class="mt-1 max-w-2xl text-sm text-gray-500">Manage your bots and web apps.</p>
                                </div>
                                <div class="relative w-full sm:w-64">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                        </svg>
                                    </div>
                                    <input type="text" id="searchInput" class="focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-10 sm:text-sm border-gray-300 rounded-md border py-2" placeholder="Search deployments...">
                                </div>
                            </div>

                            <!-- List -->
                            <ul id="deployList" class="divide-y divide-gray-200">
                                <?php if (empty($deployments)): ?>
                                    <li class="px-4 py-12 text-center">
                                        <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                        </svg>
                                        <h3 class="mt-2 text-sm font-medium text-gray-900">No projects deployed</h3>
                                        <p class="mt-1 text-sm text-gray-500">Upload your first .zip file to get started.</p>
                                    </li>
                                <?php else: ?>
                                    <?php foreach ($deployments as $app): ?>
                                        <li class="hover:bg-gray-50 transition duration-150 ease-in-out deployment-item" data-name="<?php echo strtolower($app['name']); ?>">
                                            <div class="px-4 py-4 sm:px-6">
                                                <div class="flex items-center justify-between">
                                                    <div class="flex items-center">
                                                        <div class="flex-shrink-0">
                                                            <div class="h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 font-bold text-lg">
                                                                <?php echo strtoupper(substr($app['name'], 0, 1)); ?>
                                                            </div>
                                                        </div>
                                                        <div class="ml-4">
                                                            <p class="text-sm font-medium text-indigo-600 truncate"><?php echo htmlspecialchars($app['name']); ?></p>
                                                            <div class="flex items-center text-sm text-gray-500 mt-1">
                                                                <svg class="flex-shrink-0 mr-1.5 h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                                </svg>
                                                                <p>Deployed: <?php echo $app['date']; ?></p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="flex flex-shrink-0 ml-2">
                                                        <a href="<?php echo $app['url']; ?>" target="_blank" class="inline-flex items-center px-3 py-1.5 border border-gray-300 shadow-sm text-xs font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                                            Open App 
                                                            <svg class="ml-2 -mr-0.5 h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                                            </svg>
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </li>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        // 1. Drag and Drop Logic
        const dropZone = document.getElementById('drop-zone');
        const fileInput = document.getElementById('file-upload');
        const fileNameDisplay = document.getElementById('file-name');

        fileInput.addEventListener('change', (e) => {
            if (e.target.files.length > 0) {
                fileNameDisplay.textContent = "📄 " + e.target.files[0].name;
                dropZone.classList.add('border-indigo-500', 'bg-indigo-50');
            }
        });

        // 2. Search Logic
        const searchInput = document.getElementById('searchInput');
        const items = document.querySelectorAll('.deployment-item');

        searchInput.addEventListener('input', (e) => {
            const term = e.target.value.toLowerCase();
            items.forEach(item => {
                const name = item.getAttribute('data-name');
                if (name.includes(term)) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });
        });

        // 3. Loading State
        document.getElementById('deployForm').addEventListener('submit', function() {
            document.getElementById('loadingOverlay').classList.remove('hidden');
        });
    </script>
</body>
</html>
