
<?php
    class Evo_Review 
    {
        var $imgURL;
        var $reviewerName;
        var $reviewText;
        var $animDelay;

        function __construct($img, $name, $text, $delay){
        $this->imgURL = $img;
        $this->reviewerName = $name;
        $this->reviewText = $text;
        $this->animDelay = $delay;
        }

        function display(){
        echo '<div class="card col-md-4" aos-duration="', $this->animDelay, '" data-aos="flip-up">';
        echo '<div class="card-body" style="padding: 15px;">';
        echo '<img style="width:90px; border-radius: 10px; height: 80px; display: inline;"  src="', $this->imgURL, '" alt="">';
        echo '<h5 style="color: black; display: inline; padding: 10px;" class="card-title">', $this->reviewerName, '</h5>';
        echo '<p style="color: black;" class="card-subtitle mb-2 text-muted">';
        echo '<i class="fas fa-star"></i>';
        echo '<i class="fas fa-star"></i>';
        echo '<i class="fas fa-star"></i>';
        echo '<i class="fas fa-star"></i>';
        echo '<i class="fas fa-star"></i></p>';
        echo '<p class="card-text" style="height: 200px;">', $this->reviewText, '</p>';
        echo '</div></div>';
        }
    }
?>