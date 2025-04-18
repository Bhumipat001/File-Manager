<?php
session_start();

define('USERNAME', '');
define('PASSWORD', '');

$base_dir = __DIR__;

if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    header("Location: file-manager.php");
    exit();
}

function deleteFolder($folder) {
    $files = array_diff(scandir($folder), array('.', '..'));
    foreach ($files as $file) {
        $path = $folder . '/' . $file;
        if (is_dir($path)) {
            deleteFolder($path);
        } else {
            unlink($path);
        }
    }
    return rmdir($folder);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['username'], $_POST['password'])) {
    if ($_POST['username'] === USERNAME && $_POST['password'] === PASSWORD) {
        $_SESSION['authenticated'] = true;
        header("Location: file-manager.php");
        exit();
    } else {
        $error = "Invalid username or password.";
    }
}

$upload_msg = $delete_msg = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file_to_upload'])) {
    foreach ($_FILES['file_to_upload']['name'] as $key => $name) {
        $target_path = $base_dir . '/' . basename($name);
        if (move_uploaded_file($_FILES['file_to_upload']['tmp_name'][$key], $target_path)) {
            $upload_msg = "File(s) uploaded successfully.";
        } else {
            $upload_msg = "Failed to upload file(s).";
        }
    }
    header("Location: file-manager.php?upload_success=1");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['folder_to_upload'])) {
    $relative_paths = json_decode($_POST['relative_paths'], true);
    foreach ($_FILES['folder_to_upload']['name'] as $key => $name) {
        $relative_path = $relative_paths[$key];
        $target_path = $base_dir . '/' . $relative_path;
        $directory = dirname($target_path);
        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);
        }
        if (move_uploaded_file($_FILES['folder_to_upload']['tmp_name'][$key], $target_path)) {
            $upload_msg = "Folder uploaded successfully.";
        } else {
            $upload_msg = "Failed to upload folder.";
        }
    }
    header("Location: file-manager.php?upload_success=1");
    exit();
}

