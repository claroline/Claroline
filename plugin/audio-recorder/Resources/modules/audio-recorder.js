"use strict";

import Meter from './libs/js-meter';

const isFirefox = !!navigator.mediaDevices.getUserMedia;

const isDebug = false;
if (isDebug) {
  console.log(isFirefox ? 'firefox' : 'chrome');
}

let recorder;
let tempRecordedBlobs; // array of chunked audio blobs

let audioContext = new window.AudioContext();
let realAudioInput = null;
let meter;

let aid = 0; // audio array current recording index
let aBlobs = []; // collection of audio blobs
let audios = []; // collection of audio objects for playing recorded audios

let maxTry;
let maxTime;
let nbTry = 0;
let currentTime = 0;
let intervalID;
// avoid the recorded file to be chunked by setting a slight timeout
const recordEndTimeOut = 512;

const constraints = {
  audio: true
};

// store stream chunks every 10 ms
function handleDataAvailable(event) {
  if (event.data && event.data.size > 0) {
    tempRecordedBlobs.push(event.data);
  }
}

$('.modal').on('shown.bs.modal', function() {
  console.log('modal shown');

});

$('body').on('click', '.play', function() {
  playAudio(this);
});
$('body').on('click', '.stop', function() {
  stopAudio(this);
});
$('body').on('click', '.delete', function() {
  deleteAudio(this);
});
$('body').on('click', 'input[name="audio-selected"]', function() {
  audioSelected(this);
});

$('.modal').on('hide.bs.modal', function() {
  console.log('modal closed');
  resetData();
});

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
  createVolumeMeter();
  init();
}

// getUserMedia Error Callback
function gumError(error) {
  const msg = 'navigator.getUserMedia error.';
  if (isDebug) {
    console.log(msg, error);
  }
}

function init(){
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

  $('#audio-record-start').on('click', recordStream);
  $('#audio-record-stop').on('click', stopRecording);
  $('#btn-audio-download').on('click', download);
  $('#submitButton').on('click', uploadAudio);

  maxTry = parseInt($('#maxTry').val());
  maxTime = parseInt($('#maxTime').val());


  $('#audio-record-start').prop('disabled', '');

  currentTime = 0;
}

function recordStream() {
  $('#audio-record-start').hide();
  $('#audio-record-stop').show();

  $('.max-try-reached').hide();
  $('.max-time-reached').hide();
  $('.stop-recording-message').hide();

  tempRecordedBlobs = [];
  try {
    recorder = new MediaRecorder(window.stream);
  } catch (e) {
    const msg = 'Unable to create MediaRecorder with options Object.';
    if (isDebug) {
      console.log(msg, e);
    }
  }

  recorder.ondataavailable = handleDataAvailable;
  recorder.start(10); // collect 10ms of data
  if (maxTime > 0) {
    intervalID = window.setInterval(function() {
      currentTime += 1;
      let hms = secondsTohhmmss(currentTime);
      $('.current-time').text(hms);
      if (currentTime === maxTime) {
        window.clearInterval(intervalID);
        stopRecording(true);
      }
    }, 1000);
  }

  nbTry++;
  if (isDebug) {
    console.log('MediaRecorder started', recorder);
  }
}

function stopRecording(maxTimeReached) {

  if (nbTry < maxTry) {
    $('#audio-record-start').prop('disabled', false);

  }  else {
    $('#audio-record-start').prop('disabled', true);
    $('.max-try-reached').show();
  }

  $('#audio-record-stop').hide();
  $('#audio-record-start').show();

  if(maxTimeReached === true){
    $('.max-time-reached').show();
  } else {
    $('.stop-recording-message').show();
  }
  // keep recording time
  let recLength = currentTime;
  if (maxTime > 0) {
    currentTime = 0;
    let hms = secondsTohhmmss(currentTime);
    $('.current-time').text(hms);
  }
  // timer update end
  window.clearInterval(intervalID);

  // avoid recorded audio truncated end by setting a timeout
  window.setTimeout(function() {

    recorder.stop();

    if (isDebug) {
      console.log(tempRecordedBlobs);
    }
    let options = isFirefox ? 'audio/ogg; codecs=opus' : 'audio/wav';

    let superBuffer = new Blob(tempRecordedBlobs, {
      'type': options
    });

    let audioObject = new Audio();
    audioObject.src = window.URL ? window.URL.createObjectURL(superBuffer) : superBuffer;
    audios.push(audioObject);
    aBlobs.push(superBuffer);
    let template =  `
                      <div class="recorded-row-content">
                        <div class="btn-group">
                          <button type="button" role="button" class="btn btn-default fa fa-play play"></button>
                          <button type="button" role="button" class="btn btn-default fa fa-stop stop"></button>
                          <button type="button" role="button" class="btn btn-danger fa fa-trash delete"></button>
                        </div>
                        <span class="recording-time"><small>(` + secondsTohhmmss(recLength) + `)</small></span>
                      </div>
                    `;

    $('#tr-preview-' + aid.toString()).find('td.actions').append(template);
    // enable select for the row.
    $('#tr-preview-' + aid.toString()).find('input').prop('disabled', false);
    aid++;
  }, recordEndTimeOut);
}

