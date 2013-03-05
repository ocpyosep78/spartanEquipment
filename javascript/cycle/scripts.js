$(document).ready(function(){

	$('#Menu li:last-child').addClass('last');

//	$('#cycle').cycle({
//		fx: 'fade'
//	});

	function textReplacement(input){
		var originalvalue = input.val();
		input.focus( function(){
			if( $.trim(input.val()) == originalvalue ){ input.val(''); }
		});
		input.blur( function(){
			if( $.trim(input.val()) == '' ){ input.val(originalvalue); }
		});
	}

	textReplacement($('#search_query'));
	textReplacement($('#nl_first_name'));
	textReplacement($('#nl_email'));

//	jCarouselLite example -- you will need to add a <div class="ProductListContainer"> around the <ul>
//	in the HomeFeaturedProducts Panel

//	$("#HomeFeaturedProducts .ProductListContainer").jCarouselLite({
//        btnNext: "#featured-next",
//        btnPrev: "#featured-prev",
//		visible: 5,
//		scroll: 5,
//		speed: 800
//    });
	
//	$('a.fancybox').fancybox();

});