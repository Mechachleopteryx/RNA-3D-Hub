    <div class="container motifs_compare_view">

      <div class="content">

        <div class="page-header">
          <h1><?php echo $title;?></h1>
        </div>

        <div class="row">
          <div class="span8">

            <ul class="tabs" data-tabs="tabs">
                <li class="active"><a href="#ils">Internal Loops</a></li>
                <li><a href="#hls">Hairpin Loops</a></li>
                <li><a href="#j3">3-way Junctions</a></li>
            </ul>

            <div class="tab-content">

                <div class="tab-pane active" id="ils">
                    <form method="post" action="<?=$action_il?>"  />
                    <?=$table['ils']?>
                    <br>
                    <input type='submit' class='btn primary' value="Compare selected">
                    </form>
                </div>

                <div class="tab-pane" id="hls">
                    <form method="post" action="<?=$action_hl?>"  />
                    <?=$table['hls']?>
                    <br>
                    <input type='submit' class='btn primary' value="Compare selected">
                    </form>
                </div>

                <div class="tab-pane" id="j3">
                    Coming soon.
                </div>

            </div>

          </div>

          <div class="span4 offset2">
            <h3>About</h3>
            <p>Etiam porta sem malesuada magna mollis euismod. Integer posuere erat a ante venenatis dapibus posuere velit aliquet. Aenean eu leo quam. Pellentesque ornare sem lacinia quam venenatis vestibulum. Duis mollis, est non commodo luctus, nisi erat porttitor ligula, eget lacinia odio sem nec elit.</p>
          </div>

        </div>

      </div>