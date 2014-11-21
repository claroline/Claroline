'use strict';

portfolioApp
    .factory("commentsManager", ["commentFactory", function(commentFactory){
        return {
            comments: [],
            init: function(portfolioId, comments) {
                angular.forEach(comments, function(rawComment) {
                    var comment = commentFactory.getComment(portfolioId);
                    this.comments.push(new comment(rawComment));
                }, this);
            },
            create: function(portfolioId, rawComment) {
                var emptyComment = commentFactory.getComment(portfolioId);
                var comment      = new emptyComment(rawComment);

                this.comments.push(comment);
                this.save(comment);
            },
            save: function(comment) {
                var success = function() {
                };
                var failed = function(error) {
                    console.error('Error occured while saving comment');
                    console.log(error);
                }

                return comment.$save(success, failed);
            }
        };
    }]);