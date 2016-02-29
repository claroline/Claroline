"use strict";

var isFirefox = !!navigator.mozGetUserMedia;

var audioObject;
var audioRecorder; // WebRtc object

var audioContext = new window.AudioContext();
var audioInput = null,
        realAudioInput = null,
        inputPoint = null;
var rafID = null;
var analyserContext = null;
var analyserNode = null;
var canvasWidth, canvasHeight;
var gradient;
var meter;

var aid = 0; // audio array current recording index
var aRecorders = []; // collection of recorders
var audios = []; // collection of audio objects
var aStream; // current recorder stream

// avoid the recorded file to be chunked by setting a slight timeout
var recordEndTimeOut = 512;

function recordAudio() {

    captureUserMedia({
        audio: true
    }, function (audioStream) {

        $('#audio-record-start').prop('disabled', 'disabled');
        $('#audio-record-stop').prop('disabled', '');

        var options = {
            type: 'audio',
            bufferSize: 1024,
            sampleRate: 44100
        };

        audioRecorder = RecordRTC(audioStream, options);

        audioRecorder.startRecording();
        gotStream(audioStream);

        aStream = audioStream;

        audioStream.onended = function () {
            console.log('stream ended');
        };
    }, function (error) {
        console.log(error);
    });
}

$('.modal').on('shown.bs.modal', function () {
    console.log('modal shown');
    // file name check and change
    $("#resource-name-input").on("change paste keyup", function () {
        if ($(this).val() === '') { // name is blank
            $(this).attr('placeholder', 'provide a name for the resource');
            $('#submitButton').prop('disabled', true);
        } else if ($('input:checked').length > 0) { // name is set and a recording is selected
            $('#submitButton').prop('disabled', false);
        }
        // remove blanks
        $(this).val(function (i, val) {
            return val.replace(' ', '_');
        });
    });
});

$('.modal').on('hide.bs.modal', function () {

    console.log('modal closed');

    cancelAnalyserUpdates();

    if (aStream)
        aStream.stop();
    audios = [];
    aRecorders = [];

    audioContext = null;
    audioInput = null;
    realAudioInput = null;
    inputPoint = null;
    rafID = null;
    analyserContext = null;
    analyserNode = null;
    aStream = null;
    aid = 0;
});

function stopRecordingAudio() {
    var aRec = audioRecorder;
    $('#audio-record-start').prop('disabled', '');
    $('#audio-record-stop').prop('disabled', 'disabled');

    // avoid recorded audio truncated end by setting a timeout
    window.setTimeout(function () {

        audioRecorder.stopRecording(function (url) {
            cancelAnalyserUpdates();
            audioObject = new Audio();
            audioObject.src = url;
            audios.push(audioObject);

            aRecorders.push(aRec);

            // recorded audio template
            var html = '<div class="row recorded-audio-row" id="recorded-audio-row-' + aid.toString() + '" data-index="' + aid + '">';
            html += '       <div class="col-md-8">';
            html += '         <div class="btn-group">';
            html += '           <button type="button" role="button" class="btn btn-default fa fa-play play" onclick="playAudio(this)"></button>';
            html += '           <button type="button" role="button" class="btn btn-default fa fa-stop stop" onclick="stopAudio(this)"></button>';
            html += '           <button type="button" role="button" class="btn btn-danger fa fa-trash delete" onclick="deleteAudio(this)"></button>';
            html += '         </div>';
            html += '       </div>';
            html += '       <div class="col-md-4">';
            html += '         <input type="radio" name="audio-selected" class="select" onclick="audioSelected(this)">';
            html += '       </div>';
            html += '       <hr/>';
            html += '   </div>';
            $('#audio-records-container').append(html);

            aid++;
            // stop sharing usermedia
            if (aStream) {
                aStream.stop();
            }
        });
    }, recordEndTimeOut);
}

function audioSelected(elem) {
    $('#submitButton').prop('disabled', false);
}

function playAudio(elem) {
    var index = $(elem).closest('.recorded-audio-row').attr('data-index');
    audios[index].play();
}

function stopAudio(elem) {
    var index = $(elem).closest('.recorded-audio-row').attr('data-index');
    audios[index].pause();
    audios[index].currentTime = 0;
}

