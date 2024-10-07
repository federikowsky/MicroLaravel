<?php if (isset($errors['login'])) : ?>
    <div class="alert alert-error">
        <?= $errors['login'] ?>
    </div>
<?php endif ?>

    <form action="/auth/login" method="post">
        <h1>Login</h1>
        <?= csrf_field() ?>
        <div>
            <label for="username">Username:</label>
            <input type="text" name="username" id="username" value="<?= $inputs['username'] ?? '' ?>">
            <small><?= $errors['username'] ?? '' ?></small>
        </div>

        <div>
            <label for="password">Password:</label>
            <input type="password" name="password" id="password">
            <small><?= $errors['password'] ?? '' ?></small>
        </div>

        <div>
            <label for="remember_me">
                <input type="checkbox" name="remember_me" id="remember_me"
                    value="checked" <?= $inputs['remember_me'] ?? '' ?> />
                Remember Me
            </label>
            <small><?= $errors['agree'] ?? '' ?></small>
        </div>

        <section>
            <button type="submit">Login</button>
            <a href="/auth/register">Register</a>
        </section>

    </form>
