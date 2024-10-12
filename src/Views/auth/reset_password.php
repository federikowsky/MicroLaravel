<div class="container">
    <h2>Reset Password</h2>
    <form action="/auth/password/reset" method="POST">
        <?= csrf_field() ?>
        <?= ewt_field($inputs['ewt']) ?>
        <div class="form-group">
            <label for="password">New Password:</label>
            <input type="password" id="password" name="password" 
                class="<?= error_class($errors, 'password') ?>" required>
            <small><?= $errors['password'] ?? '' ?></small>
        </div>
        <div class="form-group">
            <label for="password2">Confirm Password:</label>
            <input type="password" id="password2" name="password2" 
                class="<?= error_class($errors, 'password2') ?>" required>
            <small><?= $errors['password2'] ?? '' ?></small>
        </div>
        <button type="submit">Reset Password</button>
    </form>
</div>
