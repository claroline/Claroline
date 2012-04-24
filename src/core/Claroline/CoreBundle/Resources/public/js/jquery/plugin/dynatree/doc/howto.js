/*************************************************************************
	(c) 2008-2009 Martin Wendt
 *************************************************************************/

$(function(){
	// Replace tabs inside <pre> with 4 spaces, because some browsers display 8
	// characters
	$("pre.codesample, div.codesample pre, pre.prettyprint").each(function(){
		var text = $(this).text();
		text2 = text.replace(/\t/g, "    ");
		$(this).text(text2)
	});

	// Show some elements only, if (not) inside the Example Browser
	if (top.location == self.location)
		$(".hideOutsideFS").hide();
	else
		$(".hideInsideFS").hide();
});


(function($) {

$.widget("ui.toc", {
	init: function() {
		// The widget framework supplies this.element and this.options.
		this.options.event += '.toc'; // namespace event

		// create TOC
		var $this = this.element;
		var opts = this.options;

		// Attach the tree object to parent element
		var id = $this.attr("id");

		$this.addClass(opts.classnames.container);
		$this.append("<div class='" + opts.classnames.title + "'>" + opts.title + "</div>");

//		var $ul = $this.append("<ul />");
		var $ul = $("<ul />").appendTo($this);
//		this._addSubItems($ul, 1);
		var idx = 1;
		$("h2").each(function() {
			var $h = $(this);
			$ul.append("<li><a href='#" + idx + "'>" + $h.text() + "</a></li>");
			$h.attr("id", idx);
			idx++;
		});
	},

	// ------------------------------------------------------------------------
	lastentry: undefined
});


// The following methods return a value (thus breaking the jQuery call chain):

//$.ui.toc.getter = "getTree getRoot";


// Plugin default options:

$.ui.toc.defaults = {
	title: "Table of contents", // Text used for the toc header.
	startDepth: 1, // Start with <Hx> and higher (H1, H2, ...)
	maxDepth: 3, // Max depth to scan (..., ´H2, H3)
	addUpLink: false, // Add an clickable link to jump back upwards to the toc.
	numberItems: false, // Use an ordered list instead of <ul>. Also the index is prepended to the <h..> tags.
	orderedListStyleType: "decimal",
	strings: {
		loading: "Loading&#8230;",
		loadError: "Load error!"
	},
	classnames: {
		container: "ui-toc-container",
		title: "ui-toc-title"
	},
	debugLevel: 0,
	// templates
	//~ tabTemplate: '<li><a href="#{href}"><span>#{label}</span></a></li>', // 		var $li = $(o.tabTemplate.replace(/#\{href\}/g, url).replace(/#\{label\}/g, label));
	// ------------------------------------------------------------------------
	lastentry: undefined
};


// ---------------------------------------------------------------------------
})(jQuery);
