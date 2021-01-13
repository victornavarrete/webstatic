<!doctype html>
<html class="no-js" lang="">

<head>
  
  <title> <?php echo CONFIG['name'] ?> / <?php echo $post['title'] ?> </title>
  <meta name="description" content="">
  <?php load_partial('head'); ?>  
</head>

<body>

  <!-- Add your site or application content here -->
  <h1><?php echo $post['title'] ?></h1>
  <div><?php echo $post['text'] ?></div> 

  <?php load_partial('footer'); ?> 
</body>

</html>
