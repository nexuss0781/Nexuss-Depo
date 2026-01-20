<?php
/**
 * InfinityFree PHP Deployer - Backend Logic
 * Handles file validation, directory creation, unzipping, and cleanup.
 */

// 1. Configuration & Environment Setup
// Attempt to increase limits (InfinityFree might ignore these, but it's best practice)
@ini_set('upload_max_filesize', '64M');
@ini_set('post_max_size', '64M');
@ini_set('max_execution_time', '300'); // 5 minutes
@ini_set('memory_limit', '128M');

$deployDir = 'deployments/';
$status = 'error'; // default status
$message = 'Unknown error occurred.';
$deployedUrl = '';

// 2. Logic Execution
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // --- Validation: Project Name ---
        if (!isset($_POST['project_name']) || empty($_POST['project_name'])) {
            throw new Exception("Project name is missing.");
        }

        // Strict Regex: Only alphanumeric and dashes. Prevents "../" attacks.
        $projectName = $_POST['project_name'];
        if (!preg_match('/^[a-zA-Z0-9-]+$/', $projectName)) {
            throw new Exception("Invalid project name. Only letters, numbers, and dashes allowed.");
        }

        $targetFolder = $deployDir . $projectName;

        // --- Validation: Collision Check ---
        // We prevent overwriting to avoid accidental data loss. 
        if (is_dir($targetFolder)) {
            throw new Exception("Project '<b>$projectName</b>' already exists. <br>Please delete it via FTP/FileManager first or choose a new version name (e.g., $projectName-v2).");
        }

        // --- Validation: File Upload ---
        if (!isset($_FILES['zip_file']) || $_FILES['zip_file']['error'] !== UPLOAD_ERR_OK) {
            $code = $_FILES['zip_file']['error'] ?? 'Unknown';
            throw new Exception("File upload failed. Error Code: $code");
        }

        $fileInfo = pathinfo($_FILES['zip_file']['name']);
        if (strtolower($fileInfo['extension']) !== 'zip') {
            throw new Exception("Only .zip files are allowed.");
        }

        // --- Execution: Directory Creation ---
        if (!mkdir($targetFolder, 0755, true)) {
            throw new Exception("Failed to create directory: $targetFolder");
        }

        // --- Execution: Unzipping ---
        $zip = new ZipArchive;
        $res = $zip->open($_FILES['zip_file']['tmp_name']);
        
        if ($res === TRUE) {
            $zip->extractTo($targetFolder);
            $zip->close();
            
            // --- Cleanup: Delete temp file if needed (PHP usually handles tmp, but good to be sure) ---
            // Note: We don't need to delete the uploaded zip from 'deployments' because we extracted strictly from tmp.
            
            // Success State
            $status = 'success';
            $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
            $host = $_SERVER['HTTP_HOST'];
            // Construct absolute URL
            $deployedUrl = "$protocol://$host/" . $targetFolder . "/index.php";
            $message = "Your application has been successfully deployed.";

        } else {
            // Clean up the empty folder if unzip failed
            rmdir($targetFolder);
            throw new Exception("Failed to open or extract the Zip file.");
        }

    } catch (Exception $e) {
        $status = 'error';
        $message = $e->getMessage();
    }
} else {
    // Direct access to deploy.php redirects back to home
    header("Location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en" class="h-full bg-gray-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Deployment Status</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/heroicons@1.0.6/outline"></script>
</head>
<body class="h-full flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    
    <div class="max-w-md w-full space-y-8 bg-white p-10 rounded-xl shadow-lg border border-gray-100">
        
        <!-- Status Icon -->
        <div class="flex justify-center">
            <?php if ($status === 'success'): ?>
                <div class="rounded-full bg-green-100 p-3">
                    <svg class="h-12 w-12 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
            <?php else: ?>
                <div class="rounded-full bg-red-100 p-3">
                    <svg class="h-12 w-12 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </div>
            <?php endif; ?>
        </div>

        <!-- Title & Message -->
        <div class="text-center">
            <h2 class="mt-2 text-3xl font-extrabold text-gray-900">
                <?php echo ($status === 'success') ? 'Deployment Successful!' : 'Deployment Failed'; ?>
            </h2>
            <p class="mt-2 text-sm text-gray-500">
                <?php echo $message; ?>
            </p>
        </div>

        <?php if ($status === 'success'): ?>
            <!-- Success Actions -->
            <div class="mt-8 space-y-4">
                
                <!-- Main Link -->
                <div class="rounded-md bg-gray-50 p-4 border border-gray-200">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M11 3a1 1 0 100 2h2.586l-6.293 6.293a1 1 0 101.414 1.414L15 6.414V9a1 1 0 102 0V4a1 1 0 00-1-1h-5z" />
                                <path d="M5 5a2 2 0 00-2 2v8a2 2 0 002 2h8a2 2 0 002-2v-3a1 1 0 10-2 0v3H5V7h3a1 1 0 000-2H5z" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-gray-800">App URL</h3>
                            <div class="mt-2 text-sm text-indigo-600 break-all font-mono">
                                <a href="<?php echo $deployedUrl; ?>" target="_blank" class="hover:underline">
                                    <?php echo $deployedUrl; ?>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Telegram Helper (Since you mentioned Bots) -->
                <div class="bg-blue-50 border-l-4 border-blue-400 p-4">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-blue-700">
                                <strong>Telegram Bot?</strong> 
                                <a href="https://api.telegram.org/bot<YOUR_TOKEN>/setWebhook?url=<?php echo urlencode($deployedUrl); ?>" target="_blank" class="underline">
                                    Click here to set Webhook
                                </a> 
                                (Replace &lt;TOKEN&gt; in URL).
                            </p>
                        </div>
                    </div>
                </div>

                <a href="index.php" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Back to Dashboard
                </a>
            </div>

        <?php else: ?>
            <!-- Error Actions -->
            <div class="mt-8">
                <a href="index.php" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                    Try Again
                </a>
            </div>
        <?php endif; ?>

    </div>

</body>
</html>
