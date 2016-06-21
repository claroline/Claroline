# Innova\audio-recorder-bundle

This Bundle is a plugin intended to be used with [Claroline Connect LMS](https://github.com/claroline/Claroline).

It allows the user to record audio via an available audio input device (such as a laptop microphone) and create a *Claroline File* from the recorded audio blob.


## Requirements

**This plugin uses**
- MediaRecorder API [Firefox](https://developer.mozilla.org/en-US/docs/Web/API/MediaRecorder_API) - [Chrome](https://developers.google.com/web/updates/2016/01/mediarecorder)
- [libav-tools](https://libav.org/) to convert audio to mp3 format


***WebBrowser minimal requirement***

- To use MediaRecorder in Chrome 47 and 48, enable experimental Web Platform features from the chrome://flags page.
- Audio recording work in Firefox and in Chrome 49 and above; Chrome 47 and 48 only support video recording.
- Everything work on Firefox 29 or later

## Installation

Install with composer :
```
$ composer require innova/audio-recorder-bundle
```

Install plugin :
```
$  php app/console claroline:plugin:install InnovaAudioRecorderBundle
```


## Limitations

- Works on Chrome and Firefox
- Firefox records stream in ogg while Chrome uses wav
- **Chrome needs an https connection to allow user media sharing!** See [this](https://sites.google.com/a/chromium.org/dev/Home/chromium-security/deprecating-powerful-features-on-insecure-origins) for more informations.


## Authors

* Donovan Tengblad (purplefish32)
* Axel Penin (Elorfin)
* Arnaud Bey (arnaudbey)
* Eric Vincent (ericvincenterv)
* Nicolas Dufour (eldoniel)
* Patrick Guillou (pitrackster)
* 

## Requests

Go to [Claroline](https://github.com/claroline/Claroline/issues) if you want to ask for new features.

Go to [Claroline Support](https://github.com/claroline/ClaroSupport/issues) if you encounter some bugs.

## Licence

MIT
