(function () {
    'use strict';

    angular.module('Page').controller('PageEditCtrl', [
        'PageService',
        function (PageService) {

            this.pages = {};
            this.currentPageIndex = 0;

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
                browser_spellcheck: true,
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

            // Page constructor
            var my = this;
            var Page = function () {
                var ujm_page = {
                    description: '<h1>New page default description</h1>',
                    position: my.pages.length,
                    shuffle: false,
                    playerId: my.pages[0].playerId,
                    isLast: false,
                    isFirst: false
                };
                return ujm_page;
            };

            this.addPage = function () {
                // create a new page
                var page = new Page();
                // update last page position
                var last = this.pages[this.pages.length - 1];
                last.position = page.position + 1;
                // add new page at the right index in pages array
                this.pages.splice(this.pages.length - 1, 0, page);
            };

            this.removePage = function () {
                var current = this.pages[this.currentPageIndex];
                console.log(current);
                if (current && !current.isLast && !current.isFirst) {
                    var index = this.pages.indexOf(current);
                    // update positions...
                    for (var i = index; i < this.pages.length; i++) {
                        var p = this.pages[i];
                        p.position = p.position - 1;
                    }
                    // remove page
                    this.pages.splice(index, 1);
                }
            };

            this.update = function () {
                var promise = PageService.update(this.pages);
                promise.then(function (result) {
                    console.log('result');
                    console.log(result);
                }, function (error) {
                    console.log('error');
                    console.log(error);
                });

            };

            this.getNextPage = function () {
                var newIndex = this.currentPageIndex + 1;
                if (this.pages[newIndex]) {
                    this.currentPageIndex = newIndex;
                } else {
                    this.currentPageIndex = 0;
                }
            };

            this.getPreviousPage = function () {
                var newIndex = this.currentPageIndex - 1;
                if (this.pages[newIndex]) {
                    this.currentPageIndex = newIndex;
                } else {
                    this.currentPageIndex = this.pages.length - 1;
                }
            };


            this.setPages = function (pages) {
                this.pages = pages;
            };

            this.getPages = function () {
                return this.pages;
            };

            this.setCurrentPage = function (page) {
                var index = this.pages.indexOf(page);
                this.currentPageIndex = index;
            };
        }
    ]);
})();