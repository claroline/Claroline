'use strict';

/**
 * Clipboard Factory
 */
function ClipboardFactory($rootScope, PathFactory) {
    // Clipboard content
    var clipboard = null;
    
    // Current clipboard content comes from Templates ?
    var clipboardFromTemplates = false;
    
    // Enable paste buttons when clipboard is not empty
    $rootScope.pasteDisabled = true;
    
    return {
        /**
         * Empty clipboard
         * 
         * @returns ClipboardFactory
         */
        clear: function() {
            clipboard = null;
            clipboardFromTemplates = false;
            this.setPasteDisabled(true);
            
            return this;
        },
        
        /**
         * Copy selected steps into clipboard
         * 
         * @param steps
         * @param fromTemplates
         * @returns ClipboardFactory
         */
        copy: function(steps, fromTemplates) {
            clipboard = steps;
            clipboardFromTemplates = fromTemplates || false;
            this.setPasteDisabled(false);
            
            return this;
        },
        
        /**
         * Paste steps form clipboards into current Path tree
         * 
         * @param step
         * @returns ClipboardFactory
         */
        paste: function(step) {
            if (null !== clipboard) {
                // Clone voir : http://stackoverflow.com/questions/122102/most-efficient-way-to-clone-an-object
                var stepCopy = jQuery.extend(true, {}, clipboard);
                
                // Replace IDs before inject steps in path
                this.replaceStepsId(stepCopy);
                this.replaceResourcesId(stepCopy);

                if (!clipboardFromTemplates) {
                    stepCopy.name = stepCopy.name + '_copy';
                }
                
                step.children.push(stepCopy);

                PathFactory.recalculateStepsLevel();
            }
            
            return this;
        },
        
        /**
         * 
         * @param step
         * @returns ClipboardFactory
         */
        replaceResourcesId: function(step) {
            if (typeof step.resources !== 'undefined' && step.resources !== null && step.resources.length != 0) {
                for (var i = 0; i < step.resources.length; i++) {
                    step.resources[i].id = PathFactory.getNextResourceId();
                }
            }
            
            if (step.children.length != 0) {
                for (var j = 0; j < step.children.length; j++) {
                    this.replaceResourcesId(step.children[j]);
                }
            }
            
            return this;
        },
        
        /**
         * 
         * @param step
         * @returns ClipboardFactory
         */
        replaceStepsId: function(step) {
            step.id = PathFactory.getNextStepId();
            step.resourceId = null;

            if (step.children.length != 0) {
                for (var i = 0; i < step.children.length; i++) {
                    this.replaceStepsId(step.children[i]);
                }
            }
            return this;
        },
        
        /**
         * 
         * @returns boolean
         */
        getPasteDisabled: function() {
            return $rootScope.pasteDisabled;
        },
        
        /**
         * 
         * @param data
         * @returns ClipboardFactory
         */
        setPasteDisabled: function(data) {
            $rootScope.pasteDisabled = data;
            return this;
        }
    };
}