"use strict";

import * as VolumeMeter from './libs/volume-meter';

const isFirefox = !!navigator.mediaDevices.getUserMedia;

const isDebug = false;
if(isDebug){
  console.log(isFirefox ? 'firefox':'chrome');
}
let mediaRecorder;
let recordedBlobs; // array of chunk video blobs

// audio input volume visualisation
let audioContext = new window.AudioContext();
let audioInput = null,
  realAudioInput = null,
  inputPoint = null;
let rafID = null;
let analyserContext = null;
let analyserNode = null;
let canvasWidth, canvasHeight;
let gradient;
let meter; // audio vu-meter

// avoid the recorded file to be chunked by setting a slight timeout
const recordEndTimeOut = 1000;
const videoPlayer = document.querySelector('video');

//navigator.getUserMedia = navigator.getUserMedia || navigator.webkitGetUserMedia || navigator.mediaDevices.getUserMedia;

const constraints = {
  audio: true,
  video: true
};


$('.modal').on('shown.bs.modal', function() {
  console.log('modal shown');

  /*===============================*/
  /*====== Register Events ========*/
  /*===============================*/

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

  $('#video-record-start').on('click', record);
  $('#video-record-stop').on('click', stopRecording);
  $('#btn-video-download').on('click', downloadVideo);
  $('#submitButton').on('click', uploadVideo);

  /*===============================*/
  /*===== Init Dom Components =====*/
  /*===============================*/

  videoPlayer.controls = false;

});

$('.modal').on('hide.bs.modal', function() {
  console.log('modal closed');
  resetData();
});

// store stream chunks
function handleDataAvailable(event) {
  if (event.data && event.data.size > 0) {
    recordedBlobs.push(event.data);
  }
}

// getUserMedia() polyfill
// see here https://developer.mozilla.org/en-US/docs/Web/API/MediaDevices/getUserMedia
const promisifiedOldGUM = function(constraints, successCallback, errorCallback) {

  // First get ahold of getUserMedia, if present
  let getUserMedia = (navigator.getUserMedia ||
      navigator.webkitGetUserMedia ||
      navigator.mozGetUserMedia);

  // Some browsers just don't implement it - return a rejected promise with an error
  // to keep a consistent interface
  if(!getUserMedia) {
    return Promise.reject(new Error('getUserMedia is not implemented in this browser'));
  }

  // Otherwise, wrap the call to the old navigator.getUserMedia with a Promise
  return new Promise(function(successCallback, errorCallback) {
    getUserMedia.call(navigator, constraints, successCallback, errorCallback);
  });

}

// Older browsers might not implement mediaDevices at all, so we set an empty object first
if(navigator.mediaDevices === undefined) {
  navigator.mediaDevices = {};
}


// Some browsers partially implement mediaDevices. We can't just assign an object
// with getUserMedia as it would overwrite existing properties.
// Here, we will just add the getUserMedia property if it's missing.
if(navigator.mediaDevices.getUserMedia === undefined) {
  navigator.mediaDevices.getUserMedia = promisifiedOldGUM;
}


function record() {
  $('#video-record-start').prop('disabled', 'disabled');
  $('#video-record-stop').prop('disabled', '');
  //navigator.mediaDevices.getUserMedia
  navigator.mediaDevices.getUserMedia(constraints)
  .then(
    gumSuccess
  ).catch(
    gumError
  );
}

// getUserMedia Success Callback
function gumSuccess(stream){
  if (isDebug) {
    console.log('success');
    console.log('getUserMedia() got stream: ', stream);
  }
  window.stream = stream;
  recordStream();
  createVolumeMeter();
}

// getUserMedia Error Callback
function gumError(error){
  const msg = 'navigator.getUserMedia error.';
  showError(msg, false);
  if (isDebug) {
    console.log(msg, error);
  }
}

function recordStream() {
  const options = {
    mimeType: 'video/webm',
    audioBitsPerSecond: 128000,
    videoBitsPerSecond: 1024000
  };

  recordedBlobs = [];
  try {
    mediaRecorder = new MediaRecorder(window.stream, options);
  } catch (e) {
    const msg = 'Unable to create MediaRecorder with options Object.';
    showError(msg, false);
    if (isDebug) {
      console.log(msg, e);
    }
  }

  mediaRecorder.ondataavailable = handleDataAvailable;
  mediaRecorder.start(10); // collect 10ms of data
  if (isDebug) {
    console.log('MediaRecorder started', mediaRecorder);
  }

  if (window.URL) {
    videoPlayer.src = window.URL.createObjectURL(window.stream);
  } else {
    videoPlayer.src = window.stream;
  }
  videoPlayer.muted = true;
  videoPlayer.play();
}

