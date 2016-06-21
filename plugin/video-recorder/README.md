# Innova\video-recorder-bundle

This Bundle is a plugin intended to be used with [Claroline Connect LMS](https://github.com/claroline/Claroline)

It allows the user to record video via an available video input device (such as a webcam and microphone) and create a *Claroline File* from the recorded video/audio blob.

## Requirements

*This plugin uses*

- MediaRecorder API [Firefox infos](https://developer.mozilla.org/en-US/docs/Web/API/MediaRecorder_API) - [Chrome infos](https://developers.google.com/web/updates/2016/01/mediarecorder)
- [libav-tools](https://libav.org/) to (re)convert recorded blobs (audio + video) to WEBM [1]

>[1] In Claroline videos are served via a
```
Symfony HttpFoundation BinaryFileResponse
```
If we **do not** (re)encode the recorded source, video does not replay properly and video player time slider is ineffective...

*WebBrowser minimal requirement*

- To use MediaRecorder in Chrome 47 and 48, enable experimental Web Platform features from the chrome://flags page.
- Audio recording work in Firefox and in Chrome 49 and above; Chrome 47 and 48 only support video recording.
- Everything work on Firefox 29 or later

## Installation

Install with composer :

```
$ composer require innova/video-recorder-bundle
```

Install plugin :
```
$  php app/console claroline:plugin:install InnovaVideoRecorderBundle
```

## Limitations

- Firefox downloaded video has a chopped sound and can be properly read **in web browser only**
- Only one process/browser can access the camera/mic device at a time.

## Requests

Go to [Claroline](https://github.com/claroline/Claroline/issues) if you want to ask for new features.

Go to [Claroline Support](https://github.com/claroline/ClaroSupport/issues) if you encounter some bugs.

## Authors

* Donovan Tengblad (purplefish32)
* Axel Penin (Elorfin)
* Arnaud Bey (arnaudbey)
* Eric Vincent (ericvincenterv)
* Nicolas Dufour (eldoniel)
* Patrick Guillou (pitrackster)

## Licence

MIT
