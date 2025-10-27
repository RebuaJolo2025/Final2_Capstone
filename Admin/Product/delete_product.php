<?php
// Delete products endpoint
header('Content-Type: application/json');
include '../conn.php';

if (!$conn || $conn->connect_error) {
  echo json_encode(['success' => false, 'message' => 'DB connection error']);
  exit;
}

try {
  // Accept JSON body: { ids: [1,2,3] }
  $raw = file_get_contents('php://input');
  $data = json_decode($raw, true);
  $ids = [];
  if (isset($data['ids']) && is_array($data['ids'])) {
    $ids = array_values(array_filter($data['ids'], function($v){ return is_numeric($v); }));
  } elseif (isset($_POST['ids'])) { // fallback form-encoded
    $ids = array_map('intval', (array)$_POST['ids']);
  }

  if (empty($ids)) {
    echo json_encode(['success' => false, 'message' => 'No product IDs provided']);
    exit;
  }

  // Fetch images to delete
  $inPlaceholders = implode(',', array_fill(0, count($ids), '?'));
  $types = str_repeat('i', count($ids));

  $images = [];
  if ($stmt = $conn->prepare("SELECT id, images FROM products WHERE id IN ($inPlaceholders)")) {
    $stmt->bind_param($types, ...$ids);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
      if (!empty($row['images'])) {
        $arr = json_decode($row['images'], true);
        if (is_array($arr)) {
          foreach ($arr as $p) { if (is_string($p)) { $images[] = $p; } }
        }
      }
    }
    $stmt->close();
  }

  // Delete rows
  if ($stmt = $conn->prepare("DELETE FROM products WHERE id IN ($inPlaceholders)")) {
    $stmt->bind_param($types, ...$ids);
    $ok = $stmt->execute();
    $affected = $stmt->affected_rows;
    $stmt->close();

    // Best-effort file deletion
    foreach ($images as $path) {
      $safe = str_replace(['..', "\0"], '', $path);
      if ($safe && file_exists($safe)) { @unlink($safe); }
    }

    echo json_encode(['success' => $ok, 'deleted' => $affected]);
    exit;
  }

  echo json_encode(['success' => false, 'message' => 'Prepare failed']);
} catch (Throwable $e) {
  echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
