/**
 * Template Factory
 */
var TemplateFactoryProto = function() {
    var templates = [];
    var currentTemplate = null;
    
    return {
        /**
         * 
         * @param template
         * @returns TemplateFactory
         */
        addTemplate: function(template) {
            templates.push(template);
            return this;
        },
        
        /**
         * 
         * @param template
         * @returns TemplateFactory
         */
        replaceTemplate: function(template) {
            var templateFound = false;
            for (var i = 0; i < templates.length; i++) {
                if (templates[i].id === template.id)
                {
                    templates[i] = template;
                    templateFound = true;
                    break;
                }
            }
            
            if (!templateFound) {
                this.addTemplate(template);
            }
            
            return this;
        },
        
        /**
         * 
         * @returns Array
         */
        getTemplates: function() {
            return templates;
        },
        
        /**
         * 
         * @param data
         * @returns TemplateFactory
         */
        setTemplates: function(data) {
            templates = data;
            return this;
        },
        
        /**
         * 
         * @returns object
         */
        getCurrentTemplate: function() {
            return currentTemplate;
        },
        
        /**
         * 
         * @param data
         * @returns Template Factory
         */
        setCurrentTemplate: function(data) {
            currentTemplate = data;
            return this;
        }
    };
};