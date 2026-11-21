
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account</title>

    <link rel="stylesheet" href="css/style.css">
</head>
<body>

    <div class="register-container">

        <h1>Create Account</h1>
        <p>Register to access the Visual Schedule System.</p>

        <?php if (isset($_GET["status"]) && $_GET["status"] === "success"): ?>
            <p class="success-message">
                Registration successful! You can now log in.
            </p>
        <?php endif; ?>

        <form action="../back_end/auth/register.php" method="POST">

            <label for="first_name">First Name</label>
            <input type="text" id="first_name" name="first_name"
                   maxlength="80" required>

            <label for="last_name">Last Name</label>
            <input type="text" id="last_name" name="last_name"
                   maxlength="80" required>

            <label for="username">Username</label>
            <input type="text" id="username" name="username"
                   minlength="3" maxlength="50" required>

            <label for="email">Email</label>
            <input type="email" id="email" name="email"
                   maxlength="150" required>

            <label for="age">Age (Optional)</label>
            <input type="number" id="age" name="age"
                   min="0" max="120">

            <label for="address">Address (Optional)</label>
            <input type="text" id="address" name="address"
                   maxlength="255">

            <label for="phone">Phone (Optional)</label>
            <input type="tel" id="phone" name="phone"
                   maxlength="20">

            <label for="role">Account Role</label>
            <select id="role" name="role" required>
                <option value="">Select your role</option>
                <option value="caregiver">Caregiver</option>
                <option value="therapist">Therapist</option>
                <option value="teacher">Teacher</option>
            </select>

            <label for="password">Password</label>
            <input type="password" id="password" name="password"
                   minlength="8" required>

            <label for="confirm_password">Confirm Password</label>
            <input type="password" id="confirm_password"
                   name="confirm_password" minlength="8" required>

            <button type="submit">Create Account</button>

        </form>

        <p>Already have an account?
            <a href="index.php">Log in here</a>
        </p>

    </div>

</body>
</html>
