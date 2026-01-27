<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/database.php';

// Check if user is admin (simple check - enhance as needed)
session_start();
if (!isset($_SESSION['admin_user'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$type = $_GET['type'] ?? '';
$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($type) {
        case 'banner':
            handleBanner($pdo, $method);
            break;
        case 'product_image':
            handleProductImage($pdo, $method);
            break;
        case 'product_details':
            handleProductDetails($pdo, $method);
            break;
        case 'product_details_image':
            handleProductDetailsImage($pdo, $method);
            break;
        case 'review':
            handleReview($pdo, $method);
            break;
        case 'video':
            handleVideo($pdo, $method);
            break;
        case 'social':
            handleSocial($pdo, $method);
            break;
        case 'settings':
            handleSettings($pdo, $method);
            break;
        case 'feature_card':
            handleFeatureCard($pdo, $method);
            break;
        default:
            throw new Exception('Invalid type');
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

function handleBanner($pdo, $method) {
    if ($method === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        $id = $data['id'] ?? null;
        
        if ($id) {
            // Update
            $stmt = $pdo->prepare('UPDATE banners SET title = ?, subtitle = ?, image_path = ?, display_order = ?, is_active = ? WHERE id = ?');
            $stmt->execute([$data['title'], $data['subtitle'] ?? '', $data['image_path'] ?? '', $data['display_order'], $data['is_active'], $id]);
        } else {
            // Insert
            $stmt = $pdo->prepare('INSERT INTO banners (title, subtitle, image_path, display_order, is_active) VALUES (?, ?, ?, ?, ?)');
            $stmt->execute([$data['title'], $data['subtitle'] ?? '', $data['image_path'] ?? '', $data['display_order'], $data['is_active']]);
        }
        
        echo json_encode(['success' => true, 'message' => 'Banner saved']);
    } elseif ($method === 'DELETE') {
        $id = $_GET['id'] ?? 0;
        $stmt = $pdo->prepare('DELETE FROM banners WHERE id = ?');
        $stmt->execute([$id]);
        echo json_encode(['success' => true, 'message' => 'Banner deleted']);
    }
}

function handleProductImage($pdo, $method) {
    if ($method === 'POST') {
        $id = $_POST['id'] ?? null;
        $alt_text = $_POST['alt_text'] ?? '';
        $display_order = $_POST['display_order'] ?? 0;
        $current_image = $_POST['current_image'] ?? '';
        
        $image_path = $current_image;
        
        // Handle file upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = __DIR__ . '/../../assets/images/uploads/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $file_name = 'product_' . time() . '_' . uniqid() . '.' . $file_extension;
            $target_path = $upload_dir . $file_name;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $target_path)) {
                $image_path = 'assets/images/uploads/' . $file_name;
                
                // Delete old image if updating
                if ($current_image && file_exists(__DIR__ . '/../../' . $current_image)) {
                    unlink(__DIR__ . '/../../' . $current_image);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to upload image']);
                return;
            }
        } elseif (!$id) {
            // New image requires a file
            echo json_encode(['success' => false, 'message' => 'Image file is required']);
            return;
        }
        
        if ($id) {
            // Update
            $stmt = $pdo->prepare('UPDATE product_images SET image_path = ?, alt_text = ?, display_order = ? WHERE id = ?');
            $stmt->execute([$image_path, $alt_text, $display_order, $id]);
        } else {
            // Insert
            $stmt = $pdo->prepare('INSERT INTO product_images (image_path, alt_text, display_order) VALUES (?, ?, ?)');
            $stmt->execute([$image_path, $alt_text, $display_order]);
        }
        
        echo json_encode(['success' => true, 'message' => 'Product image saved']);
    } elseif ($method === 'DELETE') {
        $id = $_GET['id'] ?? 0;
        
        // Get image path before deleting
        $stmt = $pdo->prepare('SELECT image_path FROM product_images WHERE id = ?');
        $stmt->execute([$id]);
        $image = $stmt->fetch();
        
        if ($image) {
            // Delete file
            $file_path = __DIR__ . '/../../' . $image['image_path'];
            if (file_exists($file_path)) {
                unlink($file_path);
            }
            
            // Delete from database
            $stmt = $pdo->prepare('DELETE FROM product_images WHERE id = ?');
            $stmt->execute([$id]);
        }
        
        echo json_encode(['success' => true, 'message' => 'Product image deleted']);
    }
}

function handleProductDetails($pdo, $method) {
    if ($method === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        // Ensure new columns exist (safe migration) to avoid SQL errors when saving
        try {
            $cols = [
                'accordion_title' => 'VARCHAR(500)',
                'accordion_description' => 'TEXT',
                'features_heading' => 'VARCHAR(255)',
                'features_items' => 'TEXT',
                'order_image' => 'VARCHAR(255)'
            ];
            foreach ($cols as $col => $type) {
                $check = $pdo->prepare("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'product_details' AND COLUMN_NAME = ?");
                $check->execute([$col]);
                if (!$check->fetch()) {
                    $pdo->exec("ALTER TABLE product_details ADD COLUMN `" . $col . "` " . $type . " DEFAULT NULL");
                }
            }
        } catch (Throwable $e) {
            // Migration failure should not block save; log and continue
            error_log('Migration error adding product_details columns: ' . $e->getMessage());
        }
        
        // Check if record exists
        $stmt = $pdo->query('SELECT id FROM product_details WHERE product_id = 1');
        $exists = $stmt->fetch();
        
        $accordion_title = $data['accordion_title'] ?? '';
        $accordion_description = $data['accordion_description'] ?? '';
        $features_heading = $data['features_heading'] ?? '';
        $features_items = $data['features_items'] ?? '';

        if ($exists) {
            // Update (extended fields)
            $stmt = $pdo->prepare('UPDATE product_details SET 
                name = ?, bengali_name = ?, description = ?, price = ?, discount_price = ?, features = ?, 
                accordion_title = ?, accordion_description = ?, features_heading = ?, features_items = ?
                WHERE product_id = 1');
            $stmt->execute([
                $data['name'] ?? '', $data['bengali_name'] ?? '', $data['description'] ?? '', 
                $data['price'] ?? 0, $data['discount_price'] ?? '', $data['features'] ?? '',
                $accordion_title, $accordion_description, $features_heading, $features_items
            ]);
        } else {
            // Insert (extended fields)
            $stmt = $pdo->prepare('INSERT INTO product_details (
                product_id, name, bengali_name, description, price, discount_price, features,
                accordion_title, accordion_description, features_heading, features_items
            ) VALUES (1, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
            $stmt->execute([
                $data['name'] ?? '', $data['bengali_name'] ?? '', $data['description'] ?? '', 
                $data['price'] ?? 0, $data['discount_price'] ?? '', $data['features'] ?? '',
                $accordion_title, $accordion_description, $features_heading, $features_items
            ]);
        }
        
        echo json_encode(['success' => true, 'message' => 'Product details saved']);
    }
}

function handleReview($pdo, $method) {
    if ($method === 'POST') {
        $id = $_POST['id'] ?? null;
        $customer_name = $_POST['customer_name'] ?? '';
        $review_text = $_POST['review_text'] ?? '';
        $rating = $_POST['rating'] ?? 5;
        $display_order = $_POST['display_order'] ?? 0;
        $current_image = $_POST['current_image'] ?? '';
        
        $customer_image = $current_image;
        
        // Handle file upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = __DIR__ . '/../../assets/images/uploads/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $file_name = 'review_' . time() . '_' . uniqid() . '.' . $file_extension;
            $target_path = $upload_dir . $file_name;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $target_path)) {
                $customer_image = 'assets/images/uploads/' . $file_name;
                
                // Delete old image if updating
                if ($current_image && file_exists(__DIR__ . '/../../' . $current_image)) {
                    unlink(__DIR__ . '/../../' . $current_image);
                }
            }
        }
        
        if ($id) {
            // Update
            $stmt = $pdo->prepare('UPDATE reviews SET customer_image = ?, customer_name = ?, rating = ?, review_text = ?, display_order = ? WHERE id = ?');
            $stmt->execute([$customer_image, $customer_name, $rating, $review_text, $display_order, $id]);
        } else {
            // Insert
            $stmt = $pdo->prepare('INSERT INTO reviews (customer_image, customer_name, rating, review_text, display_order) VALUES (?, ?, ?, ?, ?)');
            $stmt->execute([$customer_image, $customer_name, $rating, $review_text, $display_order]);
        }
        
        echo json_encode(['success' => true, 'message' => 'Review saved']);
    } elseif ($method === 'DELETE') {
        $id = $_GET['id'] ?? 0;
        
        // Get image path before deleting
        $stmt = $pdo->prepare('SELECT customer_image FROM reviews WHERE id = ?');
        $stmt->execute([$id]);
        $review = $stmt->fetch();
        
        if ($review) {
            // Delete file
            $file_path = __DIR__ . '/../../' . $review['customer_image'];
            if (file_exists($file_path)) {
                unlink($file_path);
            }
            
            // Delete from database
            $stmt = $pdo->prepare('DELETE FROM reviews WHERE id = ?');
            $stmt->execute([$id]);
        }
        
        echo json_encode(['success' => true, 'message' => 'Review deleted']);
    }
}

function handleVideo($pdo, $method) {
    if ($method === 'POST') {
        $id = $_POST['id'] ?? null;
        $title = $_POST['title'] ?? '';
        $display_order = $_POST['display_order'] ?? 0;
        $current_video = $_POST['current_video'] ?? '';
        $current_thumbnail = $_POST['current_thumbnail'] ?? '';
        
        $video_path = $current_video;
        $thumbnail_path = $current_thumbnail;
        
        // Handle video upload
        if (isset($_FILES['video']) && $_FILES['video']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = __DIR__ . '/../../assets/videos/uploads/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            $file_extension = pathinfo($_FILES['video']['name'], PATHINFO_EXTENSION);
            $file_name = 'video_' . time() . '_' . uniqid() . '.' . $file_extension;
            $target_path = $upload_dir . $file_name;
            
            if (move_uploaded_file($_FILES['video']['tmp_name'], $target_path)) {
                $video_path = 'assets/videos/uploads/' . $file_name;
                
                // Delete old video if updating
                if ($current_video && file_exists(__DIR__ . '/../../' . $current_video)) {
                    unlink(__DIR__ . '/../../' . $current_video);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to upload video']);
                return;
            }
        } elseif (!$id) {
            // New video requires a file
            echo json_encode(['success' => false, 'message' => 'Video file is required']);
            return;
        }
        
        // Handle thumbnail upload
        if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = __DIR__ . '/../../assets/images/uploads/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            $file_extension = pathinfo($_FILES['thumbnail']['name'], PATHINFO_EXTENSION);
            $file_name = 'thumb_' . time() . '_' . uniqid() . '.' . $file_extension;
            $target_path = $upload_dir . $file_name;
            
            if (move_uploaded_file($_FILES['thumbnail']['tmp_name'], $target_path)) {
                $thumbnail_path = 'assets/images/uploads/' . $file_name;
                
                // Delete old thumbnail if updating
                if ($current_thumbnail && file_exists(__DIR__ . '/../../' . $current_thumbnail)) {
                    unlink(__DIR__ . '/../../' . $current_thumbnail);
                }
            }
        }
        
        if ($id) {
            // Update
            $stmt = $pdo->prepare('UPDATE videos SET title = ?, video_path = ?, thumbnail_path = ?, display_order = ? WHERE id = ?');
            $stmt->execute([$title, $video_path, $thumbnail_path, $display_order, $id]);
        } else {
            // Insert
            $stmt = $pdo->prepare('INSERT INTO videos (title, video_path, thumbnail_path, display_order) VALUES (?, ?, ?, ?)');
            $stmt->execute([$title, $video_path, $thumbnail_path, $display_order]);
        }
        
        echo json_encode(['success' => true, 'message' => 'Video saved']);
    } elseif ($method === 'DELETE') {
        $id = $_GET['id'] ?? 0;
        
        // Get paths before deleting
        $stmt = $pdo->prepare('SELECT video_path, thumbnail_path FROM videos WHERE id = ?');
        $stmt->execute([$id]);
        $video = $stmt->fetch();
        
        if ($video) {
            // Delete video file
            $video_file = __DIR__ . '/../../' . $video['video_path'];
            if (file_exists($video_file)) {
                unlink($video_file);
            }
            
            // Delete thumbnail file
            if ($video['thumbnail_path']) {
                $thumb_file = __DIR__ . '/../../' . $video['thumbnail_path'];
                if (file_exists($thumb_file)) {
                    unlink($thumb_file);
                }
            }
            
            // Delete from database
            $stmt = $pdo->prepare('DELETE FROM videos WHERE id = ?');
            $stmt->execute([$id]);
        }
        
        echo json_encode(['success' => true, 'message' => 'Video deleted']);
    }
}

function handleProductDetailsImage($pdo, $method) {
    if ($method !== 'POST') {
        echo json_encode(['success' => false, 'message' => 'Invalid method']);
        return;
    }

    // Ensure column exists
    try {
        $check = $pdo->prepare("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'product_details' AND COLUMN_NAME = 'order_image'");
        $check->execute();
        if (!$check->fetch()) {
            $pdo->exec("ALTER TABLE product_details ADD COLUMN `order_image` VARCHAR(255) DEFAULT NULL");
        }
    } catch (Throwable $e) {
        error_log('Migration error adding order_image: ' . $e->getMessage());
    }

    $order_image = '';
    if (isset($_FILES['order_image']) && $_FILES['order_image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = __DIR__ . '/../../assets/images/uploads/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

        $ext = pathinfo($_FILES['order_image']['name'], PATHINFO_EXTENSION);
        $file_name = 'order_' . time() . '_' . uniqid() . '.' . $ext;
        $target = $upload_dir . $file_name;
        if (move_uploaded_file($_FILES['order_image']['tmp_name'], $target)) {
            $order_image = 'assets/images/uploads/' . $file_name;
            // ensure row exists
            $stmt = $pdo->query('SELECT id FROM product_details WHERE product_id = 1');
            $exists = $stmt->fetch();
            if ($exists) {
                $stmt = $pdo->prepare('UPDATE product_details SET order_image = ? WHERE product_id = 1');
                $stmt->execute([$order_image]);
            } else {
                $stmt = $pdo->prepare('INSERT INTO product_details (product_id, order_image) VALUES (1, ?)');
                $stmt->execute([$order_image]);
            }

            echo json_encode(['success' => true, 'path' => $order_image]);
            return;
        }
    }

    echo json_encode(['success' => false, 'message' => 'No file uploaded']);
}

function handleSocial($pdo, $method) {
    if ($method === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        $id = $data['id'] ?? null;
        
        if ($id) {
            // Update
            $stmt = $pdo->prepare('UPDATE social_links SET platform = ?, url = ?, icon_path = ? WHERE id = ?');
            $stmt->execute([$data['platform'], $data['url'], $data['icon_path'] ?? '', $id]);
        } else {
            // Insert
            $stmt = $pdo->prepare('INSERT INTO social_links (platform, url, icon_path) VALUES (?, ?, ?)');
            $stmt->execute([$data['platform'], $data['url'], $data['icon_path'] ?? '']);
        }
        
        echo json_encode(['success' => true, 'message' => 'Social link saved']);
    } elseif ($method === 'DELETE') {
        $id = $_GET['id'] ?? 0;
        $stmt = $pdo->prepare('DELETE FROM social_links WHERE id = ?');
        $stmt->execute([$id]);
        echo json_encode(['success' => true, 'message' => 'Social link deleted']);
    }
}

function handleSettings($pdo, $method) {
    if ($method === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        
        foreach ($data as $key => $value) {
            // Check if setting exists
            $stmt = $pdo->prepare('SELECT id FROM site_settings WHERE setting_key = ?');
            $stmt->execute([$key]);
            $exists = $stmt->fetch();
            
            if ($exists) {
                // Update
                $stmt = $pdo->prepare('UPDATE site_settings SET setting_value = ? WHERE setting_key = ?');
                $stmt->execute([$value, $key]);
            } else {
                // Insert
                $stmt = $pdo->prepare('INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?)');
                $stmt->execute([$key, $value]);
            }
        }
        
        echo json_encode(['success' => true, 'message' => 'Settings saved']);
    }
}

function handleFeatureCard($pdo, $method) {
    // Ensure table exists
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS feature_cards (
            id INT AUTO_INCREMENT PRIMARY KEY,
            image_path VARCHAR(255) NOT NULL,
            title VARCHAR(500),
            display_order INT DEFAULT 0,
            is_active BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    } catch (Throwable $e) {
        error_log('Feature cards table creation error: ' . $e->getMessage());
    }

    if ($method === 'POST') {
        $id = $_POST['id'] ?? null;
        $title = $_POST['title'] ?? '';
        $display_order = $_POST['display_order'] ?? 0;
        $current_image = $_POST['current_image'] ?? '';
        
        $image_path = $current_image;
        
        // Handle file upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = __DIR__ . '/../../assets/images/uploads/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            $file_extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $file_name = 'feature_' . time() . '_' . uniqid() . '.' . $file_extension;
            $target_path = $upload_dir . $file_name;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $target_path)) {
                $image_path = 'assets/images/uploads/' . $file_name;
                
                // Delete old image if updating
                if ($current_image && file_exists(__DIR__ . '/../../' . $current_image)) {
                    unlink(__DIR__ . '/../../' . $current_image);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to upload image']);
                return;
            }
        } elseif (!$id) {
            // New feature card requires an image
            echo json_encode(['success' => false, 'message' => 'Image file is required']);
            return;
        }
        
        if ($id) {
            // Update
            $stmt = $pdo->prepare('UPDATE feature_cards SET image_path = ?, title = ?, display_order = ? WHERE id = ?');
            $stmt->execute([$image_path, $title, $display_order, $id]);
        } else {
            // Insert
            $stmt = $pdo->prepare('INSERT INTO feature_cards (image_path, title, display_order) VALUES (?, ?, ?)');
            $stmt->execute([$image_path, $title, $display_order]);
        }
        
        echo json_encode(['success' => true, 'message' => 'Feature card saved']);
    } elseif ($method === 'DELETE') {
        $id = $_GET['id'] ?? 0;
        
        // Get image path before deleting
        $stmt = $pdo->prepare('SELECT image_path FROM feature_cards WHERE id = ?');
        $stmt->execute([$id]);
        $card = $stmt->fetch();
        
        if ($card) {
            // Delete file
            $file_path = __DIR__ . '/../../' . $card['image_path'];
            if (file_exists($file_path)) {
                unlink($file_path);
            }
            
            // Delete from database
            $stmt = $pdo->prepare('DELETE FROM feature_cards WHERE id = ?');
            $stmt->execute([$id]);
        }
        
        echo json_encode(['success' => true, 'message' => 'Feature card deleted']);
    }
}
