$(function(){
	// go home, you're drunk
	$('#goHomeYouAreDrunk').click(function() { $('html,body').animate({scrollTop: 0}, 'slow'); return false; });
	// external links
	$('a[rel="external"]').click(function() { window.open($(this).attr('href')); return false; });
	
	// activate tooltip
	$('.tip').tooltip();

	// keyboard navigation
	if ($('.pager').length > 0) {
		$('html').keydown(function (e) {
			if (e.keyCode == 37 && !$('.pager .previous').hasClass('disabled')) {
				document.location = $('.pager .previous a').attr('href');
			}
			if (e.keyCode == 39 && !$('.pager .next').hasClass('disabled')) {
				document.location = $('.pager .next a').attr('href');
			}
		});
	}
});