<?= ((new SQLite3($_SERVER['DOCUMENT_ROOT'] . '/database.sqlite'))->querySingle("SELECT mode FROM users WHERE email = '{$_SESSION['email']}'") == 'dark' ? 'light' : 'dark') ?>