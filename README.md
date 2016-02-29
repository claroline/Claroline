# Innova\video-recorder-bundle

This Bundle is a plugin intended to be used with [Claroline Connect LMS](https://github.com/claroline/Claroline)

It allows the user to record video via an available video input device (such as a webcam and microphone) and create a *Claroline File* from the recorded video/audio blob.

You can choose if you want to convert the video file or keep native format.

## Requirements
This plugin uses
- [WebRTC / RecordRTC](https://www.webrtc-experiment.com/RecordRTC/)
- [libav-tools](https://libav.org/)

## Installation

Install with composer : ```$ composer require innova/video-recorder-bundle```

## Limitations

Works on Chrome and Firefox

**Chrome needs an https connection to allow user media sharing!** See [this](https://sites.google.com/a/chromium.org/dev/Home/chromium-security/deprecating-powerful-features-on-insecure-origins) for more informations.


## Authors

* Donovan Tengblad (purplefish32)
* Axel Penin (Elorfin)
* Arnaud Bey (arnaudbey)
* Eric Vincent (ericvincenterv)
* Nicolas Dufour (eldoniel)
* Patrick Guillou (pitrackster)

## Licence

MIT
