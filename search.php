<?php
require_once "includes/db_connect.php";
?>
<!DOCTYPE html>
<html>
<head>
    <title>Find Your PG | PG Oasis</title>
    <style>
        body {
            font-family: "Poppins", sans-serif;
            background: #f9f6f1;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 80%;
            margin: 40px auto;
            text-align: center;
        }
        input[type="text"] {
            padding: 10px;
            width: 60%;
            border-radius: 20px;
            border: 1px solid #ccc;
            font-size: 16px;
        }
        button {
            padding: 10px 20px;
            background-color: #c9a66b;
            color: white;
            border: none;
            border-radius: 20px;
            cursor: pointer;
        }
        .results {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            margin-top: 20px;
        }
        .pg-card {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin: 10px;
            width: 260px;
            text-align: left;
            overflow: hidden;
            transition: transform 0.2s ease;
        }
        .pg-card:hover {
            transform: translateY(-5px);
        }
        .pg-card img {
            width: 100%;
            height: 160px;
            object-fit: cover;
        }
        .pg-card .content {
            padding: 10px;
        }
        .pg-card h3 {
            margin: 0;
            color: #333;
        }
        .pg-card p {
            margin: 5px 0;
            color: #666;
            font-size: 14px;
        }
    </style>
</head>
<body>

<div class="container">
    <h1>Find Your Perfect PG 🏠</h1>
    <input type="text" id="searchBox" placeholder="Search by PG name, city, or area...">
    <div id="results" class="results"></div>
</div>

<script>
// when user types
document.getElementById('searchBox').addEventListener('keyup', function() {
    const query = this.value.trim();

    const xhr = new XMLHttpRequest();
    xhr.open('GET', 'search_action.php?q=' + encodeURIComponent(query), true);
    xhr.onload = function() {
        if (this.status === 200) {
            document.getElementById('results').innerHTML = this.responseText;
        }
    };
    xhr.send();
});
</script>

</body>
</html>
