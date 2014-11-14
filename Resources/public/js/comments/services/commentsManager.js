'use strict';

commentsApp
    .factory("commentsManager", ["commentFactory", function(commentFactory){
        return {
            comments: [],
            init: function(portfolioId) {
                var comments = [
                    {
                        'id': 1,
                        'user': 'Naimish Sakhpara',
                        'date': '07/11/2014 12:10',
                        'message': 'Location H-2, Ayojan Nagar, Near Gate-3, Near<br /> Shreyas Crossing Dharnidhar Derasar,<br /> ' +
                                    'Paldi, Ahmedabad 380007, Ahmedabad,<br /> India<br /> Phone 091 37 669307<br /> Email aapamdavad.district@gmail.com'
                    },
                    {
                        'id': 2,
                        'user': 'Naimish Sakhpara',
                        'date': '08/11/2014 12:10',
                        'message': 'Arnab Goswami: "Some people close to Congress Party and close to the government had a #secret ' +
                                    '#meeting in a farmhouse in Maharashtra in which Anna Hazare send some representatives and they had a ' +
                                    'meeting in the discussed how to go about this all fast and how eventually this will end."'
                    },
                    {
                        'id': 3,
                        'user': 'Naimish Sakhpara',
                        'date': '09/11/2014 12:10',
                        'message': 'Arnab Goswami: "Some people close to Congress Party and close to the government had a #secret ' +
                                    '#meeting in a farmhouse in Maharashtra in which Anna Hazare send some representatives and they had a ' +
                                    'meeting in the discussed how to go about this all fast and how eventually this will end."'
                    },
                    {
                        'id': 4,
                        'user': 'Naimish Sakhpara',
                        'date': '10/11/2014 12:10',
                        'message': 'Arnab Goswami: "Some people close to Congress Party and close to the government had a #secret ' +
                                    '#meeting in a farmhouse in Maharashtra in which Anna Hazare send some representatives and they had a ' +
                                    'meeting in the discussed how to go about this all fast and how eventually this will end."'
                    }
                ];
                angular.forEach(comments, function(rawComment) {
                    var comment = commentFactory.getComment(portfolioId);
                    this.comments.push(new comment(rawComment));
                }, this);

                this.comments.$resolved = true;
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