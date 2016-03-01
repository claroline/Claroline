# Innova\video-recorder-bundle

This Bundle is a plugin intended to be used with [Claroline Connect LMS](https://github.com/claroline/Claroline)

It allows the user to record video via an available video input device (such as a webcam and microphone) and create a *Claroline File* from the recorded video/audio blob.

## Requirements
This plugin uses
- [WebRTC / RecordRTC](https://www.webrtc-experiment.com/RecordRTC/)
- [libav-tools](https://libav.org/) to convert recorded blobs (audio + video) to webm format if Chrome is used for recordings

## Installation

Install with composer : ```$ composer require innova/video-recorder-bundle```

Install plugin : ```$  php app/console claroline:plugin:install InnovaVideoRecorderBundle```

## Limitations

- RecordRTC can record a single stream (video + audio into webm) at once only in Firefox (audio and video are well synced)
- On Chrome **you need to activate the flag enable-experimental-web-platform-features** (chrome://flags/)
- Chrome get two streams. A video stream and an audio stream so we need to merge the two streams in one file.
- Firefox downloaded video has a chopped sound and **can be properly read in web browser only**... see [this](https://github.com/muaz-khan/RecordRTC/issues/62)
- Preview playback in Chrome is not accurately synced
- It seems that if firefox is opened with a RecordRTC instance running, it will not work in Chrome... And the opposite is true
- Recorded File size with Firefox can be big (10s ~= 3Mo)

## Authors

* Donovan Tengblad (purplefish32)
* Axel Penin (Elorfin)
* Arnaud Bey (arnaudbey)
* Eric Vincent (ericvincenterv)
* Nicolas Dufour (eldoniel)
* Patrick Guillou (pitrackster)

## Licence

MIT
