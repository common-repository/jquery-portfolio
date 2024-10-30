	jQuery(document).ready(function(){

		var container = jQuery('#portfolio_container');
		// initialize isotope
		container.isotope({
			
		});

		// filter items when filter link is clicked
		jQuery('#filters a').click(function(){
		  var selector = jQuery(this).attr('data-filter');
		  container.isotope({ filter: selector });
		  return false;
		});


	});