<?php
require_once __DIR__ . '/../back_end/auth/auth_check.php';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Communication Board</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f7fbff; }
        .category-card { border: 0; cursor: pointer; }
        .category-card.active { border: 3px solid #4A90D9; }
        .picture-card { min-height: 180px; border: 3px solid #4A90D9; border-radius: 16px; }
        .picture-symbol { font-size: 4rem; }
        .picture-card button { min-height: 56px; }
    </style>
</head>
<body>
    <nav class="navbar navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="tasks.php">Communication</a>
            <div class="navbar-nav">
                <a class="nav-link" href="tasks.php">Tasks</a>
                <a class="nav-link" href="progress.php">Progress</a>
                <a class="nav-link" href="child_view.php">Child View</a>
            </div>
        </div>
    </nav>
    <main class="container py-4">
        <h1 class="h2 mb-4">Picture Communication Board</h1>
        <div id="communicationAlert" role="alert"></div>
        <div class="card card-body mb-4">
            <form id="createCardForm" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label" for="cardCategory">Category</label>
                    <select class="form-select" id="cardCategory" name="category_id" required></select>
                </div>
                <div class="col-md-3">
                    <label class="form-label" for="cardLabel">Card label</label>
                    <input class="form-control" id="cardLabel" name="label" maxlength="100" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label" for="cardIcon">Picture or icon</label>
                    <input class="form-control" id="cardIcon" name="image_path" maxlength="255" placeholder="e.g. Apple or 🍎">
                </div>
                <div class="col-md-3">
                    <label class="form-label" for="cardText">Spoken text</label>
                    <input class="form-control" id="cardText" name="spoken_text" maxlength="255">
                </div>
                <div class="col-12">
                    <button class="btn btn-primary" type="submit">Add Picture Card</button>
                </div>
            </form>
        </div>
        <h2 class="h4">Categories</h2>
        <div id="categoryList" class="row g-3 mb-4"></div>
        <h2 class="h4">Picture Cards</h2>
        <div id="cardList" class="row g-3"></div>
    </main>
    <script src="javascript/communication.js"></script>
</body>
</html>
