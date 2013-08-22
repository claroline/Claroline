(function () {
    'use strict';
    $(document).on('ready', function () {
        if( !(FileAPI.support.cors || FileAPI.support.flash) ){
            alert('Flash is needed');
        } else {
            var dropzone_enabled = false;
            var drop_enabled = false;

            if( FileAPI.support.dnd ){
                    $(document).dnd(function (over, evt){
                    drop_enabled = ($(evt.target).hasClass('nodes') || $(evt.target).parents('.nodes').length > 0);
                    if (over) {
                        if (!dropzone_enabled) {
                            $('.nodes').addClass('dropzone');
                            $('.nodes').after('<div class="dropzone-text"></div>');
                            dropzone_enabled = true;
                        }
                    } else {
                        if (dropzone_enabled) {
                            $('.nodes').removeClass('dropzone');
                            $('.dropzone-text').remove();
                            dropzone_enabled = false;
                        }
                    }

                    if (drop_enabled) {
                        $('.dropzone-text').html(Translator.get('platform:drop_file'));
                    }else {
                        $('.dropzone-text').html(Translator.get('platform:drag_file_here'));
                    }
                }, function (files){
                    if (drop_enabled) {
                        onFiles(files);
                    }else {
                    }
                });
            }
        }
    });

    var FU = {
        files: [],
        index: 0,
        active: false,

        add: function (file){
            FU.files.push(file);
        },

        getFileById: function (id){
            var i = FU.files.length;
            while( i-- ){
                if( FileAPI.uid(FU.files[i]) === id ){
                    return  FU.files[i];
                }
            }
        },

        start: function (){
            if( !FU.active && (FU.active = FU.files.length > FU.index) ){
                FU._upload(FU.files[FU.index]);
            }
        },

        abort: function (id){
            var file = this.getFileById(id);
            if( file.xhr ){
                file.xhr.abort();
            }
        },

        _upload: function (file){
            if( file ){
                var currentDirectoryId = Claroline.ResourceManager.Controller.views.main.currentDirectory.id;

                file.xhr = FileAPI.upload({
                    url: Routing.generate('claro_file_upload_with_ajax', {'parent': currentDirectoryId}),
                    imageAutoOrientation: true,
                    data: { fileName: file.name },
                    files: { file: file},
                    upload: function (){
                        if (FU.index === 0) {
                            $('.resources').after(
                                '<div class="resources-progress-bar"><div>' + Translator.get('platform:upload') + '<div class="progress progress-striped active"><div class="bar" style="width: 0%;"></div></div><div id="progress-files">1/' + FU.files.length + '</div></div></div>'
                            );
                        }
                    },
                    progress: function (evt){
                        var progress = ((evt.loaded/evt.total*(100/FU.files.length))+((100/FU.files.length)*FU.index))+'%';
                        console.log('progress: ' + progress);
                        $('div.progress > div.bar').css('width', progress);
                    },
                    complete: function (err, xhr){
                        var state = err ? 'error' : 'done';
                        FU.index++;
                        FU.active = false;
                        $('#progress-files').html(FU.index+'/'+FU.files.length);
                        var progress = (100/FU.files.length)*FU.index;
                        console.log('progress: '+progress);
                        $('div.progress > div.bar').css('width', progress+'%');
                        FU.start();

                        if (xhr.status === 200) {
                            var jsonResponse = $.parseJSON(xhr.response);
                            Claroline.ResourceManager.Controller.views.main.subViews.nodes.addThumbnails(jsonResponse);
                        } else {
                            if (xhr.status === 403) {
                                showErrorMessage(Translator.get('platform:upload_denied'));
                            } else {
                                showErrorMessage(Translator.get('platform:upload_fail'));
                            }
                        }

                        if (FU.index === FU.files.length) {
                            setTimeout(function(){
                                $('div.resources-progress-bar').remove();
                            }, 500);
                            FU.index = 0;
                            FU.active = false;
                            FU.files = [];
                        }
                    }
                });
            }
        }
    };

    function showErrorMessage(message) {
        var alertUl = $('.alert-error > ul');
        if (alertUl.length > 0) {
            alertUl.append(
                '<li>'+message+'</li>'
            );
        } else {
            $('.resource-manager').prepend(
                '<div class="alert alert-error">'
            +      '<a class="close" href="#" data-dismiss="alert">×</a>'
            +      '<ul>'
            +          '<li>' + message + '</li>'
            +      '</ul>'
            +   '</div>'
            );
        }
    }

    function onFiles(files) {
        FileAPI.each(files, function (file) {
            if ( file.size >= 25*FileAPI.MB ){
                showErrorMessage(Translator.get('platform:max_size_25mb'));
            } else if ( file.size === void 0 ){
                showErrorMessage(Translator.get('platform:empty_file'));
            } else {
                FU.add(file);
            }
        });
        FU.start();
    }
})();