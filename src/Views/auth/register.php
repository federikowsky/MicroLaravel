<form action="/auth/register" method="post">
    <h1>Sign Up</h1>
    <?= csrf_field() ?>
    <div>
        <label for="username">Username:</label>
        <input type="text" name="username" id="username" value="<?= $inputs['username'] ?? '' ?>"
               class="<?= error_class($errors, 'username') ?>">
        <small><?= $errors['username'] ?? '' ?></small>
    </div>

    <div>
        <label for="email">Email:</label>
        <input type="email" name="email" id="email" value="<?= $inputs['email'] ?? '' ?>"
               class="<?= error_class($errors, 'email') ?>">
        <small><?= $errors['email'] ?? '' ?></small>
    </div>

    <div>
        <label for="password">Password:</label>
        <input type="password" name="password" id="password"
               class="<?= error_class($errors, 'password') ?>">
        <small><?= $errors['password'] ?? '' ?></small>
    </div>

    <div>
        <label for="password2">Password Again:</label>
        <input type="password" name="password2" id="password2"
               class="<?= error_class($errors, 'password2') ?>">
        <small><?= $errors['password2'] ?? '' ?></small>
    </div>

    <div>
        <label for="agree">
            <input type="checkbox" name="agree" id="agree" value="checked" <?= $inputs['agree'] ?? '' ?> /> I
            agree
            with the
            <a href="#" title="term of services">term of services</a>
        </label>
        <small><?= $errors['agree'] ?? '' ?></small>
    </div>

    <button type="submit">Register</button>

    <footer>Already a member? <a href="/auth/login">Login here</a></footer>

</form>