function resetData() {
  cancelAnalyserUpdates();

  if (window.stream) {
    window.stream.getAudioTracks().forEach(function(track) {
      track.stop();
    });
    window.stream.getVideoTracks().forEach(function(track) {
      track.stop();
    });
  }

  recordedBlobs = null;
  mediaRecorder = null;
  audioContext = null;
  audioInput = null;
  realAudioInput = null;
  inputPoint = null;
  rafID = null;
  analyserContext = null;
  analyserNode = null;
}

function stopRecording() {

  videoPlayer.pause();

  window.setTimeout(function() {
    mediaRecorder.stop();
    $('#video-record-start').prop('disabled', '');
    $('#video-record-stop').prop('disabled', 'disabled');
    $('#submitButton').prop('disabled', false);
    if (isDebug) {
      console.log(recordedBlobs);
    }
    let superBuffer = new Blob(recordedBlobs, {
      type: 'video/webm'
    });


    videoPlayer.src = window.URL ? window.URL.createObjectURL(superBuffer) : superBuffer;
    videoPlayer.muted = false;
    videoPlayer.controls = true;

    videoPlayer.onended = function() {
      let blob = new Blob(recordedBlobs, {
        type: 'video/webm'
      });
      videoPlayer.src = window.URL ? window.URL.createObjectURL(blob) : blob;
    };
  }, recordEndTimeOut);

}


// use with claro new Resource API
function uploadVideo() {
  $('#video-record-start').prop('disabled', 'disabled');
  $('#video-record-stop').prop('disabled', 'disabled');

  let formData = new FormData();
  const nav = isFirefox ? 'firefox' : 'chrome';
  formData.append('nav', nav);
  let video = new Blob(recordedBlobs, {
    type: 'video/webm'
  });
  formData.append('video', video);

  const fileName = $("#resource-name-input").val();
  formData.append('fileName', fileName);

  const route = $('#arForm').attr('action');
  xhr(route, formData, null, function(fileURL) {});
}

function xhr(url, data, progress, callback) {

  const message = Translator.trans('creating_resource', {}, 'innova_video_recorder');
  // tell the user that his action has been taken into account
  $('#submitButton').text(message);
  $('#submitButton').attr('disabled', true);

  $('#submitButton').append('&nbsp;<i id="spinner" class="fa fa-spinner fa-spin"></i>');

  let request = new XMLHttpRequest();
  request.onreadystatechange = function() {
    if (request.readyState === 4 && request.status === 200) {
      if (isDebug) {
        console.log('xhr end with success');
      }
      resetData();

      // use reload or generate route...
      location.reload();

    } else if (request.status === 500) {
      if (isDebug) {
        console.log('xhr error');
      }
      $('#spinner').remove();
      const msg = Translator.trans('resource_creation_error', {}, 'innova_video_recorder');
      showError(msg, true);
    }
  };

  request.upload.onprogress = function(e) {
    // if we want to use progress bar
  };

  request.open('POST', url, true);
  request.send(data);
}

function showError(msg, canDownload = false) {

  $('#form-error-msg').text(msg);
  $('#form-error-msg-row').show();
  // allow user to save the recorded file on his device...
  if (canDownload) {
    $('#form-error-download-msg').show();
    $('#btn-video-download').show();
  }
  // change form view
  $('#form-content').hide();
  $('#submitButton').hide();
}


function downloadVideo() {
  let blob = new Blob(recordedBlobs, {
    type: 'video/webm'
  });
  const url = window.URL.createObjectURL(blob);
  let a = document.createElement('a');
  a.style.display = 'none';
  a.href = url;

  let fileName = $("#resource-name-input").val();
  a.download = fileName + '.webm';
  document.body.appendChild(a);
  a.click();
  setTimeout(function() {
    document.body.removeChild(a);
    window.URL.revokeObjectURL(url);
  }, 100);
}

function createVolumeMeter() {
  inputPoint = audioContext.createGain();
  // Create an AudioNode from the stream.
  realAudioInput = audioContext.createMediaStreamSource(window.stream);

  meter = VolumeMeter.createAudioMeter(audioContext);
  realAudioInput.connect(meter);
  draw();
}

function draw() {

  if (!analyserContext) {
    const canvas = document.getElementById("analyser");
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
  rafID = window.requestAnimationFrame(draw);
}

function cancelAnalyserUpdates() {
  window.cancelAnimationFrame(rafID);
  // clear the current state
  if (analyserContext) {
    analyserContext.clearRect(0, 0, canvasWidth, canvasHeight);
  }
  rafID = null;
}
