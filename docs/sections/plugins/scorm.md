---
layout: default
title: Scorm plugin
---

# Scorm plugin

The Scorm plugin allows to create Scorm resources in Claroline platform.
It supports Scorm 1.2 and Scorm 2004.

## Scorm API integration

The `APIClass` object exposed in [api.js](https://github.com/claroline/Claroline/blob/13.x/src/plugin/scorm/Resources/modules/resources/scorm/player/api.js) 
is responsible of the interactions with the scorm package.

The `APIClass` can be retrieve from the `window` object :

```js
window.API
window.api
window.API_1484_11
window.api_1484_11
```

### Available functions

Here is the list of exposed functions.

#### Scorm 1.2

```js
APIClass.LMSInitialize(arg)
APIClass.LMSFinish(arg)
APIClass.LMSGetValue(arg)
APIClass.LMSSetValue(argName, argValue) 
APIClass.LMSCommit(arg)
APIClass.LMSGetLastError()
APIClass.LMSGetErrorString(errorCode)
APIClass.LMSGetDiagnostic(errorCode)
```


#### Scorm 2004

```js
APIClass.Initialize(arg) 
APIClass.Terminate(arg)
APIClass.GetValue(arg)
APIClass.SetValue(argName, argValue)
APIClass.Commit(arg)
APIClass.GetLastError()
APIClass.GetErrorString(errorCode)
APIClass.GetDiagnostic(errorCode)
```
