"use strict";

var isFirefox = !!navigator.mozGetUserMedia;

var videoRecorder; // WebRtc object

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

// avoid the recorded file to be chunked by setting a slight timeout
var recordEndTimeOut = 1000;
var videoPlayer = document.querySelector('video');

function record() {

  captureUserMedia({
      video: true,
      audio: true
    },
    function(stream) {

      $('#video-record-start').prop('disabled', 'disabled');
      $('#video-record-stop').prop('disabled', '');

      videoPlayer.pause();
      videoPlayer.muted = true;
      // videoPlayer.src = window.URL.createObjectURL(stream);
      videoPlayer.srcObject = stream;
      videoPlayer.load();
      videoPlayer.play();

      var options = {
        type: 'video',
        disableLogs: true/*,
        bufferSize: 4096,
        sampleRate: 44100*/
      };

      videoRecorder = RecordRTC(stream, options);
      videoRecorder.startRecording();
      gotStream(stream);

      stream.onended = function() {
        console.log('stream ended');
      };
    },
    function(error) {
      console.log(error);
    });
}

$('.modal').on('shown.bs.modal', function() {
  console.log('modal shown');
  // file name check and change
  $("#resource-name-input").on("change paste keyup", function() {
    if ($(this).val() === '') { // name is blank
      $(this).attr('placeholder', 'provide a name for the resource');
      $('#submitButton').prop('disabled', true);
    } else if ($('input:checked').length > 0) { // name is set and a recording is selected
      $('#submitButton').prop('disabled', false);
    }
    // remove blanks
    $(this).val(function(i, val) {
      return val.replace(' ', '_');
    });
  });
});

$('.modal').on('hide.bs.modal', function() {

  console.log('modal closed');
  cancelAnalyserUpdates();
  videoRecorder.clearRecordedData();

  audioContext = null;
  audioInput = null;
  realAudioInput = null;
  inputPoint = null;
  rafID = null;
  analyserContext = null;
  analyserNode = null;
});

function stopRecording() {

  $('#video-record-start').prop('disabled', '');
  $('#video-record-stop').prop('disabled', 'disabled');
  $('#submitButton').prop('disabled', false);

  // avoid recorded blob truncated end by setting a timeout
  window.setTimeout(function() {

    videoRecorder.stopRecording(function(url) {
      cancelAnalyserUpdates();
      videoPlayer.pause();
      videoPlayer.muted = false;
      videoPlayer.srcObject = null;
      videoPlayer.src = url;
      videoPlayer.load();
      videoPlayer.onended = function() {
        videoPlayer.pause();
        videoPlayer.src = URL.createObjectURL(videoRecorder.blob);
      };

    });
  }, recordEndTimeOut);
}


// use with claro new Resource API
function uploadVideo() {
  // get recorded blob
  var blob = videoRecorder.blob;
  var formData = new FormData();
  // nav should be mandatory
  if (isFirefox) {
    formData.append('nav', 'firefox');
  } else {
    formData.append('nav', 'chrome');
  }
  // convert is optionnal
  formData.append('convert', false);
  // file is mandatory
  formData.append('file', blob);
  // filename is mandatory
  var fileName = $("#resource-name-input").val();
  formData.append('fileName', fileName);

  var route = $('#arForm').attr('action');
  xhr(route, formData, null, function(fileURL) {});
}

function xhr(url, data, progress, callback) {

  var message = Translator.trans('creating_resource', {}, 'innova_video_recorder');
  // tell the user that his action has been taken into account
  $('#submitButton').text(message);
  $('#submitButton').attr('disabled', true);

  var request = new XMLHttpRequest();
  request.onreadystatechange = function() {
    if (request.readyState === 4 && request.status === 200) {
      console.log('xhr end with success');
      cancelAnalyserUpdates();
      videoRecorder.clearRecordedData();

      audioContext = null;
      audioInput = null;
      realAudioInput = null;
      inputPoint = null;
      rafID = null;
      analyserContext = null;
      analyserNode = null;
      // use reload or generate route...
      location.reload();

    } else if (request.status === 500) {
      console.log('xhr error');
      var errorMessage = Translator.trans('resource_creation_error', {}, 'innova_video_recorder');
      $('#form-error-msg-row').show();
      // allow user to save the recorded file on his device...
      $('#btn-video-download').show();
      // change form view
      $('#form-content').hide();
      $('#submitButton').hide();
    }
  };

  request.upload.onprogress = function(e) {
    // if we want to use progress bar
  };

  request.open('POST', url, true);
  request.send(data);

}

function downloadVideo() {
  videoRecorder.save();
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
