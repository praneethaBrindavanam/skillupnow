<?php
session_start();
require_once 'admin-config.php';

requireAdminLogin();

$pageTitle = "View Student";
include 'admin-header.php';

/* ===============================
   CURRENT ADMIN INFO
================================ */
$currentAdminId   = getCurrentAdminId();
$currentAdminRole = getCurrentAdminRole();

/* ===============================
   VALIDATE STUDENT ID
================================ */
$userId = intval($_GET['id'] ?? 0);

if ($userId <= 0) {
    $_SESSION['error'] = "Invalid student ID.";
    header("Location: manage-students.php");
    exit();
}

/* ===============================
   DB CONNECTION
================================ */
$conn = getDbConnection();

/* ===============================
   FETCH STUDENT (USERS TABLE)
================================ */
$stmt = $conn->prepare("
    SELECT *
    FROM users
    WHERE user_id = ?
      AND user_type = 'learner'
");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $stmt->close();
    closeDbConnection($conn);
    $_SESSION['error'] = "Student not found.";
    header("Location: manage-students.php");
    exit();
}

$student = $result->fetch_assoc();
$stmt->close();
closeDbConnection($conn);
?>

<!-- ================= BACK BUTTON ================= -->
<div style="margin-bottom: 2rem;">
    <a href="manage-students.php"
       style="color: var(--primary-teal); font-weight: 600; text-decoration: none;">
        <i class="fas fa-arrow-left"></i> Back to Students
    </a>
</div>

<!-- ================= STUDENT PROFILE CARD ================= -->
<div style="background:white;border-radius:1rem;box-shadow:0 1px 3px rgba(0,0,0,0.1);overflow:hidden;margin-bottom:2rem;">

    <!-- HEADER -->
    <div style="background:linear-gradient(135deg,var(--primary-cyan),var(--primary-teal));
                padding:2rem;color:white;">
        <div style="display:flex;align-items:center;gap:1.5rem;">
            <div style="width:80px;height:80px;border-radius:50%;
                        background:white;display:flex;align-items:center;
                        justify-content:center;font-size:2rem;font-weight:700;
                        color:var(--primary-teal);">
                <?= strtoupper(substr($student['full_name'], 0, 1)) ?>
            </div>
            <div>
                <h1 style="margin:0"><?= htmlspecialchars($student['full_name']) ?></h1>
                <p style="opacity:0.9;margin:0">
                    Student • <?= htmlspecialchars($student['custom_user_id']) ?>
                </p>
            </div>
        </div>
    </div>

    <!-- BODY -->
    <div style="padding:2rem;">

        <h3>Basic Information</h3>
        <p><b>Username:</b> @<?= htmlspecialchars($student['username']) ?></p>
        <p><b>Email:</b> <?= htmlspecialchars($student['email']) ?></p>
        <p><b>Phone:</b> <?= $student['phone'] ?: '-' ?></p>
        <p><b>College:</b> <?= $student['college_name'] ?: '-' ?></p>

        <p><b>Status:</b>
            <?= $student['is_active']
                ? '<span style="color:green">Active</span>'
                : '<span style="color:red">Inactive</span>' ?>
        </p>

        <p><b>Email Verified:</b>
            <?= $student['is_verified']
                ? '<span style="color:green">Yes</span>'
                : '<span style="color:red">No</span>' ?>
        </p>

        <?php if (!empty($student['bio'])): ?>
            <hr>
            <h3>Bio</h3>
            <p><?= nl2br(htmlspecialchars($student['bio'])) ?></p>
        <?php endif; ?>

        <hr>
        <h3>Account Info</h3>
        <p><b>Created At:</b>
            <?= date('M d, Y h:i A', strtotime($student['created_at'])) ?>
        </p>

        <p><b>Last Login:</b>
            <?= $student['last_login']
                ? date('M d, Y h:i A', strtotime($student['last_login']))
                : 'Never' ?>
        </p>

    </div>
</div>

<?php include 'admin-footer.php'; ?>