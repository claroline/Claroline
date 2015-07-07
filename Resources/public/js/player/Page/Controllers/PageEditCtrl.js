(function () {
    'use strict';

    angular.module('Page').controller('PageEditCtrl', [
        'PageService',
        function (PageService) {
            
            this.currentPage = {};
            this.pages = {};
            
            this.sortableOptions = {
                placeholder: "placeholder",
                stop: function (e, ui) {
                   this.updateActivitiesOrder();
                }.bind(this),
                cancel: ".unsortable",
                items: "li:not(.unsortable)"
            };
            
            // Tiny MCE options
            this.tinymceOptions = {
                relative_urls: false,
                theme: 'modern',
                browser_spellcheck : true,
                autoresize_min_height: 100,
                autoresize_max_height: 500,
                plugins: [
                    'autoresize advlist autolink lists link image charmap print preview hr anchor pagebreak',
                    'searchreplace wordcount visualblocks visualchars fullscreen',
                    'insertdatetime media nonbreaking save table directionality',
                    'template paste textcolor emoticons code'
                ],
                toolbar1: 'undo redo | styleselect | bold italic underline | forecolor | alignleft aligncenter alignright | preview fullscreen',
                paste_preprocess: function (plugin, args) {
                    var link = $('<div>' + args.content + '</div>').text().trim(); //inside div because a bug of jquery
                    var url = link.match(/^(((ftp|https?):\/\/)[\-\w@:%_\+.~#?,&\/\/=]+)|((mailto:)?[_.\w-]+@([\w][\w\-]+\.)+[a-zA-Z]{2,3})$/);

                    if (url) {
                        args.content = '<a href="' + link + '">' + link + '</a>';
                        window.Claroline.Home.generatedContent(link, function (data) {
                            insertContent(data);
                        }, false);
                    }
                }
            };
            
            this.addPage = function(){
                
            };
            
            this.removePage = function(){
                
            };
            
            this.update = function(){
                
            };

            this.getNextPage = function () {            
                var index = this.pages.indexOf(this.currentPage);
                if (false !== index && this.pages[index + 1]) {
                    this.setCurrentPage(this.pages[index + 1]);
                } else {
                    this.setCurrentPage(this.pages[0]);
                }
            };
            
            this.getPreviousPage = function(){
                 var index = this.pages.indexOf(this.currentPage);
                if (false !== index && this.pages[index - 1]) {
                    this.setCurrentPage(this.pages[index - 1]);
                } else {
                    this.setCurrentPage(this.pages[this.pages.length - 1]);
                }
            };
            
            this.setPages = function(pages){
                this.pages = pages;
            };
            
            this.getPages = function(){
                return this.pages;
            }
            
            this.setCurrentPage = function(page){
                this.currentPage = page;
            };
            
            this.getCurrentPage = function (){
                return currentPage;
            };
        }
    ]);
})();