<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Home</title>
</head>

<body>
    <h1>Welcome</h1>
    <p>Hello <?php echo htmlspecialchars($name); /* we have to use htmlspecialchars if it is user entry to prevent a malicous code script execution*/ ?></p>

    <ul>
        <?php foreach ($colours as $colour): ?>
            <li><?php echo htmlspecialchars($colour); ?></li>
        <?php endforeach; /* endforeach is used when php is used in scripts since we don't have {} but we use : (colon) in 1st line (otherwise we would only have one closed bracket } in this line making it hard to read */?>
    </ul>
</body>

</html>