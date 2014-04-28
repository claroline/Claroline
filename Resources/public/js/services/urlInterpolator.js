'use strict';

portfolioApp
    .service("urlInterpolator", ["$interpolate", function($interpolate){
        return {
            interpolate: function(text, parameters) {
                var interpolatedUrl = $interpolate(Routing.generate("icap_portfolio_internal_portfolio")  + text);
                return interpolatedUrl(parameters);
            }};
    }]);