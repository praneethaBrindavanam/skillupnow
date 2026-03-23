<?php
session_start();
require_once 'admin-config.php';

requireAdminLogin();

$pageTitle = "View Tutor";

/* ===============================
   VALIDATE TUTOR ID
================================ */
$userId = intval($_GET['id'] ?? 0);

if ($userId <= 0) {
    $_SESSION['error'] = "Invalid tutor ID.";
    header("Location: manage-tutors.php");
    exit();
}

$conn = getDbConnection();

/* ===============================
   TOGGLE TUTOR VERIFICATION
================================ */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'toggle_verification') {
    $stmt = $conn->prepare("
        UPDATE users 
        SET is_tutor_verified = 1 - is_tutor_verified
        WHERE user_id = ? AND user_type = 'tutor'
    ");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->close();

    $_SESSION['success'] = "Tutor verification status updated.";
    header("Location: view-tutor.php?id=$userId");
    exit();
}

/* ===============================
   FETCH TUTOR (USERS ONLY)
================================ */
$stmt = $conn->prepare("
    SELECT *
    FROM users
    WHERE user_id = ? AND user_type = 'tutor'
");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $stmt->close();
    closeDbConnection($conn);
    $_SESSION['error'] = "Tutor not found.";
    header("Location: manage-tutors.php");
    exit();
}

$tutor = $result->fetch_assoc();
$stmt->close();
closeDbConnection($conn);

include 'admin-header.php';
?>

<?php if (!empty($_SESSION['success'])): ?>
<div style="max-width:1200px;margin:0 auto 2rem;
            background:#D1FAE5;border:2px solid #10B981;
            border-radius:1rem;padding:1rem;">
    <i class="fas fa-check-circle"></i>
    <?= htmlspecialchars($_SESSION['success']) ?>
</div>
<?php unset($_SESSION['success']); endif; ?>

<div style="max-width:1200px;margin:0 auto">

<!-- BACK -->
<a href="manage-tutors.php"
   style="color:var(--primary-teal);text-decoration:none;font-weight:600">
← Back to Tutors
</a>

<!-- HEADER -->
<div style="background:white;border-radius:1rem;
            box-shadow:0 1px 3px rgba(0,0,0,0.1);
            margin:1.5rem 0;overflow:hidden">

    <div style="background:linear-gradient(135deg,var(--primary-cyan),var(--primary-teal));
                padding:2rem;color:white;display:flex;
                justify-content:space-between;align-items:center">

        <div style="display:flex;align-items:center;gap:1.5rem">
            <div style="width:80px;height:80px;border-radius:50%;
                        background:white;color:var(--primary-teal);
                        display:flex;align-items:center;justify-content:center;
                        font-size:2rem;font-weight:700">
                <?= strtoupper(substr($tutor['full_name'], 0, 1)) ?>
            </div>
            <div>
                <h1 style="margin:0"><?= htmlspecialchars($tutor['full_name']) ?></h1>
                <p style="margin:0;opacity:.9">
                    Tutor • <?= htmlspecialchars($tutor['custom_user_id']) ?>
                </p>
            </div>
        </div>

        <form method="POST">
            <input type="hidden" name="action" value="toggle_verification">
            <button class="btn"
                style="background:<?= $tutor['is_tutor_verified'] ? '#F59E0B' : '#10B981' ?>;
                       color:white">
                <?= $tutor['is_tutor_verified'] ? 'Unverify' : 'Verify' ?> Tutor
            </button>
        </form>
    </div>

    <!-- BODY -->
    <div style="padding:2rem">

        <h3>Basic Information</h3>
        <p><b>Username:</b> @<?= htmlspecialchars($tutor['username']) ?></p>
        <p><b>Email:</b> <?= htmlspecialchars($tutor['email']) ?></p>
        <p><b>Phone:</b> <?= $tutor['phone'] ?: '-' ?></p>
        <p><b>College:</b> <?= $tutor['college_name'] ?: '-' ?></p>

        <p><b>Status:</b>
            <?= $tutor['is_active']
                ? '<span style="color:green">Active</span>'
                : '<span style="color:red">Inactive</span>' ?>
        </p>

        <p><b>Email Verified:</b>
            <?= $tutor['is_verified']
                ? '<span style="color:green">Yes</span>'
                : '<span style="color:red">No</span>' ?>
        </p>

        <p><b>Tutor Verified:</b>
            <?= $tutor['is_tutor_verified']
                ? '<span style="color:green">Yes</span>'
                : '<span style="color:#F59E0B">Pending</span>' ?>
        </p>

        <?php if (!empty($tutor['bio'])): ?>
        <hr>
        <h3>Bio</h3>
        <p><?= nl2br(htmlspecialchars($tutor['bio'])) ?></p>
        <?php endif; ?>

        <hr>
        <h3>Account Info</h3>
        <p><b>Created:</b>
            <?= date('M d, Y h:i A', strtotime($tutor['created_at'])) ?>
        </p>

        <p><b>Last Login:</b>
            <?= $tutor['last_login']
                ? date('M d, Y h:i A', strtotime($tutor['last_login']))
                : 'Never' ?>
        </p>

    </div>
</div>

</div>

<?php include 'admin-footer.php'; ?>