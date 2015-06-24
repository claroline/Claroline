// Instructions aren't displayed (by default) for more visibility
$('#Instructions').css({"display" : "none"});
$('#showInstruct').css({"display" : "inline-block"});
$('#hideInstruct').css({"display" : "none"});

// If click, instructions are displayed
function DisplayInstruction() {
    $('#Instructions').css({"display" : "inline-block"});
    $('#showInstruct').css({"display" : "none"});
    $('#hideInstruct').css({"display" : "inline-block"});
}

// If click, instructions are hidden
function HideInstruction() {
    $('#Instructions').css({"display" : "none"});
    $('#hideInstruct').css({"display" : "none"});
    $('#showInstruct').css({"display" : "inline-block"});
}
