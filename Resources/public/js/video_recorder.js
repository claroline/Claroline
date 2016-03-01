"use strict";

var isFirefox = !!navigator.mozGetUserMedia;

var videoRecorder; // WebRTC object
var audioRecorder; // WebRTC object

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
var audioPlayer = document.querySelector('audio');

var audioBlob, videoBlob;
var recorderStream;
var isRecording = false;


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

  $("video").on("play", function() {
    console.log("you pressed play");
    if (!isFirefox && !isRecording) {
      audioPlayer.play();
    }
  });

  $("video").on("pause", function() {
    console.log("you pressed pause");
    if (!isFirefox && !isRecording) {
      audioPlayer.pause();
    }
  });
});

$('.modal').on('hide.bs.modal', function() {

  console.log('modal closed');
  resetData();

});


function record() {
  isRecording = true;
  captureUserMedia({
      video: true,
      audio: true
    },
    function(stream) {

      $('#video-record-start').prop('disabled', 'disabled');
      $('#video-record-stop').prop('disabled', '');

      var options = {
        type: 'video',
        disableLogs: false
      };

      videoRecorder = RecordRTC(stream, options);
      // webkit user agent
      if (!isFirefox) {
        console.log('start recording for !firefox');
        audioRecorder = RecordRTC(stream, {
          type: 'audio',
          disableLogs: false,
          onAudioProcessStarted: function() {
            videoRecorder.startRecording();
          }
        });
        audioRecorder.startRecording();
      } else {
        console.log('start recording for firefox');
        videoRecorder.startRecording();
      }

      videoPlayer.pause();
      videoPlayer.muted = true;
      videoPlayer.src = window.URL.createObjectURL(stream);
      videoPlayer.play();

      recorderStream = stream;

      gotStream(stream);

      stream.onended = function() {
        console.log('stream ended');
      };
    },
    function(error) {
      console.log(error);
      isRecording = false;
    });
}

function resetData() {
  cancelAnalyserUpdates();
  if (videoRecorder) {
    videoRecorder.clearRecordedData();
  }

  if (audioRecorder) {
    audioRecorder.clearRecordedData();
  }

  if (recorderStream) {
    recorderStream.stop();
  }

  videoBlob = null;
  audioBlob = null;

  audioContext = null;
  audioInput = null;
  realAudioInput = null;
  inputPoint = null;
  rafID = null;
  analyserContext = null;
  analyserNode = null;
  isRecording = false;
}

function stopRecording() {

  // avoid recorded blob truncated end by setting a timeout
  window.setTimeout(function() {
    isRecording = false;
    $('#video-record-start').prop('disabled', '');
    $('#video-record-stop').prop('disabled', 'disabled');
    $('#submitButton').prop('disabled', false);

    var aUrl = null;
    // webkit based user agent
    if (!isFirefox) {
      console.log('not firefox');
      audioRecorder.stopRecording(function(audioUrl) {
        aUrl = audioUrl;
        videoRecorder.stopRecording(function(videoUrl) {
          // wav audio
          audioBlob = audioRecorder.blob;
          // webm video
          videoBlob = videoRecorder.blob;
          previewRecordings(videoUrl, audioUrl);
        });
      });
    } else {
      videoRecorder.stopRecording(function(videoUrl) {
        // webm containing audio + video
        videoBlob = videoRecorder.blob;
        previewRecordings(videoUrl, null);
      });
    }

    // stop sharing usermedia
    if (recorderStream) {
      recorderStream.stop();
    }

  }, recordEndTimeOut);
}


function previewRecordings(videoUrl, audioUrl) {
  cancelAnalyserUpdates();
  // @TODO do the same for audio element if !isFirefox
  videoPlayer.pause();
  videoPlayer.muted = false;
  videoPlayer.srcObject = null;
  videoPlayer.src = videoUrl;
  videoPlayer.load();
  if (!isFirefox) {
    audioPlayer.pause();
    audioPlayer.src = audioUrl;
    audioPlayer.load();
  }
  videoPlayer.onended = function() {
    videoPlayer.pause();
    videoPlayer.src = URL.createObjectURL(videoBlob);
    if (!isFirefox) {
      audioPlayer.pause();
      audioPlayer.src = URL.createObjectURL(audioBlob);
    }
  };
}


// use with claro new Resource API
function uploadVideo() {
  $('#video-record-start').prop('disabled', 'disabled');
  $('#video-record-stop').prop('disabled', 'disabled');
  var formData = new FormData();
  if (isFirefox) {
    formData.append('nav', 'firefox');
  } else {
    formData.append('nav', 'chrome');
    formData.append('audio', audioBlob);
  }

  formData.append('video', videoBlob);

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
      resetData();
      // use reload or generate route...
      location.reload();

    } else if (request.status === 500) {
      console.log('xhr error');
      var errorMessage = Translator.trans('resource_creation_error', {}, 'innova_video_recorder');
      $('#form-error-msg-row').show();
      // allow user to save the recorded file on his device... only if firefox ? (in chrome user would have to donwload 2 files and merge them)
      if (isFirefox) {
        $('#btn-video-download').show();
      } else {
        $('#form-error-download-msg').hide();
      }
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
