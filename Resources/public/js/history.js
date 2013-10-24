$(document).ready(function() {
	updateDiffRadios();
	$('#wiki-section-revision-history-list > li').find('input[name="diff"], input[name="oldid"]').click(updateDiffRadios);
});

function updateDiffRadios() {
	var $lis = $('#wiki-section-revision-history-list > li')
    var diffLi = false,
        oldLi = false;
    if (!$lis.length) {
        return true;
    }
    $lis.removeClass('selected-version').each(function () {
        var $li = $(this),
            $inputs = $li.find('input[type="radio"]'),
            $oldidRadio = $inputs.filter('[name="oldid"]').eq(0),
            $diffRadio = $inputs.filter('[name="diff"]').eq(0);
        if (!$oldidRadio.length || !$diffRadio.length) {
            return true;
        }
        if ($oldidRadio.prop('checked')) {
            oldLi = true;
            $li.addClass('selected-version');
            $oldidRadio.css('visibility', 'visible');
            $diffRadio.css('visibility', 'hidden');
        } else if ($diffRadio.prop('checked')) {
            diffLi = true;
            $li.addClass('selected-version');
            $oldidRadio.css('visibility', 'hidden');
            $diffRadio.css('visibility', 'visible');
        } else {
            if (diffLi && oldLi) {
                $oldidRadio.css('visibility', 'visible');
                $diffRadio.css('visibility', 'hidden');
            } else if (diffLi) {
                $diffRadio.css('visibility', 'visible');
                $oldidRadio.css(
                    'visibility', 'visible');
            } else {
                $diffRadio.css('visibility', 'visible');
                $oldidRadio.css('visibility', 'hidden');
            }
        }
    });
    return true;
}