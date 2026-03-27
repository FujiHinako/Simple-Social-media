<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$data_dir = __DIR__ . '/data/';
$users_file = $data_dir . 'users.json';

$users = json_decode(file_get_contents($users_file), true) ?: [];
$user = null;
foreach ($users as $u) {
    if ($u['id'] === $user_id) {
        $user = $u;
        break;
    }
}

if (!$user || $user['id'] !== $user_id) {
    die('Access denied');
}

if ($_POST) {
    $user['first_name'] = $_POST['first_name'];
    $user['middle_name'] = $_POST['middle_name'] ?? '';
    $user['last_name'] = $_POST['last_name'];
    $user['bio'] = $_POST['bio'];
    $user['email'] = $_POST['email'];

    $user_full_name = trim($user['first_name'] . ' ' . ($user['middle_name'] ?? '') . ' ' . $user['last_name']);

    // Handle profile photo upload
    if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = __DIR__ . '/images/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
        
        $file_ext = strtolower(pathinfo($_FILES['profile_photo']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (in_array($file_ext, $allowed) && $_FILES['profile_photo']['size'] < 5*1024*1024) { // 5MB
            $filename = $user_id . '_' . time() . '.' . $file_ext;
            $filepath = $upload_dir . $filename;
            
            if (move_uploaded_file($_FILES['profile_photo']['tmp_name'], $filepath)) {
                $user['profile_photo'] = 'images/' . $filename;

                // Auto-post new profile photo
                $posts_file = $data_dir . 'posts.json';
                $posts = json_decode(file_get_contents($posts_file), true) ?: [];
                $new_post = [
                    'id' => uniqid(),
                    'user_id' => $user['id'],
                    'user_name' => $user_full_name,
                    'content' => 'Updated my profile photo 👤✨',
                    'image' => $user['profile_photo'],
                    'likes' => 0,
                    'comments' => [],
                    'created_at' => date('c')
                ];
                $posts[] = $new_post;
                file_put_contents($posts_file, json_encode($posts, JSON_PRETTY_PRINT));
            }
        }
    }

    // Handle cover photo upload
    if (isset($_FILES['cover_photo']) && $_FILES['cover_photo']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = __DIR__ . '/images/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
        
        $file_ext = strtolower(pathinfo($_FILES['cover_photo']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (in_array($file_ext, $allowed) && $_FILES['cover_photo']['size'] < 5*1024*1024) { // 5MB
            $filename = $user_id . '_cover_' . time() . '.' . $file_ext;
            $filepath = $upload_dir . $filename;
            
            if (move_uploaded_file($_FILES['cover_photo']['tmp_name'], $filepath)) {
                $user['cover_photo'] = 'images/' . $filename;

                // Auto-post new cover photo
                $posts_file = $data_dir . 'posts.json';
                $posts = json_decode(file_get_contents($posts_file), true) ?: [];
                $new_post = [
                    'id' => uniqid(),
                    'user_id' => $user['id'],
                    'user_name' => $user_full_name,
                    'content' => 'Updated my cover photo! 🌅✨',
                    'image' => $user['cover_photo'],
                    'likes' => 0,
                    'comments' => [],
                    'created_at' => date('c')
                ];
                $posts[] = $new_post;
                file_put_contents($posts_file, json_encode($posts, JSON_PRETTY_PRINT));
            }
        }
    }

    $index = array_search($user['id'], array_column($users, 'id'));
    $users[$index] = $user;
    file_put_contents($users_file, json_encode($users, JSON_PRETTY_PRINT));
    header('Location: profile.php?uid=' . $user_id);
    exit;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile - ConnectHub</title>
    <!-- Bootstrap + MDB -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/mdb-ui-kit@7.0.0/dist/css/mdb.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/mdb-ui-kit@7.0.0/dist/js/mdb.umd.min.js"></script>
    <script>
    function readURL(input, previewId) {
        if (input.files && input.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById(previewId).src = e.target.result;
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
    document.getElementById('profilePhoto').addEventListener('change', function() {
        readURL(this, 'profilePreview');
    });
    document.getElementById('coverPhoto').addEventListener('change', function() {
        readURL(this, 'coverPreview');
    });
    </script>
</head>
<body class="bg-light">
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-lg-8 col-xl-6">
                <div class="card shadow-lg border-0 rounded-4">
                    <div class="card-header bg-gradient text-white p-4 rounded-top-4">
                        <h2 class="mb-0"style="color: black;">
                            <i class="fas fa-edit me-3" ></i>Edit Profile
                        </h2>
                    </div>
                    <div class="card-body p-5">

<form method="POST" enctype="multipart/form-data">


<div class="row mb-4">
    <div class="col-md-3 text-center">
        <div class="avatar-upload position-relative">
            <img src="<?php echo htmlspecialchars($user['profile_photo'] ?? 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTIwIiBoZWlnaHQ9IjEyMCIgdmlld0JveD0iMCAwIDEyMCAxMjAiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGNpcmNsZSBjeD0iNjAiIGN5PSI2MCIgcj0iNjAiIGZpbGw9IiNkZGQiLz48L3N2Zz4='); ?>" id="profilePreview" class="rounded-circle shadow-4 mb-3" style="width: 120px; height: 120px; object-fit: cover;">
            <div class="upload-overlay position-absolute top-50 start-50 translate-middle">
                <label for="profilePhoto" class="btn btn-primary btn-sm rounded-circle p-2 cursor-pointer">
                    <i class="fas fa-camera"></i>
                </label>
                <input type="file" id="profilePhoto" name="profile_photo" class="d-none" accept="image/*">
            </div>
        </div>
        <small class="text-muted">Click camera icon to change</small>
    </div>
    <div class="col-md-9">
        <div class="row">
            <div class="col-md-6 mb-3">
                <div class="form-outline">
                    <input type="text" class="form-control form-control-lg" name="first_name" value="<?php echo htmlspecialchars($user['first_name']); ?>" required />
                    <label class="form-label">First Name</label>
                </div>
            </div>
            <div class="col-md-6 mb-3">
                <div class="form-outline">
                    <input type="text" class="form-control form-control-lg" name="middle_name" value="<?php echo htmlspecialchars($user['middle_name'] ?? ''); ?>" />
                    <label class="form-label">Middle Name</label>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Cover Photo Upload Section -->
<div class="row mb-4">
    <div class="col-12">
        <label class="form-label fw-bold">Cover Photo</label>
        <div class="position-relative mb-3">
            <img src="<?php echo htmlspecialchars($user['cover_photo'] ?? 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIyMDBweCIgdmlld0JveD0iMCAwIDE2MDAgOTAwIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciPjxwYXRoIGQ9Ik0wLDloOTAwbDMwMCw2MDBsLTYwMCw0MDB6IiBmaWxsPSIjZGRkIi8+PHRleHQgeD0iNTAlIiB5PSI1NSUiIGZvbnQtc2l6ZT0iMzIiIGZpbGw9IiM5OTkiIHRleHQtYW5jaG9yPSJtaWRkbGUiIGR5PSIuM2VtIj5ObyBDb3ZlciBQaG90byBBZGRlZDwvdGV4dD48L3N2Zz4='); ?>" id="coverPreview" class="img-fluid rounded shadow-4" style="height: 200px; object-fit: cover; width: 100%;">
            <div class="upload-overlay position-absolute top-50 start-50 translate-middle">
                <label for="coverPhoto" class="btn btn-primary btn-lg rounded-circle p-3 cursor-pointer shadow">
                    <i class="fas fa-image fs-4"></i>
                </label>
                <input type="file" id="coverPhoto" name="cover_photo" class="d-none" accept="image/*">
            </div>
        </div>
        <small class="text-muted">Click image icon to upload cover photo (JPG, PNG, max 5MB)</small>
    </div>
</div>

                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <div class="form-outline">
                                        <input type="text" class="form-control form-control-lg" name="last_name" value="<?php echo htmlspecialchars($user['last_name']); ?>" required />
                                        <label class="form-label">Last Name</label>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-4">
                                    <div class="form-outline">
                                        <input type="email" class="form-control form-control-lg" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required />
                                        <label class="form-label">Email</label>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-4">
                                <div class="form-outline">
                                    <textarea class="form-control form-control-lg" name="bio" rows="4" style="resize: vertical;"><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>
                                    <label class="form-label">Bio</label>
                                </div>
                            </div>
                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <a href="profile.php?uid=<?php echo $user_id; ?>" class="btn btn-outline-secondary btn-lg me-md-2">
                                    Cancel
                                </a>
                                <button type="submit" class="btn btn-primary btn-lg px-5">
                                    <i class="fas fa-save me-2"></i>Save Changes
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

