'use strict';

commentsApp
    .factory("commentsManager", ["$http", "commentFactory", "urlInterpolator", function($http, commentFactory, urlInterpolator){
        return {
            comments: [],
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
            },
            loadComments: function(portfolioId) {
                this.comments.length = 0;
                this.comments.$resolved = false;

                if (0 < portfolioId) {
                    var url = urlInterpolator.interpolate('/{{portfolioId}}/comment', {portfolioId: portfolioId});
                    var $this = this;

                    $http.get(url)
                        .success(function(data) {
                            angular.forEach(data, function(rawComment) {
                                var comment = commentFactory.getComment(portfolioId);
                                this.comments.push(new comment(rawComment));
                            }, $this);

                            $this.comments.$resolved = true;
                        });
                }
                else {
                    this.comments.$resolved = true;
                }
            },
            addComment: function(portfolioId, message) {
                if (0 < portfolioId) {
                    this.create(portfolioId, {message: message})
                }
            }
        };
    }]);