var PageslideProto = [
    '$http', 
    '$log', 
    '$parse', 
    '$rootScope', 
    function ($http, $log, $parse, $rootScope) {
        var defaults = {};
        var str_inspect_hint = 'Add testing="testing" to inspect this object';

        /* Return directive definition object */
        return {
            restrict: "A",
            replace: false,
            transclude: false,
            scope: {},
            link: function ($scope, el, attrs) {
                /* parameters */
                var param = {};
                param.side = attrs.pageslide || 'right';
                param.speed = attrs.psSpeed || '0.5';

                /* init */
                var css_class = 'ng-pageslide ps-hidden';
                css_class += ' ps-' + param.side;

                /* DOM manipulation */
                var content = document.getElementById(attrs.href.substr(1));
                var slider = document.createElement('div');
                var body = document.getElementsByTagName('body')[0];
                slider.id = "ng-pageslide";
                slider.className = css_class;

                document.body.appendChild(slider);
                slider.appendChild(content);

                /* set CSS from parameters */
                if (param.speed){
                    slider.style.transitionDuration = param.speed + 's';
                    slider.style.webkitTransitionDuration = param.speed + 's';
                    body.style.transitionDuration = param.speed + 's';
                    body.style.webkitTransitionDuration = param.speed + 's';
                }

                /*
                * Events
                * */
                el[0].onclick = function(e){
                    e.preventDefault();
                    if (/ps-hidden/.exec(slider.className)){
                        if (slider.className.indexOf('ps-left') != -1) {
                            body.className += ' ps-push-left';
                        }

                        if (slider.className.indexOf('ps-right') != -1) {
                            body.className += ' ps-push-right';
                        }
                        content.style.display = 'none';
                        slider.className = slider.className.replace(' ps-hidden','');
                        slider.className += ' ps-shown';
                        setTimeout(function(){
                            content.style.display = 'block';
                        },(param.speed * 1000));
                    }
                };

                var close_handler = document.getElementById(attrs.href.substr(1) + '-close');

                if (close_handler){
                    close_handler.onclick = function(e){
                        e.preventDefault();
                        if (/ps-shown/.exec(slider.className)){
                            if (slider.className.indexOf('ps-left') != -1) {
                                body.className = body.className.replace(' ps-push-left','');
                            }

                            if (slider.className.indexOf('ps-right') != -1) {
                                body.className = body.className.replace(' ps-push-right','');
                            }
                            content.style.display = 'none';
                            slider.className = slider.className.replace(' ps-shown','');
                            slider.className += ' ps-hidden';
                        }
                    };
                }
            }
        };
    }
];