if (isset($_GET['delete'])) {
    $target = $base_dir . '/' . basename($_GET['delete']);
    if (is_dir($target)) {
        if (deleteFolder($target)) {
            $delete_msg = "Folder and its contents deleted successfully.";
        } else {
            $delete_msg = "Failed to delete folder.";
        }
    } elseif (file_exists($target)) {
        unlink($target);
        $delete_msg = "File deleted successfully.";
    } else {
        $delete_msg = "File or folder does not exist.";
    }
    header("Location: file-manager.php?delete_success=1");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>File Manager</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #121212;
            margin: 0;
            padding: 0;
            color: #e0e0e0;
        }
        h1 {
            text-align: center;
            padding: 20px 0;
            margin: 0;
            color: #f1f1f1;
        }
        p {
            text-align: center;
            color: #e0e0e0;
        }
        .container {
            width: 90%;
            max-width: 800px;
            margin: 30px auto;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.5);
            background: <?php echo isset($_SESSION['authenticated']) && $_SESSION['authenticated'] === true ? '#1f1f1f' : 'transparent'; ?>;
        }
        .btn {
            display: inline-block;
            background-color: #007BFF;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            font-size: 14px;
            cursor: pointer;
        }
        .btn:hover {
            background-color: #0056b3;
        }
        .btn.danger {
            background-color: #dc3545;
        }
        .btn.danger:hover {
            background-color: #a71d2a;
        }
        form {
            margin: 20px 0;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            justify-content: center;
        }
        form input[type="file"], form input[type="text"], form input[type="password"] {
            padding: 10px;
            font-size: 14px;
            background-color: #333;
            border: 1px solid #444;
            color: #e0e0e0;
        }
        form input[type="file"]:focus, form input[type="text"]:focus, form input[type="password"]:focus {
            border-color: #007BFF;
            outline: none;
        }
        form button {
            margin-top: 5px;
        }
        .msg {
            padding: 10px;
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
            border-radius: 5px;
            margin: 10px 0;
        }
        .msg.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        ul {
            list-style-type: none;
            padding: 0;
            color: #e0e0e0;
        }
        li {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px;
            border-bottom: 1px solid #444;
        }
        li span {
            word-break: break-word;
            max-width: 70%;
        }
        li a {
            color: #007BFF;
            text-decoration: none;
            font-size: 14px;
        }
        li a:hover {
            color:rgb(255, 0, 0);
            text-decoration: underline;
        }
        .login-container {
            display: flex;
            justify-content: center;
            align-items: center;
            height: calc(100vh - 150px);
            background-color: transparent;
            margin: 0;
            padding: 0;
        }
        .login-card {
            background-color: #1f1f1f;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.5);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }
        .login-card h2 {
            margin-bottom: 20px;
            color: #f1f1f1;
        }
        .login-card form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        .login-card form label {
            font-size: 14px;
            color: #e0e0e0;
            text-align: left;
        }
        .login-card form input[type="text"], 
        .login-card form input[type="password"] {
            width: 100%;
            padding: 12px;
            font-size: 14px;
            background-color: #333;
            border: 1px solid #444;
            color: #e0e0e0;
            border-radius: 5px;
        }
        .login-card form input[type="text"]:focus, 
        .login-card form input[type="password"]:focus {
            border-color: #007BFF;
            outline: none;
        }
        .login-card form button {
            padding: 12px;
            font-size: 16px;
            background-color: #007BFF;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        .login-card form button:hover {
            background-color: #0056b3;
        }
        .login-card .msg {
            margin-top: 10px;
            padding: 10px;
            border-radius: 5px;
            font-size: 14px;
        }
        .login-card .msg.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        form {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 15px;
        }
        form label {
            font-size: 16px;
            color: #e0e0e0;
            text-align: left;
            width: 100%;
        }
        form input[type="text"], form input[type="password"] {
            width: 100%;
            max-width: 300px;
            padding: 10px;
            font-size: 14px;
            background-color: #333;
            border: 1px solid #444;
            color: #e0e0e0;
            border-radius: 5px;
        }
        form input[type="text"]:focus, form input[type="password"]:focus {
            border-color: #007BFF;
            outline: none;
        }
        form button {
            width: 100%;
            max-width: 300px;
            padding: 10px;
            font-size: 16px;
            background-color: #007BFF;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        form button:hover {
            background-color: #0056b3;
        }
    </style>
    <script>
        function handleFolderInput(event) {
            const fileInput = event.target;
            const relativePaths = [];
            for (const file of fileInput.files) {
                relativePaths.push(file.webkitRelativePath || file.name);
            }
            document.getElementById('relative_paths').value = JSON.stringify(relativePaths);
        }
    </script>
</head>
<body>
    <h1>File Manager</h1>
    <?php if (isset($_SESSION['authenticated']) && $_SESSION['authenticated'] === true): ?>
        <div class="container">
            <p><a href="?logout=true" class="btn danger">Logout</a></p>
            <?php if (isset($_GET['upload_success'])) echo "<div class='msg'>Upload successful!</div>"; ?>
            <?php if (isset($_GET['delete_success'])) echo "<div class='msg'>Deletion successful!</div>"; ?>
            <?php if (!empty($upload_msg)) echo "<div class='msg'>$upload_msg</div>"; ?>
            <?php if (!empty($delete_msg)) echo "<div class='msg error'>$delete_msg</div>"; ?>
            <h2>Upload Files</h2>
            <form action="file-manager.php" method="POST" enctype="multipart/form-data">
                <input type="file" name="file_to_upload[]" multiple required>
                <button type="submit" class="btn">Upload Files</button>
            </form>
            <h2>Upload Folders</h2>
            <form action="file-manager.php" method="POST" enctype="multipart/form-data">
                <input type="file" name="folder_to_upload[]" webkitdirectory directory multiple onchange="handleFolderInput(event)" required>
                <input type="hidden" id="relative_paths" name="relative_paths">
                <button type="submit" class="btn">Upload Folder</button>
            </form>
            <h2>Files and Folders in Directory:</h2>
            <ul>
                <?php
                $items = array_diff(scandir($base_dir), array('.', '..', basename(__FILE__)));
                foreach ($items as $item) {
                    $path = $base_dir . '/' . $item;
                    if (is_dir($path)) {
                        echo "<li><span>[Folder] $item</span> <a href='?delete=$item' onclick='return confirm(\"Are you sure you want to delete this folder and its contents?\");'>Delete</a></li>";
                    } else {
                        echo "<li><span>[File] $item</span> <a href='?delete=$item' onclick='return confirm(\"Are you sure you want to delete this file?\");'>Delete</a></li>";
                    }
                }
                ?>
            </ul>
        </div>
    <?php else: ?>
        <div class="login-container">
            <div class="login-card">
                <h2>Login</h2>
                <?php if (isset($error)) echo "<div class='msg error'>$error</div>"; ?>
                <form action="file-manager.php" method="POST">
                    <label for="username">Username:</label>
                    <input type="text" id="username" name="username" required>
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" required>
                    <button type="submit" class="btn">Login</button>
                </form>
            </div>
        </div>
    <?php endif; ?>
</body>
</html>