"use strict";

//var mediaSource = new MediaSource();
//mediaSource.addEventListener('sourceopen', handleSourceOpen, false);
var mediaRecorder;
var recordedBlobs; // array of chunk video blobs
var sourceBuffer;
var mediaStream;

// audio input volume visualisation
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



navigator.getUserMedia = navigator.getUserMedia || navigator.webkitGetUserMedia || navigator.mozGetUserMedia;
var constraints = {
  audio: true,
  video: true
};


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
  resetData();
});


function handleDataAvailable(event) {

  if (event.data && event.data.size > 0) {
    //console.log('yep');
    recordedBlobs.push(event.data);
  }
}

/*
function handleStop(event) {
  console.log('Recorder stopped: ', event);
}
*/
/*function handleSourceOpen(event) {
  console.log('MediaSource opened');
  sourceBuffer = mediaSource.addSourceBuffer('video/webm; codecs="vp8"');
  console.log('Source buffer: ', sourceBuffer);
}*/


function record() {
  //navigator.getUserMedia(constraints, successCallback, errorCallback);

  recordedBlobs = [];
  navigator.getUserMedia(
    constraints,
    function(stream) {

      console.log('getUserMedia() got stream: ', stream);
      window.stream = stream;
      mediaStream = stream;

      $('#video-record-start').prop('disabled', 'disabled');
      $('#video-record-stop').prop('disabled', '');

      var options = {
        mimeType: 'video/webm'
      };

    //  mediaRecorder = new MediaRecorder(stream, options);

      try {
        mediaRecorder = new MediaRecorder(stream, options);
      } catch (e0) {
        console.log('Unable to create MediaRecorder with options Object: ', e0);
        try {
          options = {
            mimeType: 'video/webm,codecs=vp9'
          };
          mediaRecorder = new MediaRecorder(stream, options);
        } catch (e1) {
          console.log('Unable to create MediaRecorder with options Object: ', e1);
          try {
            options = 'video/vp8'; // Chrome 47
            mediaRecorder = new MediaRecorder(stream, options);
          } catch (e2) {
            alert('MediaRecorder is not supported by this browser.\n\n' +
              'Try Firefox 29 or later, or Chrome 47 or later, with Enable experimental Web Platform features enabled from chrome://flags.');
            console.error('Exception while creating MediaRecorder:', e2);
            return;
          }
        }
      }

      //mediaRecorder.onstop = handleStop;
      mediaRecorder.ondataavailable = handleDataAvailable;
      mediaRecorder.start(10); // collect 10ms of data
      console.log('MediaRecorder started', mediaRecorder);

      videoPlayer.muted = true;
      if (window.URL) {
        videoPlayer.src = window.URL.createObjectURL(stream);
      } else {
        videoPlayer.src = stream;
      }

      videoPlayer.play();
      gotStream(stream);
    },
    function(error) {
      console.log(error);
    });
}

function resetData() {
  cancelAnalyserUpdates();

  if(mediaStream){
    mediaStream = null;
  }
  recordedBlobs = null;
  mediaSource = null;
  mediaRecorder = null;
  sourceBuffer = null;
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

  mediaRecorder.stop();
  $('#video-record-start').prop('disabled', '');
  $('#video-record-stop').prop('disabled', 'disabled');
  $('#submitButton').prop('disabled', false);

  /*if(window.stream){
    window.stream.stop();
  }*/

  if(mediaStream){
    mediaStream.stop();
  }

  loadRecordedStream();


}

function loadRecordedStream() {
  videoPlayer.pause();
  videoPlayer.muted = false;
  var superBuffer = new Blob(recordedBlobs, {
    type: 'video/webm'
  });
  videoPlayer.src = window.URL.createObjectURL(superBuffer);

  videoPlayer.onended = function() {
    var blob = new Blob(recordedBlobs, {
      type: 'video/webm'
    });
    videoPlayer.src = URL.createObjectURL(blob);
  };
}




// use with claro new Resource API
function uploadVideo() {
  $('#video-record-start').prop('disabled', 'disabled');
  $('#video-record-stop').prop('disabled', 'disabled');
  var formData = new FormData();
  formData.append('nav', 'firefox');
  var video = new Blob(recordedBlobs, {
    type: 'video/webm'
  });
  formData.append('video', video);

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
    //  location.reload();

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


function download() {
  var blob = new Blob(recordedBlobs, {type: 'video/webm'});
  var url = window.URL.createObjectURL(blob);
  var a = document.createElement('a');
  a.style.display = 'none';
  a.href = url;
  a.download = 'test.webm';
  document.body.appendChild(a);
  a.click();
  setTimeout(function() {
    document.body.removeChild(a);
    window.URL.revokeObjectURL(url);
  }, 100);
}

function downloadVideo() {
  videoRecorder.save();
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
