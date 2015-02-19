(function ($) {
	$(document).ready(function(){
		$('.wrap form').hide();
		$('#basic').show();
		$('.subsubsub a').click(function(e){
			$('.wrap').find('form').hide();
			$('#'+$(this).attr('class')).show();
		});
	});
})(jQuery);