<?php
$conn = new mysqli("localhost", "root", "", "chababonsai");

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}