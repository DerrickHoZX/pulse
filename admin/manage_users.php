<?php
include "../inc/admin_check.inc.php";
require_once "../inc/db.inc.php";
$basePath = "../";

$conn = getDBConnection();
$message = '';
$error   = '';

// Handle role toggle and unlock actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action  = $_POST['action'] ?? '';
    $user_id = intval($_POST['user_id'] ?? 0);

    if ($user_id === $_SESSION['user_id']) {
        $error = 'You cannot modify your own account.';
    } elseif ($action === 'toggle_role') {
        $current_role = $_POST['current_role'] ?? 'member';
        $new_role     = $current_role === 'admin' ? 'member' : 'admin';
        $stmt = $conn->prepare("UPDATE users SET role = ? WHERE user_id = ?");
        $stmt->bind_param('si', $new_role, $user_id);
        $stmt->execute();
        $stmt->close();
        $message = 'User role updated successfully.';
    } elseif ($action === 'unlock') {
        $stmt = $conn->prepare("UPDATE users SET login_attempts = 0, lockout_until = NULL WHERE user_id = ?");
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $stmt->close();
        $message = 'User account unlocked.';
    } elseif ($action === 'delete') {
        $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $stmt->close();
        $message = 'User deleted successfully.';
    }
}

// Search
$search = trim($_GET['q'] ?? '');
$sql    = "SELECT user_id, fname, lname, email, role, created_at, login_attempts, lockout_until FROM users";
if ($search) {
    $like = "%$search%";
    $sql .= " WHERE fname LIKE ? OR lname LIKE ? OR email LIKE ?";
}
$sql .= " ORDER BY created_at DESC";

$stmt = $conn->prepare($sql);
if ($search) {
    $stmt->bind_param('sss', $like, $like, $like);
}
$stmt->execute();
$users = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>PULSE Admin - Manage Users</title>
    <?php include "../inc/head.inc.php"; ?>
</head>
<body>
    <?php include "../inc/nav.inc.php"; ?>
    <div style="margin-top: 100px;"></div>

    <main class="container-fluid px-5 py-5">
        <div class="d-flex justify-content-between align-items-end mb-4">
            <div>
                <span class="section-label">Administration</span>
                <h2 class="section-title">Manage <em>Users</em></h2>
            </div>
            <a href="index.php" class="btn btn-outline-light">Back</a>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-success mb-4"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-danger mb-4"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <!-- Search -->
        <form method="GET" action="manage_users.php" class="mb-4">
            <div class="d-flex gap-2" style="max-width: 400px;">
                <input type="text" name="q" class="form-control admin-form-control"
                       placeholder="Search by name or email..."
                       value="<?= htmlspecialchars($search) ?>">
                <button type="submit" class="btn-dark-solid" style="white-space:nowrap;">Search</button>
                <?php if ($search): ?>
                    <a href="manage_users.php" class="btn btn-outline-light" style="white-space:nowrap;">Clear</a>
                <?php endif; ?>
            </div>
        </form>

        <div class="admin-panel-card">
            <div class="table-responsive">
                <table class="table admin-table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Joined</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($users)): ?>
                            <tr><td colspan="7" class="text-center">No users found.</td></tr>
                        <?php else: ?>
                            <?php foreach ($users as $u):
                                $isLocked = $u['lockout_until'] && strtotime($u['lockout_until']) > time();
                                $isSelf   = $u['user_id'] === $_SESSION['user_id'];
                            ?>
                            <tr>
                                <td><?= $u['user_id'] ?></td>
                                <td><?= htmlspecialchars($u['fname'] . ' ' . $u['lname']) ?></td>
                                <td><?= htmlspecialchars($u['email']) ?></td>
                                <td>
                                    <?php if ($u['role'] === 'admin'): ?>
                                        <span class="badge bg-primary">Admin</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Member</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= date('d M Y', strtotime($u['created_at'])) ?></td>
                                <td>
                                    <?php if ($isLocked): ?>
                                        <span class="badge bg-danger">Locked</span>
                                    <?php elseif ($u['login_attempts'] >= 2): ?>
                                        <span class="badge bg-warning text-dark">At Risk</span>
                                    <?php else: ?>
                                        <span class="badge bg-success">Active</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!$isSelf): ?>
                                    <div class="d-flex gap-2 flex-wrap">
                                        <!-- Toggle Role -->
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="action" value="toggle_role">
                                            <input type="hidden" name="user_id" value="<?= $u['user_id'] ?>">
                                            <input type="hidden" name="current_role" value="<?= $u['role'] ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-primary"
                                                    onclick="return confirm('Change this user\'s role?')">
                                                <?= $u['role'] === 'admin' ? 'Demote' : 'Make Admin' ?>
                                            </button>
                                        </form>

                                        <!-- Unlock -->
                                        <?php if ($isLocked || $u['login_attempts'] > 0): ?>
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="action" value="unlock">
                                            <input type="hidden" name="user_id" value="<?= $u['user_id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-warning">Unlock</button>
                                        </form>
                                        <?php endif; ?>

                                        <!-- Delete -->
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="user_id" value="<?= $u['user_id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger"
                                                    onclick="return confirm('Permanently delete this user?')">Delete</button>
                                        </form>
                                    </div>
                                    <?php else: ?>
                                        <span style="color:var(--pulse-muted);font-size:0.78rem;">You</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <?php include "../inc/footer.inc.php"; ?>
</body>
</html>