<?php
/**
 * About section for the homepage.
 *
 * @package Hestia
 * @since Hestia 1.0
 */

if ( ! function_exists( 'hestia_about' ) ) :
	/**
	 * About section content.
	 *
	 * @since Hestia 1.0
	 * @modified 1.1.51
	 */
	function hestia_about() {

		/**
		 * Don't show section if Disable section is checked
		 */
		$section_style = '';
		$hide_section  = get_theme_mod( 'hestia_about_hide', false );
		if ( (bool) $hide_section === true ) {
			if ( is_customize_preview() ) {
				$section_style .= 'display: none;';
			} else {
				return;
			}
		}

		/**
		 * Display overlay (section-image class) on about section only if section have a background
		 */
		$class_to_add              = '';
		$hestia_frontpage_featured = get_theme_mod( 'hestia_feature_thumbnail', get_template_directory_uri() . '/assets/img/contact.jpg' );
		if ( ! empty( $hestia_frontpage_featured ) ) {
			$class_to_add   = 'section-image';
			$section_style .= 'background-image: url(\'' . esc_url( $hestia_frontpage_featured ) . '\');';
		}
		$section_style = 'style="' . $section_style . '"';

		hestia_before_about_section_trigger(); ?>

		<a name="AboutUs"></a>

		<section class="hestia-about <?php echo esc_attr( $class_to_add ); ?>" id="about" data-sorder="hestia_about" <?php echo wp_kses_post( $section_style ); ?>>
			<?php hestia_display_customizer_shortcut( 'hestia_about_hide', true ); ?>
			<div class="container">
				<div class="row hestia-about-content">
					<?php
					// Show the selected frontpage content
					if ( have_posts() ) {
						while ( have_posts() ) {
							the_post();
							get_template_part( 'template-parts/content', 'frontpage' );
						}
					} else { // I'm not sure it's possible to have no posts when this page is shown, but WTH
						get_template_part( 'template-parts/content', 'none' );
					}
					?>
				</div>
				<div class="aboutSecBackground blackText">
					<div class="flexOuter">
						<img class="boxShadow" aos-duration="200" data-aos="flip-up" src="http://localhost:81\evoDev\wp-content\themes\hestia\assets\img\evoLogoAboutSection.png" alt="">
						<div class="flexCol">
							<h5 style="color: black;" class="blackText" aos-duration="200" data-aos="flip-up">Welcome to Evo Salon and Spa</h5>
							<p aos-duration="200" data-aos="flip-up" style="width: 500px;">
								Our mission is to provide a warm, welcoming and comfortable environment to every guest that walks through our door. 
								At Evo Salon and Spa we are commited to offering a large menu of quality Salon and Spa services by our friendly professionals.  
								You will leave here looking and feeling your very best.
							</p>
						</div>
					</div>
					<div class="flexCol"> 
						<h5 style="color: black; padding-top: 20px;" class="blackText" aos-duration="200" data-aos="flip-up">See what they're saying about us:</h5>
						<div class="flexOuter">
							<div class="container row">
							<?php
								$review1 = new Evo_Review("http://localhost:81/evoDev/wp-content/themes/hestia/assets/img/reviewPics/pic1.jpg", 
								"Emma G", "The lash services here are great! I used to go somewhere else to get me lashes done and they would fall
								 out right away, Jonelle does such an amazing job and my lashes look great for weeks! Evo is so nice and clean, 
								and in such a good spot!",
								100);
								$review1->display();

								$review2 = new Evo_Review("http://localhost:81/evoDev/wp-content/themes/hestia/assets/img/reviewPics/pic2.jpg", 
								"Leandra M", "Evo was great! This was my first time here. The owner was present and very friendly during my 
								appointment, clearly taking pride and joy in their businesses. The salon was clean, fully stocked and the 
								receptionist was helpful and attentive!",
								200);
								$review2->display();

								$review3 = new Evo_Review("http://localhost:81/evoDev/wp-content/themes/hestia/assets/img/reviewPics/pic3.jpg", 
								"Jessika T", "I've been a customer at Evo for a few years. I absolutely love this place! Danae is my stylist. She is 
								always really professional, friendly, and she is a really great stylist! Definitely go to Evo. 
								Excellent cuts and amazing colors!!!",
								300);
								$review3->display();

								$review4 = new Evo_Review("http://localhost:81/evoDev/wp-content/themes/hestia/assets/img/reviewPics/pic4.jpg", 
								"Patti A", "Evo is in a great location, very nicely decorated. Coffee/hospitality table right when you walk in.
								Everyone is very friendly. A lot to offer @ reasonable prices. Would recommend to all!",
								400);
								$review4->display();

								$review5 = new Evo_Review("http://localhost:81/evoDev/wp-content/themes/hestia/assets/img/reviewPics/pic5.jpg", 
								"Kelly G", "I am a extremely hard person to please when it comes to my hair. I have been all over the capital 
								district looking for that one stylist. I ended up finding her at Evo! I have been 
								so happy with my hair. Thank god I found Evo!!!!",
								500);
								$review5->display();

								$review6 = new Evo_Review("http://localhost:81/evoDev/wp-content/themes/hestia/assets/img/reviewPics/pic6.jpg", 
								"Lilly A", "I have been seeing Christina for my eyelashes for a few months now and I am obsessed! She does such 
								a good job, she's always accommodating to my schedule and she always takes me at the time of my appointment. 
								I would recommend everyone to see her.",
								600);
								$review6->display();
							?>

							<!-- <div class="card" style="width: 300px;">
								<div class="card-body" style="padding: 15px;">
									<img style="width:90px; height: 80px; display: inline;"  src="http://localhost:81\evoDev\wp-content\themes\hestia\assets\img\reviewPics\pic1.jpg" width="20px" height="20px" alt="">
									<h5 style="color: black; display: inline;" class="card-title">Emma G</h5>
									<p style="color: black;" class="card-subtitle mb-2 text-muted">
										<i class="fas fa-star"></i>
										<i class="fas fa-star"></i>
										<i class="fas fa-star"></i>
										<i class="fas fa-star"></i>
										<i class="fas fa-star"></i>
									</p>
									<p class="card-text">
										The lash services here are great! I used to go somewhere else to get me lashes done and they would fall out right away, 
										Jonelle does such an amazing job and my lashes look great for weeks! Evo is so nice and clean, and in such a good spot!
									</p>
								</div>
							</div> -->
						</div>

						</div>
					</div>
				</div>


			</div>
		</section>
		<?php
		hestia_after_about_section_trigger();
	}

endif;

if ( function_exists( 'hestia_about' ) ) {
	$section_priority = apply_filters( 'hestia_section_priority', 15, 'hestia_about' );
	add_action( 'hestia_sections', 'hestia_about', absint( $section_priority ) );
}
