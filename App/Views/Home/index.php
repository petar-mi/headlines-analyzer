<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Home</title>
</head>

<body>
    <h1>Welcome</h1>
    <p>Hello <?php echo htmlspecialchars($name); /* moramo da koristimo htmlspecialchars da ukoliko se radi o unosu korisnika ne bi bila izvrsena neka maliciozna skripta*/ ?></p>

    <ul>
        <?php foreach ($colours as $colour): ?>
            <li><?php echo htmlspecialchars($colour); ?></li>
        <?php endforeach; /*endforeach se koristi uglavnom kada se php koristi u skriptama jer nemamo {} zagrada za petlju vec : (dve tackice) u prvom redu, inace bi se u ovom redu nalazila samo jedna zatvorena zagrada } sto bi bilo necitljivo*/?>
    </ul>
</body>

</html>