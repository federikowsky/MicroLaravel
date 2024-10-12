<?php

const MIN_DONATION = 1;

$errors = [];
$inputs = [];
$valid = false;

$request_method = strtoupper($_SERVER['REQUEST_METHOD']);

if ($request_method === 'POST') {
    // sanitize & validate amount
    $amount = filter_input(INPUT_POST, 'amount', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $inputs['amount'] = $amount;
    if ($amount !== false && $amount !== null) {
        $amount = filter_var($amount, FILTER_VALIDATE_FLOAT, ['options' => ['min_range' => MIN_DONATION]]);
        if ($amount === false) {
            $errors['amount'] = 'The minimum donation is $1';
        } else {
            $valid = true;
        }
    } else {
        $errors['amount'] = 'Please enter a donation amount';
    }
    // process the payment
    // ...

    // place variables to sessions
    $_SESSION['valid'] = $valid;
    $_SESSION['errors'] = $errors;
    $_SESSION['inputs'] = $inputs;

    // redirect to the page itself
    header("Location: {$_SERVER['REQUEST_URI']}", true, 303);
    exit;
} elseif ($request_method === 'GET') {
    if (isset($_SESSION['valid'])) {
        // get the valid state from the session
        $valid = $_SESSION['valid'];
        unset($_SESSION['valid']);
    }

    if (isset($_SESSION['errors'])) {
        $errors = $_SESSION['errors'];
        unset($_SESSION['errors']);
    }

    if (isset($_SESSION['inputs'])) {
        $errors = $_SESSION['inputs'];
        unset($_SESSION['inputs']);
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
    <title>PHP PRG</title>
</head>

<body>
    <main>
        <?php if ($valid) : ?>
            <div class="alert alert-success">
                Thank you for your donation of $<?= $inputs['amount'] ?? '' ?>
            </div>
        <?php endif ?>

        <form action="index.php" method="post">
            <h1>Donation</h1>
            <div>
                <label for="amount">Amount:</label>
                <input type="text" name="amount" value="<?= $inputs['amount'] ?? '' ?>" id="amount" placeholder="Minimum donation $<?= MIN_DONATION ?>">
                <small><?= $errors['amount'] ?? '' ?></small>
            </div>
            <button type="submit">Donate</button>
        </form>
        <form action="/auth/logout" method="post">
            <?= csrf_field() ?>
            <button type="submit">Logout</button>
        </form>
        <br>
        <a href="/auth/register">registrati qui</a>
        <br>
        <a href="/auth/login">login qui</a>
    </main>

</body>

</html>
