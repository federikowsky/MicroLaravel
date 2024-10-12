<div class="container">
    <h2>Forgot Password</h2>
    <form action="/auth/password/forgot" method="post">
        <?= csrf_field() ?>
        <div class="form-group">
            <label for="email">Email Address:</label>
            <input type="email" id="email" name="email" required>
        </div>
        <button type="submit">Submit</button>
    </form>
</div>