function deleteAudio(elem) {
    var index = $(elem).closest('.recorded-audio-row').attr('data-index');
    audios.splice(index, 1);
    aRecorders.splice(index, 1);

    $('#recorded-audio-row-' + index.toString()).remove();
    if (audios.length === 0) {
        $('#submitButton').prop('disabled', true);
    }

    // rebuilt all row id(s) and index
    $('.recorded-audio-row').each(function (i) {
        console.log('rebuilt row data-indexes');
        $(this).attr('id', 'recorded-audio-row-' + i.toString());
        $(this).attr('data-index', i);
    });

    aid = audios.length;
}


// use with claro new Resource API
function uploadAudio() {
    // get selected audio index
    var index = -1;
    index = $('input:checked').closest('.recorded-audio-row').attr('data-index');
    if (index > -1) {
        var recorder = aRecorders[index];
        var blob = recorder.getBlob();
        var formData = new FormData();
        // nav should be mandatory
        if (isFirefox) {
            formData.append('nav', 'firefox');
        } else {
            formData.append('nav', 'chrome');
        }
        // convert is optionnal
        formData.append('convert', true);
        // file is mandatory
        formData.append('file', blob);
        // filename is mandatory
        var fileName = $("#resource-name-input").val();
        formData.append('fileName', fileName);

        var route = $('#arForm').attr('action');
        xhr(route, formData, null, function (fileURL) {});
    }
}

function xhr(url, data, progress, callback) {

    var message = Translator.trans('creating_resource', {}, 'innova_audio_recorder');
    // tell the user that his action has been taken into account
    $('#submitButton').text(message);
    $('#submitButton').attr('disabled', true);

    var request = new XMLHttpRequest();
    request.onreadystatechange = function () {
        if (request.readyState === 4 && request.status === 200) {
            console.log('xhr end with success');
            audios = [];
            aRecorders = [];

            audioContext = null;
            audioInput = null;
            realAudioInput = null;
            inputPoint = null;
            rafID = null;
            analyserContext = null;
            analyserNode = null;
            aStream = null;
            aid = 0;
            // use reload or generate route...
            location.reload();

        } else if (request.status === 500) {
            console.log('xhr error');
            //var errorMessage = Translator.trans('resource_creation_error', {}, 'innova_audio_recorder');
            //$('#form-error-msg').text(errorMessage);
            $('#form-error-msg-row').show();
            // allow user to save the recorded file on his device...
            var index = -1;
            index = $('input:checked').closest('.recorded-audio-row').attr('data-index');
            if (index > -1) {
                // show download button
                $('#btn-audio-download').show();
                $('#form-content').hide();
                $('#submitButton').hide();
            }
        }
    };

    request.upload.onprogress = function (e) {
        // if we want to use progress bar
    };

    request.open('POST', url, true);
    request.send(data);

}

function downloadAudio() {
    var index = $('input:checked').closest('.recorded-audio-row').attr('data-index');
    var recorder = aRecorders[index];
    recorder.save();
}

function captureUserMedia(mediaConstraints, successCallback, errorCallback) {
    // needs adapter.js to work in chrome
    navigator.mediaDevices.getUserMedia(mediaConstraints).then(successCallback).catch(errorCallback);
}


function gotStream(stream) {
    inputPoint = audioContext.createGain();
    // Create an AudioNode from the stream.
    realAudioInput = audioContext.createMediaStreamSource(stream);

    meter = createAudioMeter(audioContext);
    realAudioInput.connect(meter);
    drawLoop();
}

function drawLoop(time) {

    if (!analyserContext) {
        var canvas = document.getElementById("analyser");
        canvasWidth = canvas.width;
        canvasHeight = canvas.height;
        analyserContext = canvas.getContext('2d');
        gradient = analyserContext.createLinearGradient(0, 0, canvasWidth, 0);
        gradient.addColorStop(0.15, '#ffff00'); // min level color
        gradient.addColorStop(0.80, '#ff0000'); // max level color
    }

    // clear the background
    analyserContext.clearRect(0, 0, canvasWidth, canvasHeight);

    analyserContext.fillStyle = gradient;
    // draw a bar based on the current volume
    analyserContext.fillRect(0, 0, meter.volume * canvasWidth * 1.4, canvasHeight);

    // set up the next visual callback
    rafID = window.requestAnimationFrame(drawLoop);
}

function cancelAnalyserUpdates() {
    window.cancelAnimationFrame(rafID);
    // clear the current state
    if (analyserContext) {
        analyserContext.clearRect(0, 0, canvasWidth, canvasHeight);
    }
    rafID = null;
}
