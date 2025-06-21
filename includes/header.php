<?php
session_start();
require_once __DIR__ . '/../helpers/functions.php';

function is_active($page_name) {
    $current_page = basename($_SERVER['PHP_SELF']);
    
    $active_class = 'bg-orange-500 text-white shadow-lg shadow-orange-500/30';
    $inactive_class = 'text-gray-400 hover:bg-gray-700 hover:text-white';

    if (is_array($page_name)) {
        foreach ($page_name as $page) {
            if ($current_page == $page) {
                return $active_class;
            }
        }
        return $inactive_class;
    }
    
    return $current_page == $page_name ? $active_class : $inactive_class;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Toko Sepatu</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"/>
    
    <link rel="stylesheet" href="/tokosepatu/assets/css/style.css">

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f1f5f9; /* bg-slate-100 */
        }
        .swal2-popup {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>
<body class="bg-slate-100">
    <div class="flex h-screen">