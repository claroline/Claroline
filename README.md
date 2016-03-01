# Innova\video-recorder-bundle

This Bundle is a plugin intended to be used with [Claroline Connect LMS](https://github.com/claroline/Claroline)

It allows the user to record video via an available video input device (such as a webcam and microphone) and create a *Claroline File* from the recorded video/audio blob.

You can choose if you want to convert the video file or keep native format.

## Requirements
This plugin uses
- [WebRTC / RecordRTC](https://www.webrtc-experiment.com/RecordRTC/)
- [libav-tools](https://libav.org/) to convert recorded blobs (audio + video) to webm format if Chrome is used 

## Installation

Install with composer : ```$ composer require innova/video-recorder-bundle```

## Limitations

- RecordRTC can record a single stream (video + audio into webm) at once only in Firefox
- On Chrome you need to activate the flag chrome://flags/#enable-experimental-web-platform-features activated
- Chrome get two streams. A video stream and an audio stream.
- According to [this demo](https://www.webrtc-experiment.com/RecordRTC/) It seems that it does not work on Chrome for now...
- Downloaded video has a chopped sound and can be properly readen in web browser only... see [this](https://github.com/muaz-khan/RecordRTC/issues/62)



## Authors

* Donovan Tengblad (purplefish32)
* Axel Penin (Elorfin)
* Arnaud Bey (arnaudbey)
* Eric Vincent (ericvincenterv)
* Nicolas Dufour (eldoniel)
* Patrick Guillou (pitrackster)

## Licence

MIT
