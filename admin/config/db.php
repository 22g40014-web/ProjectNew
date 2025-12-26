<?php
$conn = new mysqli("localhost", "root", "", "chaba_bonsai");

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}