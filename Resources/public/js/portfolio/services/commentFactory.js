'use strict';

portfolioApp
    .factory("commentFactory", ["$resource", function($resource) {
        return {
            baseUrl: Routing.generate("icap_portfolio_internal_portfolio"),
            portfolioId: null,
            getComment: function(portfolioId) {
                this.portfolioId = portfolioId;

                var comment = $resource(
                    this.baseUrl + "/:portfolioId/comment/:id",
                    {
                        portfolioId: portfolioId,
                        id: "@id"
                    },
                    {
                        get:    { method: "GET" },
                        create: { method: "GET" },
                        save:   { method: "POST" },
                        remove: { method: "DELETE"}
                    }
                );
                comment.prototype.message = '';

                return comment;
            }
        }
    }]);