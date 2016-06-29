"use strict";

import Meter from './libs/js-meter';

const isFirefox = !!navigator.mediaDevices.getUserMedia;

const isDebug = false;
if (isDebug) {
  console.log(isFirefox ? 'firefox' : 'chrome');
}
let recorder;
let recordedBlobs; // array of chunk video blobs

// audio input volume visualisation
let audioContext = new window.AudioContext();
let realAudioInput = null;
let meter;

let maxTime = 0;
let currentTime = 0;

let intervalID;

// avoid the recorded file to be chunked by setting a slight timeout
const recordEndTimeOut = 1000;
// video element
const preview = document.getElementById('preview');
// gum constraints
const constraints = {
  audio: true,
  video: true
};

$('.modal').on('shown.bs.modal', function() {
  if (isDebug) {
    console.log('modal shown');
  }
});

$('.modal').on('hide.bs.modal', function() {
  if (isDebug) {
    console.log('modal closed');
  }
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
  if (!getUserMedia) {
    return Promise.reject(new Error('getUserMedia is not implemented in this browser'));
  }

  // Otherwise, wrap the call to the old navigator.getUserMedia with a Promise
  return new Promise(function(successCallback, errorCallback) {
    getUserMedia.call(navigator, constraints, successCallback, errorCallback);
  });

}

// Older browsers might not implement mediaDevices at all, so we set an empty object first
if (navigator.mediaDevices === undefined) {
  navigator.mediaDevices = {};
}


// Some browsers partially implement mediaDevices. We can't just assign an object
// with getUserMedia as it would overwrite existing properties.
// Here, we will just add the getUserMedia property if it's missing.
if (navigator.mediaDevices.getUserMedia === undefined) {
  navigator.mediaDevices.getUserMedia = promisifiedOldGUM;
}

navigator.mediaDevices.getUserMedia(constraints)
  .then(
    gumSuccess
  ).catch(
    gumError
  );


// getUserMedia Success Callback
function gumSuccess(stream) {
  if (isDebug) {
    console.log('success');
    console.log('getUserMedia() got stream: ', stream);
  }
  window.stream = stream;

  if (window.URL) {
    preview.src = window.URL.createObjectURL(window.stream);
  } else {
    preview.src = window.stream;
  }
  preview.muted = true;
  preview.play();
  init();
}

// getUserMedia Error Callback
function gumError(error) {
  const msg = 'navigator.getUserMedia error.';
  $('#video-record-start').prop('disabled', '');
  if (isDebug) {
    console.log(msg, error);
  }
}

function init() {
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

  $('#video-record-start').prop('disabled', '');
  preview.controls = false;
  maxTime = parseInt($('#maxTime').val());

  $('#video-record-start').on('click', recordStream);
  $('#video-record-stop').on('click', stopRecording);
  $('#btn-video-download').on('click', downloadVideo);
  $('#submitButton').on('click', uploadVideo);
  $('#retry').on('click', reinitRecording);  
  createVolumeMeter();
}

function recordStream() {

  $('#video-record-start').hide();
  $('#video-record-stop').show();

  const options = {
    mimeType: 'video/webm',
    audioBitsPerSecond: 128000,
    videoBitsPerSecond: 1024000
  };

  if (maxTime > 0) {
    intervalID = window.setInterval(function() {
      currentTime += 1;
      let hms = secondsTohhmmss(currentTime);
      $('.current-time').text(hms);
      if (currentTime === maxTime) {
        window.clearInterval(intervalID);
        stopRecording();
      }
    }, 1000);
  }

  recordedBlobs = [];
  try {
    recorder = new MediaRecorder(window.stream, options);
  } catch (e) {
    const msg = 'Unable to create MediaRecorder with options Object.';
    if (isDebug) {
      console.log(msg, e);
    }
  }

  recorder.ondataavailable = handleDataAvailable;
  recorder.start(10); // collect 10ms of data
  if (isDebug) {
    console.log('MediaRecorder started', recorder);
  }

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
}

function reinitRecording(){

  if (maxTime > 0) {
    currentTime = 0;
    let hms = secondsTohhmmss(currentTime);
    $('.current-time').text(hms);
  }

  $('#retry').prop('disabled', true);
  $('#retry').hide();
  $('#submitButton').prop('disabled', true);
  $('#video-record-start').show();
  $(".alert").hide();
  if (window.URL) {
    preview.src = window.URL.createObjectURL(window.stream);
  } else {
    preview.src = window.stream;
  }
  preview.muted = true;
  preview.play();
}

function stopRecording() {

  window.setTimeout(function() {


    window.clearInterval(intervalID);
    recorder.stop();

    $('#video-record-stop').hide();
    $('#retry').prop('disabled', false);
    $('#retry').show();
    $(".alert").show();
    $('#submitButton').prop('disabled', false);
    if (isDebug) {
      console.log(recordedBlobs);
    }

    let superBuffer = new Blob(recordedBlobs, {
      type: 'video/webm'
    });

    preview.src = window.URL ? window.URL.createObjectURL(superBuffer) : superBuffer;
    preview.muted = false;
    preview.controls = true;

    preview.onended = function() {
      let blob = new Blob(recordedBlobs, {
        type: 'video/webm'
      });
      preview.src = window.URL ? window.URL.createObjectURL(blob) : blob;
    };
  }, recordEndTimeOut);

}

// use with claro new Resource API
function uploadVideo() {

  $('#form-content').hide();
  $('.progress-row').show();

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

  $('#submitButton').attr('disabled', true);
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
      const msg = Translator.trans('resource_creation_error', {}, 'innova_video_recorder');
      $('.progress-row').hide();
      $('.error-row').show();
    }
  };
  // progress bar
  request.upload.onprogress = function(e) {
    if (e.lengthComputable) {
      let percent = Math.round(100 * e.loaded / e.total);
      var $div = $(".progress-bar");
      var $span = $div.find('span');
      $div.attr('aria-valuenow', percent);
      $div.css('width', percent + '%');
      $div.text(percent + '%');
      $span.text(percent + '%');
    }
  };

  request.open('POST', url, true);
  request.send(data);
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

function secondsTohhmmss(value) {
  var hours = Math.floor(value / 3600);
  var minutes = Math.floor((value - (hours * 3600)) / 60);
  var seconds = value - (hours * 3600) - (minutes * 60);

  // round seconds
  seconds = Math.round(seconds * 100) / 100;
  var result = '';
  if (hours > 0) {
    result += hours.toString() + ' h ';
  }

  if (minutes > 0) {
    result += minutes.toString() + ' min ';
  }
  result += seconds.toString() + ' s';
  return result;
}

function createVolumeMeter() {
  // Create an AudioNode from the stream.
  realAudioInput = audioContext.createMediaStreamSource(window.stream);
  meter = new Meter();
  meter.setup(audioContext, realAudioInput);
}

function cancelAnalyserUpdates() {
  if (meter) {
    window.cancelAnimationFrame(meter.rafID);
  }
}
