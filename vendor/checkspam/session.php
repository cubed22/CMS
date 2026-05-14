<?php

if( isset($_SESSION) && (!empty($_SESSION['error']) || !empty($_SESSION['success']) ) ) {
    if ( !empty($_SESSION['error']) ) {
      $messageD = $_SESSION['error'];
      ?>
        <script>
          /*$( document ).ready( function () 
          {
            
                $([document.documentElement, document.body]).animate({
                    scrollTop: $(".formPack").offset().top - 200
                }, 1000);
            });*/
        
        </script>
      <?php
    } 
    if ( !empty($_SESSION['success'])) {
      $messageD = $_SESSION['success'];
    }
    ?>
      <script>
        $( document ).ready( function () 
        {
            UIkit.modal.dialog("<div class='uk-padding modal-body modal-padding-bottom'><h2><?php echo $messageD; ?></h2><button class='button uk-modal-close uk-float-right' type='button'>Rozumím</button></div>");
        });
      </script>
    <?php
    /* po zobrazení smažeme session */
    session_destroy();
}