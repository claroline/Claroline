let _tinymce = new WeakMap()

export default class tinyMceConfig {
  constructor() {
    _tinymce.set(this, window.tinymce)
    _tinymce.get(this).claroline.init = _tinymce.get(this).claroline.init || {}
    _tinymce.get(this).claroline.plugins = _tinymce.get(this).claroline.plugins || {}

    this._setFromTinymceConfiguration(_tinymce.get(this).claroline.configuration)
    this._setPluginsToolbarAndFormat()
    this._configurationOverrides()
    this._hacks()
  }

  _setPluginsToolbarAndFormat() {
    let plugins = [
      'autoresize advlist autolink lists link image charmap print preview hr anchor pagebreak',
      'searchreplace wordcount visualblocks visualchars fullscreen',
      'insertdatetime media nonbreaking save table directionality',
      'template paste textcolor emoticons code -accordion -codemirror'
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

  _setFromTinymceConfiguration(config) {
    for (let prop of Object.getOwnPropertyNames(config)) {
      this[ prop ] = config[ prop ]
    }
  }

  _configurationOverrides() {
    this.autoresize_max_height = false
  }

  _hacks() {
    // Since v4, tinyMce's textcolor plugin doesn't fire an ExecCommand anymore when changing text color
    // The ExecCommand is fired manually in order to be catchable by angular-ui-tinymce and
    // ensure the model is updated right after color change
    this.setup = function (ed) {
      ed.on('init', function () {
        let oldApply = ed.formatter.apply
        ed.formatter.apply = function apply(name, vars, node) {
          oldApply(name, vars, node)
          ed.fire('ExecCommand', {name: name, vars: vars})
        }
      })
    }
  }
}
