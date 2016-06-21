let _$http = new WeakMap()
let _$q = new WeakMap()
let _upload = new WeakMap()
let _utilities = new WeakMap()

export default class websiteOptions {
  constructor ($http, $q, upload, utilityFunctions, websiteData) {
    _$http.set(this, $http)
    _$q.set(this, $q)
    _upload.set(this, upload)
    _utilities.set(this, utilityFunctions)

    this.data = websiteData.options
    this.bannerEditorActive = false
    this.footerEditorActive = false
    this.isFullScreen = this.data.totalWidth == 0 || this.data.totalWidth == null
    this.bgImagePath = null
    this.bannerBgImagePath = null
    this.bgImagePath = null
    this.bannerBgImagePath = null
    this.footerBgImagePath = null
    this.bgImageChoice = 'upload'
    this.bannerBgImageChoice = 'upload'
    this.footerBgImageChoice = 'upload'
    this.bgImageRepeat = {x: false, y: false}
    this.bannerBgImageRepeat = {x: false, y: false}
    this.footerBgImageRepeat = {x: false, y: false}
    this.websiteId = websiteData.id
    this.basePath = websiteData.basePath

    if (this.data.analyticsProvider == null || this.data.analyticsProvider == '') this.data.analyticsProvider = 'none'
    if (this.isFullScreen) this.data.totalWidth = 800

    this._initializeRepeatVars('bg');
    this._initializeRepeatVars('bannerBg');
    this._initializeRepeatVars('footerBg');
  }

  save() {
    return _$http.get(this).put(window.Routing.generate('icap_website_options_update', {websiteId: this.websiteId}), this._jsonOptions())
      .then((response) => {
        if (typeof response.data === 'object') {
          return response.data;
        } else {
          return _$q.get(this).reject(response.data);
        }
      }, (response) => {
        return _$q.get(this).reject(response.data);
      });
  }

  getImageStyleText(imageStr) {
    if (_utilities.get(this).isNotBlank(this.data[imageStr])) {
      var imageURL = this.data[imageStr];
      if (!_utilities.get(this).validURL(imageURL)) {
        imageURL = this.basePath + imageURL;
      }
      return 'url("' + imageURL + '")';
    } else {
      return 'none';
    }
  }

  uploadImage($file, imageStr) {
    return _upload.get(this).upload({
      url: window.Routing.generate('icap_website_options_image_upload', {websiteId: this.websiteId, imageStr: imageStr}),
      method: 'POST',
      data: {'imageFile': $file}
    }).success(response => {
      this.data[imageStr] = response[imageStr];
      return response;
    }).error(response => {
      return _$q.get(this).reject(response);
    })
  }

  updateImagePath(newPath, imageStr) {
    return _$http.get(this).put(window.Routing.generate('icap_website_options_image_update', {
        websiteId: this.websiteId,
        imageStr: imageStr
      }), {"newPath": newPath})
      .then(response => {
        if (typeof response.data === 'object') {
          this.data[imageStr] = response.data[imageStr];
          return response;
        } else {
          return _$q.get(this).reject(response);
        }
      }, response => {
        return _$q.get(this).reject(response);
      })
  }

  toggleBold() {
    if (this.data.menuFontWeight == 'bold') this.data.menuFontWeight = 'normal'
    else this.data.menuFontWeight = 'bold'
  }

  toggleItalic() {
    if (this.data.menuFontStyle == 'italic') this.data.menuFontStyle = 'normal';
    else this.data.menuFontStyle = 'italic';
  }

  toggleFullScreen() {
    if (this.isFullScreen) this.isFullScreen = false;
    else this.isFullScreen = true;
  }

  _initializeRepeatVars (str) {
    let repeat = this.data[str + "Repeat"];

    if (repeat == "repeat") {
      this[str + "ImageRepeat"]["x"] = true;
      this[str + "ImageRepeat"]["y"] = true;
    } else if (repeat == "repeat-x") {
      this[str + "ImageRepeat"]["x"] = true;
      this[str + "ImageRepeat"]["y"] = false;
    } else if (repeat == "repeat-y") {
      this[str + "ImageRepeat"]["x"] = false;
      this[str + "ImageRepeat"]["y"] = true;
    }
  }

  repeatChanged(str) {
    let x = this[str + "ImageRepeat"]["x"];
    let y = this[str + "ImageRepeat"]["y"];

    if (x && y) {
      this.data[str + "Repeat"] = 'repeat';
    } else if (!x && !y) {
      this.data[str + "Repeat"] = 'no-repeat';
    } else if (x && !y) {
      this.data[str + "Repeat"] = 'repeat-x';
    } else if (!x && y) {
      this.data[str + "Repeat"] = 'repeat-y';
    }
  }

  isBgPositionButtonActive(bgStr, position) {
    let currentPosition = this.data[bgStr + 'Position'];
    let currentRepeat = this.data[bgStr + 'Repeat'];
    if (position == currentPosition || currentRepeat == 'repeat') return true;
    else if (currentRepeat == 'repeat-x') {
      if (position.split(" ")[1] == currentPosition.split(" ")[1]) return true;
    } else if (currentRepeat == 'repeat-y') {
      if (position.split(" ")[0] == currentPosition.split(" ")[0]) return true;
    }

    return false;
  }

  _jsonOptions () {
    let options = this._omitOptions(['bgImage', 'bannerBgImage', 'footerBgImage'])
    options.totalWidth = this.isFullScreen ? 0 : options.totalWidth

    return options
  }

  _omitOptions (props) {
    let result = Object.assign({}, this.data)
    for (let prop of props) {
      delete result[prop]
    }

    return result
  }
}

websiteOptions.$inject = [ '$http', '$q', 'Upload', 'utilityFunctions', 'website.data' ]
