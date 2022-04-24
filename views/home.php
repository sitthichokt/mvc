<?php
include 'a.php';
?>

<?php startblock('header'); ?>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="">
        <meta name="author" content="">
        <link rel="shortcut icon" href="favicon.png">
        <title>Simple PHP MVC</title>
        <link href="public/assets/css/bootstrap.min.css" rel="stylesheet">
    </head>
<?php endblock(); ?>

<?php startblock('content'); ?>
         <section>
            <h1>Homepage</h1>
            <p>
                  <a href="<?php echo $this->data ?>">Check the first product</a>
            </p>
         <section>
<?php endblock(); ?>

<?php startblock('script'); ?>
    <script src="public/assets/jquery-3.6.0.min.js"></script>
    <script src="public/assets/js/bootstrap.min.js"></script>
<?php endblock(); ?>


