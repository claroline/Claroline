function hasClass(ele,cls) {
    return ele.className.match(new RegExp('(\\s|^)'+cls+'(\\s|$)'));
}

function addClass(ele,cls) {
    if (!this.hasClass(ele,cls)) ele.className += " "+cls;
}

if(window.self !== window.top){
    addClass(document.querySelector("body"), "iframe");
    addClass(document.querySelector("html"), "iframe");
}
