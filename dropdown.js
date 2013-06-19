jQuery(document).ready(function($) {

	var form = $('#country-selector-form');
	
	form.before('<div id="country-selector" class="dropdown language-dropdown"><a href="#" class="dropdown-trigger">' + Drupal.t('Select your language') + '</a><div class="dropdown-content menu"></div></div>');

	form.detach();

	var dropdown = $('#country-selector');
	var content  = $('.dropdown-content', dropdown);
	var trigger  = $('.dropdown-trigger', dropdown);
	content.hide();
	
	trigger.click(function() {
		content.toggle();
		return false;
	});

	var options = $('#edit-country', form).children();

	// 3 columns
	j=0;
	// only one column ftw
	for (i=1; i<=3; i++) {
		var ul = $('<ul></ul>');
		for (;j<Math.ceil(options.length/3*i);j++) {
			var opt = $(options.get(j));
			var li = $('<li><a href="'+opt.attr('value')+'">' + opt.html() + '</a></li>');
			if (opt.attr('selected')) {
				li.addClass('active');
				trigger.html(opt.html());
			}
			$('a', li).click(function() {
				trigger.html($(this).html());
			});
			ul.append(li);
		}
		content.append(ul);
	}

	form.remove();
});
