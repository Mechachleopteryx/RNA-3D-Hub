    <div class="topbar" data-dropdown="dropdown">
      <div class="fill">
        <div class="container">
          <a class="brand" href="<?php echo $baseurl;?>">RNA 3D Hub</a>
          <ul class="nav">
            <li><a href="<?php echo $baseurl;?>release/view/0.5">Motif Atlas</a></li>
            <li><a href="<?php echo $baseurl;?>pdb">RNA 3D Structures</a></li>

            <li class="dropdown">
                <a href="#" class="dropdown-toggle">Motif Atlas Releases</a>
                <ul class="dropdown-menu">
                    <li><a href="<?php echo $baseurl;?>release">View all</a></li>
                    <li><a href="<?php echo $baseurl;?>release/view/0.5">View current</a></li>
                    <li><a href="<?php echo $baseurl;?>release/compare">Compare</a></li>
                </ul>
            </li>

            <li class="dropdown">
                <a href="#" class="dropdown-toggle">Non-redundant Lists</a>
                <ul class="dropdown-menu">
                    <li><a href="<?php echo $baseurl;?>nrlist">Home</a></li>
                    <li><a href="<?php echo $baseurl;?>nrlist/release_history">Release history</a></li>
                    <li><a href="<?php echo $baseurl;?>nrlist/compare_releases">Compare releases</a></li>
                    <li class="divider"></li>
                    <li><a href="<?php echo $baseurl;?>nrlist/release/current">View current release</a></li>
                </ul>
            </li>

            <li><a href="#about">About</a></li>
            <li><a href="#contact">Contact</a></li>
          </ul>
        </div>
      </div>
    </div>