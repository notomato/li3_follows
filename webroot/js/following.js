function follow_links() {
	$('body').on('click', '.follow', function(e) {
		e.preventDefault();
		var link = $(e.target).closest('.follow');
		var icon = link.find('i');
		$.ajax({
			url: "/follows/follow",
			dataType: 'html',
			data: link.customdata(),
			type: 'POST',
			success: function(response){
				console.log(response);
				if (response == 'true') {
					if (link.hasClass('btn-success') || link.hasClass('btn-danger')) {
						var text = link.attr('data-follow_text');
						link.removeClass('btn-success')
						    .removeClass('btn-danger')
							.attr('title', text)
							.find('i').removeClass('icon-white');
					}
					else {
						var text = link.attr('data-unfollow_text');
						link.addClass('btn-success')
						    .attr('title',text)
							.find('i').addClass('icon-white');
					}
				} else if (response == 'false') {
					//$(e.target).addClass('error');
				}
			},
			error: function(response) {
				// trace(response);
			}
		});
	});
}

function follow_hover() {
	$("body").on({
		mouseenter: function(){
			if ($(this).hasClass('btn-success')) {
				$(this).removeClass('btn-success').addClass('btn-danger');
			}
		},
		mouseleave: function(){
			if ($(this).hasClass('btn-danger')) {
				$(this).removeClass('btn-danger').addClass('btn-success');
			}
		}
	}, '.follow');
}

$(document).ready(function() {
	follow_links();
	follow_hover();
});	