<?php session_start(); ?>
<html>
<head><title>Hello app</title>
<meta charset="UTF-8">
</head>
<body>

<!-- The data encoding type, enctype, MUST be specified as below -->
<form enctype="multipart/form-data" action="result.php" method="POST">
    <!-- MAX_FILE_SIZE must precede the file input field -->
    <input type="hidden" name="MAX_FILE_SIZE" value="3000000" />
    <!-- Name of input element determines name in $_FILES array -->
Username: <input type="text" name="firstname"><br />
Send this file: <input name="userfile" type="file" accept="image/png,image/jpeg"/><br />
Email of user: <input type="email" name="useremail"><br />
Phone number (1-XXX-XXX-XXXX): <input type="phone" name="phone">
<input type="submit" value="Send File" />
</form>
</body>
</html>
