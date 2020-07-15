(function($){

	hljs.initHighlightingOnLoad();

	$(document).ready(function(){
		$('.col-right-wrapper').stick_in_parent({
			parent: '.layout-content'
		});
		$('body').scrollspy({
			target: '.table-of-contents'
		});
	});

})(jQuery);
