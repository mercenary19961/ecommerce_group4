<?php
class User {
    private $conn;
    private $errors = [];

    public function __construct($db) {
        $this->conn = $db;
    }

    public function validateInput($data) {
        return htmlspecialchars(stripslashes(trim($data)));
    }

    public function validateRegistrationData($email, $username, $phone, $address, $password, $confirmPassword) {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->errors[] = "Invalid email format.";
        }

        // Validate username
        if (strlen($username) == 0) {
            $this->errors[] = "Username is required.";
        } elseif (!preg_match('/^[a-zA-Z\s]+$/', $username)) {
            $this->errors[] = "Username can only contain letters and spaces.";
        } else {
            // Split the username by spaces
            $words = explode(' ', $username);
            // Filter out words that are less than 2 characters long
            $words = array_filter($words, function($word) {
                return strlen($word) >= 2;
            });
            // Check if we have at least 2 valid words
            if (count($words) < 2) {
                $this->errors[] = "Username must be at least 2 words, each with at least 2 letters.";
            }
        }

        // Validate phone number
        if (!preg_match("/^07\d{8}$/", $phone)) {
            $this->errors[] = "Invalid phone number. It must start with '07' and be exactly 10 digits.";
        }

        if (strlen($address) == 0) {
            $this->errors[] = "Address is required.";
        }

        if (!preg_match("/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/", $password)) {
            $this->errors[] = "Password must be 8+ chars with 1 uppercase, 1 lowercase, 1 digit, and 1 special character.";
        }

        if ($password !== $confirmPassword) {
            $this->errors[] = "Passwords do not match.";
        }

        return empty($this->errors);
    }

    public function getErrors() {
        return $this->errors;
    }

    public function register($email, $username, $phone, $address, $password) {
        if ($this->validateRegistrationData($email, $username, $phone, $address, $password, $password)) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT); // Securely hash the password

            // Prepare SQL statement to prevent SQL injection
            $stmt = $this->conn->prepare("INSERT INTO users (username, email, address, password, phone, role_id) VALUES (?, ?, ?, ?, ?, 2)");
            $stmt->bind_param("ssssi", $username, $email, $address, $hashed_password, $phone);

            if ($stmt->execute()) {
                return true;
            } else {
                $this->errors[] = "Error: " . $stmt->error;
                return false;
            }

            $stmt->close();
        } else {
            return false;
        }
    }
}
?>
