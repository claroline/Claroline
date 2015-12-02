/*
 * This file is part of the Claroline Connect package.
 *
 * (c) Claroline Consortium <consortium@claroline.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/* global FileAPI */
/* global Translator */

(function () {
    'use strict';
    $(document).on('ready', function () {
        if (!(FileAPI.support.cors || FileAPI.support.flash)) {
            alert('Flash is needed');
        } else {
            var dropzoneEnabled = false;
            var dropEnabled = false;

            if (FileAPI.support.dnd) {
                $(document).dnd(function (over, evt) {
                    dropEnabled = ($(evt.target).hasClass('nodes') || $(evt.target).parents('.nodes').length > 0);
                    if (over) {
                        if (!dropzoneEnabled) {
                            $('.nodes').addClass('dropzone');
                            $('.nodes').after('<div class="dropzone-text"></div>');
                            dropzoneEnabled = true;
                        }
                    } else {
                        if (dropzoneEnabled) {
                            $('.nodes').removeClass('dropzone');
                            $('.dropzone-text').remove();
                            dropzoneEnabled = false;
                        }
                    }

                    if (dropEnabled) {
                        $('.dropzone-text').html(Translator.trans('drop_file', {}, 'platform'));
                    } else {
                        $('.dropzone-text').html(Translator.trans('drag_file_here', {}, 'platform'));
                    }
                }, function (files) {
                    if (dropEnabled) {
                        onFiles(files);
                    }
                });
            }
        }
    });

    var FU = {
        files: [],
        index: 0,
        active: false,

        add: function (file) {
            var manager = Claroline.ResourceManager.get('main');
            var currentDirectoryId = manager.parameters.currentDirectoryId || manager.parameters.directoryId;

            if (currentDirectoryId == 0) {
                window.Claroline.Modal.simpleContainer(null, Translator.trans('desktop_root_directory_upload_error', {}, 'platform'));
                return;
            }
            
            FU.files.push(file);
        },

        getFileById: function (id) {
            var i = FU.files.length;
            while (i--) {
                if (FileAPI.uid(FU.files[i]) === id) {
                    return  FU.files[i];
                }
            }
        },

        start: function () {
            if (!FU.active && (FU.active = FU.files.length > FU.index)) {
                FU._upload(FU.files[FU.index]);
            }
        },

        abort: function (id) {
            var file = this.getFileById(id);
            if (file.xhr) {
                file.xhr.abort();
            }
        },

        _upload: function (file) {
            if (file) {
                var manager = Claroline.ResourceManager.get('main');
                var currentDirectoryId = manager.parameters.currentDirectoryId || manager.parameters.directoryId;

                file.xhr = FileAPI.upload({
                    url: Routing.generate('claro_file_upload_with_ajax', {'parent': currentDirectoryId}),
                    imageAutoOrientation: true,
                    data: { fileName: file.name },
                    files: { file: file },
                    upload: function () {
                        if (FU.index === 0) {
                            $('.nodes').after(
                                '<div class="resources-progress-bar"><div>' + Translator.trans('upload', {}, 'platform') +
                                '<div class="progress progress-striped active">' +
                                '<div class="progress-bar progress-bar-info" role="progressbar" style="width: 0%;" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0">' +
                                '</div></div><div id="progress-files">1/' + FU.files.length + '</div></div></div>'
                            );
                        }
                    },
                    progress: function (evt) {
                        var progress = ((evt.loaded / evt.total * (100 / FU.files.length)) +
                            ((100 / FU.files.length) * FU.index));
                        $('div.progress > div.progress-bar').css('width', progress + '%');
                        $('div.progress > div.progress-bar').attr('aria-valuenow', progress);
                    },
                    complete: function (err, xhr) {
                        FU.index++;
                        FU.active = false;
                        $('#progress-files').html(FU.index + '/' + FU.files.length);
                        var progress = (100 / FU.files.length) * FU.index;
                        $('div.progress > div.progress-bar').css('width', progress + '%');
                        $('div.progress > div.progress-bar').attr('aria-valuenow', progress);
                        FU.start();

                        if (xhr.status === 200) {
                            var jsonResponse = $.parseJSON(xhr.response);

                            var event = {
                                nodeId: currentDirectoryId,
                                view: 'main',
                                fromRouter: true
                            };

                            manager.dispatcher.trigger('open-directory', event)
                        } else {
                            if (xhr.status === 403) {
                                showErrorMessage(Translator.trans('upload_denied', {}, 'platform'));
                            } else {
                                showErrorMessage(Translator.trans('upload_fail', {}, 'platform'));
                            }
                        }

                        if (FU.index === FU.files.length) {
                            setTimeout(function () {
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
        var alertUl = $('.alert-danger > ul');
        if (alertUl.length > 0) {
            alertUl.append(
                '<li>' + message + '</li>'
            );
        } else {
            $('.resource-manager').prepend(
                '<div class="alert alert-danger">' + '<a class="close" href="#" data-dismiss="alert" aria-hidden="true">&times;</a>' + '<ul>' +
                '<li>' + message + '</li>' + '</ul>' + '</div>'
            );
        }
    }

    function onFiles(files) {
        var maxSize = $('#data-attributes').attr('data-max-post-size');
        var lastChar = maxSize.substr(maxSize.length - 1);
        var varSize = maxSize.slice(0, maxSize.length - 1);
        var size = maxSize;

        FileAPI.each(files, function (file) {
            if (maxSize !== 0) {
                switch(lastChar) {
                    case 'M': size = varSize * FileAPI.MB; break;
                    case 'K': size = varSize * FileAPI.KB; break;
                    case 'G': size = varSize * FileAPI.GB; break;
                }
            }

            if (file.size >= size) {
                showErrorMessage(Translator.trans('max_size_is', {'size': maxSize}, 'platform'));
            } else if (file.size === void 0) {
                showErrorMessage(Translator.trans('empty_file', {}, 'platform'));
            } else {
                FU.add(file);
            }
        });
        FU.start();
    }
})();