function audioSelected(elem) {
  $('#submitButton').prop('disabled', false);
}

function resetData() {

  cancelAnalyserUpdates();
  if (window.stream) {
    window.stream.getAudioTracks().forEach(function(track) {
      track.stop();
    });
  }
}

function playAudio(elem) {
  const index = $(elem).closest('.recorded-audio-row').attr('data-index');
  audios[index].play();
}

function stopAudio(elem) {
  const index = $(elem).closest('.recorded-audio-row').attr('data-index');
  audios[index].pause();
  audios[index].currentTime = 0;
}

function deleteAudio(elem) {
  const index = $(elem).closest('.recorded-audio-row').attr('data-index');
  audios.splice(index, 1);
  aBlobs.splice(index, 1);
  $('#tr-preview-' + index.toString()).find('.recorded-row-content').remove();
  $('#tr-preview-' + index.toString()).find('input').prop('disabled', true);
  //$('#recorded-audio-row-' + index.toString()).remove();
  const noAudioSelected = $('input:checked').length === 0;
  if (audios.length === 0 || noAudioSelected) {
    $('#submitButton').prop('disabled', true);
  }

  // rebuilt all rows
  let rowIndexToPopulate = 0;
  $('.recorded-audio-row').each(function(i) {
    // problème avec index = 0
    let $temp = $(this).find('.recorded-row-content').clone();
    $(this).find('.recorded-row-content').remove();
    $(this).find('input').prop('disabled', true);
    if($temp.length > 0){
      $('#tr-preview-' + rowIndexToPopulate.toString()).find('.actions').append($temp);
      $('#tr-preview-' + rowIndexToPopulate.toString()).find('input').prop('disabled', false);
      rowIndexToPopulate ++;
    }
  });

  aid = audios.length;

  nbTry--;
  if (nbTry < maxTry) {
    $('#audio-record-start').prop('disabled', false);
    $('.stop-recording-message').hide();
    $('.max-try-reached').hide();
    $('.max-time-reached').hide();
  }

}

function uploadAudio() {
  // get selected audio index
  let index = -1;
  index = $('input:checked').closest('.recorded-audio-row').attr('data-index');
  if (index > -1) {
    $('#submitButton').prop('disabled', true);
    let blob = aBlobs[index];
    let formData = new FormData();
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
    let fileName = $("#resource-name-input").val();
    formData.append('fileName', fileName);

    let route = $('#arForm').attr('action');
    xhr(route, formData, null, function(fileURL) {});
  }
}

function xhr(url, data, progress, callback) {

  const message = Translator.trans('creating_resource', {}, 'innova_audio_recorder');
  // tell the user that his action has been taken into account
  $('#form-content').hide();
  $('.progress-row').show();

  let request = new XMLHttpRequest();
  request.onreadystatechange = function() {
    if (request.readyState === 4 && request.status === 200) {
      if (isDebug) console.log('xhr end with success');
      resetData();

      // use reload or generate route...
      location.reload();

    } else if (request.status === 500) {
      if (isDebug) {
        console.log('xhr error');
        console.log(request);
      }
      $('.progress-row').hide();
      $('.error-row').show();
      $('#submitButton').prop('disabled', true);
    }
  };

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

function download() {
  const index = $('input:checked').closest('.recorded-audio-row').attr('data-index');
  let blob = aBlobs[index];
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
