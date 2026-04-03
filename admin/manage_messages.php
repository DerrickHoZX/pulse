<?php
include "../inc/admin_check.inc.php";
require_once "../inc/db.inc.php";
$basePath = "../";

$conn = getDBConnection();

// Search/filter
$search = trim($_GET['q'] ?? '');
$reason = trim($_GET['reason'] ?? '');

$sql = "SELECT * FROM contact_messages WHERE 1=1";
$params = [];
$types = '';

if ($search) {
    $like = "%$search%";
    $sql .= " AND (name LIKE ? OR email LIKE ? OR message LIKE ?)";
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $types .= 'sss';
}
if ($reason) {
    $sql .= " AND reason = ?";
    $params[] = $reason;
    $types .= 's';
}

$sql .= " ORDER BY submitted_at DESC";

$stmt = $conn->prepare($sql);
if ($params)
    $stmt->bind_param($types, ...$params);
$stmt->execute();
$messages = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Get unique reasons for filter dropdown
$reasons = $conn->query("SELECT DISTINCT reason FROM contact_messages ORDER BY reason")->fetch_all(MYSQLI_ASSOC);

// Summary count
$total = count($messages);
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title>PULSE Admin - Messages</title>
    <?php include "../inc/head.inc.php"; ?>
</head>

<body>
    <?php include "../inc/nav.inc.php"; ?>
    <div style="margin-top: 100px;"></div>

    <main class="container-fluid px-5 py-5">
        <div class="d-flex justify-content-between align-items-end mb-4">
            <div>
                <span class="section-label">Administration</span>
                <h2 class="section-title">User <em>Messages</em></h2>
            </div>
            <a href="admin.php" class="btn btn-outline-light">Back</a>
        </div>

        <!-- Filters -->
        <form method="GET" action="manage_messages.php" class="d-flex gap-2 flex-wrap mb-4">
            <input type="text" name="q" class="form-control admin-form-control" style="max-width:280px;"
                placeholder="Search name, email, message..." value="<?= htmlspecialchars($search) ?>">

            <select id="reason" name="reason" aria-label="Filter by reason" class="form-control admin-form-control"
                style="max-width:200px;" onchange="this.form.submit()">
                <option value="">All Reasons</option>
                <?php foreach ($reasons as $r): ?>
                    <option value="<?= htmlspecialchars($r['reason']) ?>" <?= $reason === $r['reason'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($r['reason']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <button type="submit" class="btn-dark-solid">Search</button>
            <?php if ($search || $reason): ?>
                <a href="manage_messages.php" class="btn btn-outline-light">Clear</a>
            <?php endif; ?>

            <span style="margin-left:auto;font-size:0.8rem;color:var(--pulse-muted);align-self:center;">
                <strong style="color:var(--pulse-white);"><?= $total ?></strong> message<?= $total !== 1 ? 's' : '' ?>
            </span>
        </form>

        <!-- Messages -->
        <?php if (empty($messages)): ?>
            <div style="text-align:center;padding:60px 0;color:var(--pulse-muted);">
                No messages found.
            </div>
        <?php else: ?>
            <div class="d-flex flex-column gap-3">
                <?php foreach ($messages as $msg): ?>
                    <div class="admin-panel-card p-4">
                        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                            <!-- Left: message info -->
                            <div style="flex:1;min-width:250px;">
                                <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                                    <strong style="color:var(--pulse-white);"><?= htmlspecialchars($msg['name']) ?></strong>
                                    <span class="badge bg-secondary"><?= htmlspecialchars($msg['reason']) ?></span>
                                    <span style="font-size:0.75rem;color:var(--pulse-muted);">
                                        <?= date('d M Y, g:i A', strtotime($msg['submitted_at'])) ?>
                                    </span>
                                </div>
                                <p style="color:var(--pulse-muted);font-size:0.82rem;margin:0 0 12px;">
                                    <?= nl2br(htmlspecialchars($msg['message'])) ?>
                                </p>
                            </div>

                            <!-- Right: contact options -->
                            <div class="d-flex flex-column gap-2" style="min-width:200px;">
                                <a href="mailto:<?= htmlspecialchars($msg['email']) ?>?subject=Re: <?= urlencode($msg['reason']) ?> - PULSE Support"
                                    class="btn-dark-solid text-center"
                                    style="font-size:0.75rem;padding:10px 16px;text-decoration:none;">
                                    ✉ Reply via Email
                                </a>
                                <?php if ($msg['phone']): ?>
                                    <a href="tel:<?= htmlspecialchars($msg['phone']) ?>" class="btn btn-outline-light"
                                        style="font-size:0.75rem;padding:10px 16px;text-align:center;">
                                        📞 <?= htmlspecialchars($msg['phone']) ?>
                                    </a>
                                <?php endif; ?>
                                <div style="font-size:0.72rem;color:var(--pulse-muted);">
                                    <?= htmlspecialchars($msg['email']) ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>

    <?php include "../inc/footer.inc.php"; ?>
</body>

</html>