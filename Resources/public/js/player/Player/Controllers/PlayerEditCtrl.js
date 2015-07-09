(function () {
    'use strict';

    angular.module('Player').controller('PlayerEditCtrl', [
        'PlayerService',
        function (PlayerService) {

            this.player = {};
            this.isCollapsed = false;

            this.endDateIsOpened = false;
            this.startDateIsOpened = false;

            this.openEndDate = function ($event) {
                $event.preventDefault();
                $event.stopPropagation();
                this.startDateIsOpened = true;
            };

            this.openStartDate = function ($event) {
                $event.preventDefault();
                $event.stopPropagation();
                this.endDateIsOpened = true;
            };

            // not working
            this.dateOptions = {
                showButtonBar: false,
                closeText: 'Close Me',
                showWeeks: false
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


            this.update = function () {        
                console.log(this.player);
                PlayerService.update(this.player);
            };

            this.setPlayer = function (player) {
                this.player = player;
            };

            this.getPlayer = function () {
                return this.player;
            };
        }
    ]);
})();