'use strict';

portfolioApp
    .factory("widgetsConfig", [function(){
        return {
            config: {
                "skills": {},
                "educations": {},
                "experiences": {},
                "title": {
                    "unique": true
                },
                "userInformation": {
                    "unique": true
                },
                "contacts": {
                    "unique": true
                },
                "interests": {},
                "presentation": {
                    "unique": true
                },
                "links": {
                    "unique": true
                }
            },
            getTypes: function() {
                return Object.keys(this.config);
            }
        };
    }]);