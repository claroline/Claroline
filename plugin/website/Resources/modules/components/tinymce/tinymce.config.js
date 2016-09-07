let _tinymce = new WeakMap()
export default class tinyMceConfig {
  constructor () {
    _tinymce.set(this, window.tinymce)
    _tinymce.get(this).claroline.init = _tinymce.get(this).claroline.init || {}
    _tinymce.get(this).claroline.plugins = _tinymce.get(this).claroline.plugins || {}
    this._setFromTinymceConfiguration()
    this._setPluginsToolbarAndFormat()
    this.autosave_ask_before_unload = false
    // Avoid having the "leave this page" confirmation everytime page is loaded
    this.setup = () => {
    }
  }

  _setPluginsToolbarAndFormat () {
    let plugins = [
      'autoresize advlist autolink lists link image charmap print preview hr anchor pagebreak',
      'searchreplace wordcount visualblocks visualchars fullscreen',
      'insertdatetime media nonbreaking save table directionality',
      'template paste textcolor emoticons code -accordion -mention -codemirror'
    ]

    let toolbar1 = 'bold italic underline strikethrough | alignleft aligncenter alignright alignjustify | fullscreen displayAllButtons'

    let tinyPlugins = _tinymce.get(this).claroline.plugins
    Object.keys(tinyPlugins).forEach((key) => {
      let value = tinyPlugins[key]
      if ('autosave' != key && value === true) {
        plugins.push(key)
        toolbar1 += ' ' + key
      }
    })

    this.plugins = plugins
    this.toolbar1 = toolbar1
  }

  _setFromTinymceConfiguration () {
    let config = _tinymce.get(this).claroline.configuration
    for (let prop of Object.getOwnPropertyNames(config)) {
      this[ prop ] = config[ prop ]
    }
  }
}
