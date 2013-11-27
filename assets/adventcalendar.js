$(function(){
	// go home, you're drunk
	$('#goHomeYouAreDrunk').click(function() { $('html,body').animate({scrollTop: 0}, 'slow'); return false; });
	// external links
	$('a[rel="external"]').click(function() { window.open($(this).attr('href')); return false; });
	
	// activate tooltip
	$('.tip').tooltip();
});