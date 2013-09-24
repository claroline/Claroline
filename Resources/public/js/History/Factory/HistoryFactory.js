/**
 * History Factory
 * 
 * @todo remove reference to $rootScope
 * @todo replace jQuery.extend function by native js function
 * @todo remove dependence to path factory
 */ 
var HistoryFactoryProto = [
    '$rootScope',
    'PathFactory', 
    function($rootScope, PathFactory) {
        // Is redo function is disabled ?
        $rootScope.redoDisabled = true;
        
        // Is undo function is disabled ?
        $rootScope.undoDisabled = true;
        
        // History stack
        var history = [];
        var historyState = -1;
        
        return {
            /**
             * Get current history stack
             * 
             * @returns Array
             */
            get: function() {
                return history;
            },
            
            /**
             * Restore default history state (= empty history)
             * 
             * @returns HistoryFactory
             */
            clear: function() {
                this.setRedoDisabled(true);
                this.setUndoDisabled(true);
                
                history = [];
                historyState = -1;
                
                return this;
            },
            
            /**
             * Store current path in history
             * 
             * @param path - The current path
             * @returns HistoryFactory
             */
            update: function(path) {
                // Increment history state
                this.incrementHistoryState();
                
                // Store path in history stack
                this.addPathToHistory(path);
                
                if (this.getHistoryState() !== 0) {
                    // History is not empty => enable the undo function
                    this.setUndoDisabled(false);
                }
                this.setRedoDisabled(true);
                
                return this;
            },
            
            /**
             * Get the last path state from history stack and set it as current path
             * 
             * @returns HistoryFactory
             */
            undo: function() {
                // Decrement history state
                this.decrementHistoryState();
                
                var path = this.getPathFromHistory(historyState);
                
                // Clone object
                var pathCopy = jQuery.extend(true, {}, path);
                
                this.setRedoDisabled(false);
                if (0 === historyState) {
                    // History stack is empty => disable the undo function
                    this.setUndoDisabled(true);
                }
                
                // Inject new path
                PathFactory.setPath(pathCopy);
                
                return this;
            },
            
            /**
             * Get the next history state from history stack and set it as current path
             * 
             * @returns HistoryFactory
             */
            redo: function() {
                // Increment history state
                this.incrementHistoryState();
                
                var path = this.getPathFromHistory(historyState);
                
                // Clone object
                var pathCopy = jQuery.extend(true, {}, path);
                
                this.setUndoDisabled(false);
                if (historyState == history.length - 1) {
                    this.setRedoDisabled(true);
                }
                
                // Inject new path
                PathFactory.setPath(pathCopy);
                
                return this;
            },
            
            /**
             * 
             * @returns HistoryFactory
             */
            incrementHistoryState: function() {
                // Increment history state
                this.setHistoryState(this.getHistoryState() + 1);
                return this;
            },
            
            /**
             * 
             * @returns HistoryFactory
             */
            decrementHistoryState: function() {
                // Decrement history state
                this.setHistoryState(this.getHistoryState() - 1);
                return this;
            },
            
            /**
             * 
             * @returns Integer
             */
            getHistoryState: function() {
                return historyState;
            },
            
            /**
             * 
             * @param data
             * @returns HistoryFactory
             */
            setHistoryState: function(data) {
                historyState = data;
                return this;
            },
            
            /**
             * Get path state stored at position index in history stack
             * 
             * @param index
             * @returns object
             */
            getPathFromHistory : function(index) {
                return history[index];
            },
            
            /**
             * Store path state in history stack
             * 
             * @param data
             * @returns HistoryFactory
             */
            addPathToHistory : function(data) {
                // Clone object
                var pathCopy = jQuery.extend(true, {}, data);
                history.push(pathCopy);
                
                return this;
            },
            
            /**
             * 
             * @returns boolean
             */
            getRedoDisabled: function() {
                return $rootScope.redoDisabled;
            },
            
            /**
             * 
             * @param data
             * @returns HistoryFactory
             */
            setRedoDisabled: function(data) {
                $rootScope.redoDisabled = data;
                return this;
            },
            
            /**
             * 
             * @returns boolean
             */
            getUndoDisabled: function() {
                return $rootScope.undoDisabled;
            },
            
            /**
             * 
             * @param data
             * @returns HistoryFactory
             */
            setUndoDisabled: function(data) {
                $rootScope.undoDisabled = data;
                return this;
            }
        };
    }
];