    <div class="container motifs_view">

      <div class="content">
        <div class="page-header">
          <h1>
            <?php echo $title;?>
            <small><?=$status?></small>
          </h1>
          <a href='<?=$alt_view?>'>Switch to graph view</a>
        </div>
        <div class="row">
          <div class="span10">

            <h2></h2>
            <?php echo $counts; echo $table;?>

          </div>
          <div class="span4">
            <h3></h3>
          </div>
        </div>
      </div>

    <script>
        $(function () {
            $("#sort").tablesorter();
    		$(".fancybox").fancybox({
    		    openSpeed  : 'fast',
    		    closeSpeed : 'fast',
    		    arrows     : true
    		});
        })
    </script>
