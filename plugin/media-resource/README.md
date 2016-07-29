# MediaResourceBundle

This plug-in allow users to add metadata to an audio file.

Given an audio file users can define time based regions and for each region define :
- Text transcription
- Add help for oral comprehension of the audio document

Help available for each region are :
- Play the region in loop
- Play the region slowly
- Play the text transcription in backward building (Uses Text To Speech. so works well in chrome but need a flag to be enabled in Firefox. Also in Firefox Speech Synthesis is not as good as in Chrome)
- Up to 3 textual elements
- Up to 3 http links
- A related region (i.e. a region that can help to understand the current one)

This plug-in allow the user to export the resource in a zip file containing the original media, a `.vtt` file (for subtitles) based on region text transcription, and as much audio file as regions (i.e. each defined region is exported as an audio file).

User can also define a view among 4 views / play mode available :
- **Continuous pause** will play the whole file and automatically pause when entering a region
- **Continuous live** shows the document as a waveform, allow the user to listen freely to it and create its own region (one only) and so access to 3 helps :
    - Play the region
    - Play the region in loop
    - Play the region slowly
- **Continuous active** shows the document as a waveform, and provide all the settings and helps set by the author.
- **Free** is exactly the same as Continuous active except its behavior that is slightly different :
    - Shows (highlight on the waveform) the region when in pause
    - Shows some of the available helps (all except links and related region)
    - Can also show the textual transcription (if set so).
- **Scripted active** force the student to listent to the entire document. While listening the student can "mark" comprehension problems.
After this first step for each problem identified we show the corresponding regions available helps

## Requirements
- This bundle uses ffmpeg to export the project as mulitple audio files. So you'll need it on your server.
- WebSpeech API is also used for backward building help. This functionality will work out of the box on Chrome but need to be activated with Firefox.

## Javascript librairies
Intensive use of [wavesurfer.js] (http://www.wavesurfer.fm/)
