<?php
require_once 'db.php';

if (!isset($_GET['quote_id'], $_GET['token'])) {
    die("Invalid request.");
}

$quoteId = trim($_GET['quote_id']);
$token = trim($_GET['token']);
$isDecline = isset($_GET['decline']); // if decline parameter is set, show decline form

$conn = getDbConnection();
$stmt = $conn->prepare("SELECT * FROM horloges WHERE ReparatieNummer = ? AND quote_token = ?");
$stmt->execute([$quoteId, $token]);
$quote = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$quote) {
    die("Invalid token or quotation not found.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $comment = $_POST['comment'] ?? '';

    if ($action === 'accept') {
        // Update the quote status to accepted
        $updateStmt = $conn->prepare("UPDATE horloges SET quote_status = 'accepted' WHERE ReparatieNummer = ?");
        $updateStmt->execute([$quoteId]);

        // Insert a notification for the repair operator (assumed operator id = 1 or 2)
        $operatorIds = [1, 2];
        foreach ($operatorIds as $opId) {
            $notifyStmt = $conn->prepare("INSERT INTO notifications (user_id, message, created_at) VALUES (?, ?, NOW())");
            $notifyStmt->execute([$opId, "Quotation #{$quoteId} has been accepted. You can now start the repair."]);
        }

        $_SESSION['success'] = "You have accepted the quotation.";
    } elseif ($action === 'decline') {
        if (strlen($comment) > 256) {
            $_SESSION['error'] = "Your comment cannot exceed 256 characters.";
            // Reload the same page to allow the user to correct the comment
            header("Location: " . $_SERVER['REQUEST_URI']);
            exit;
        }
        // Update the quote status to declined and store a comment
        $updateStmt = $conn->prepare("UPDATE horloges SET quote_status = 'declined', decline_comment = ? WHERE ReparatieNummer = ?");
        $updateStmt->execute([$comment, $quoteId]);
        $operatorIds = [1, 2];
        foreach ($operatorIds as $opId) {
            $notifyStmt = $conn->prepare("INSERT INTO notifications (user_id, message, created_at) VALUES (?, ?, NOW())");
            $message = "Quotation #{$quoteId} has been rejected. Comment: " . $comment;
            $notifyStmt->execute([$opId, $message]);
        }
        $_SESSION['success'] = "You have declined the quotation.";
    } else {
        $_SESSION['error'] = "Invalid action.";
    }
    // Instead of redirecting to thank_you.php, show a simple confirmation page
    $responseMessage = $_SESSION['success'] ?? $_SESSION['error'] ?? "Your response has been recorded.";
    // Clear messages from session
    unset($_SESSION['success'], $_SESSION['error']);
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
      <meta charset="UTF-8">
      <title>Quotation Response Received</title>
      <style>
          body { font-family: Arial, sans-serif; padding: 2rem; }
          a { text-decoration: none; color: blue; }
      </style>
    </head>
    <body>
      <h2>Thank You</h2>
      <p><?= htmlspecialchars($responseMessage) ?></p>
      <p><a href="/home">Return to Home</a></p>
    </body>
    </html>
    <?php
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Quotation Response</title>
  <style>
      body { font-family: Arial, sans-serif; padding: 2rem; }
      form { max-width: 600px; margin: 0 auto; }
      textarea { width: 100%; }
      button { margin-top: 1rem; padding: 0.5rem 1rem; }
  </style>
</head>
<body>
<h2>Quotation #<?= htmlspecialchars($quoteId) ?></h2>
<?php
// If there's an error message in the session, display it.
if (isset($_SESSION['error'])):
?>
  <div class="error-message">
      <?= htmlspecialchars($_SESSION['error']) ?>
  </div>
<?php
    unset($_SESSION['error']); // Clear the error message so it doesn't persist.
endif;
?>
<p>Please review your quotation. You may accept it or decline it with a comment.</p>
<form method="POST">
    <?php if ($isDecline): ?>
        <button type="submit" name="action" value="decline">Decline Quotation</button>
        <br><br>
        <label for="comment">Please provide a reason for declining:</label><br>
        <textarea name="comment" id="comment" rows="4" required></textarea>
    <?php else: ?>
        <button type="submit" name="action" value="accept">Accept Quotation</button>
        <br><br>
        <label for="comment">Or, if you prefer to decline, leave a comment here and click the button below:</label><br>
        <textarea name="comment" id="comment" rows="4"></textarea>
        <button type="submit" name="action" value="decline">Decline Quotation</button>
    <?php endif; ?>
</form>
</body>
</html>
