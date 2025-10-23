<?php
include 'config.php';

class Product {
    public $id;
    public $name;
    public $price;
    public $image;
    public $description;
    public $category;

    public function __construct($id, $name, $price, $image, $description, $category) {
        $this->id = $id;
        $this->name = $name;
        $this->price = $price;
        $this->image = $image;
        $this->description = $description;
        $this->category = $category;
    }

    // Lấy tất cả sản phẩm
    public static function getAllProducts($conn) {
        $products = [];
        $sql = "SELECT f.id, f.name, f.price, f.image, f.description, c.name AS category 
                FROM foods f
                JOIN categories c ON f.category_id = c.id
                ORDER BY f.created_at DESC";
        $result = $conn->query($sql);

        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $products[] = new Product(
                    $row['id'],
                    $row['name'],
                    $row['price'],
                    $row['image'],
                    $row['description'],
                    $row['category']
                );
            }
        }
        return $products;
    }

    // Lấy sản phẩm theo danh mục
    public static function getProductsByCategory($conn, $category_id) {
        $products = [];
        $sql = "SELECT f.id, f.name, f.price, f.image, f.description, c.name AS category 
                FROM foods f
                JOIN categories c ON f.category_id = c.id
                WHERE f.category_id = ?
                ORDER BY f.created_at DESC";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $category_id);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $products[] = new Product(
                $row['id'],
                $row['name'],
                $row['price'],
                $row['image'],
                $row['description'],
                $row['category']
            );
        }
        return $products;
    }
}
?>
