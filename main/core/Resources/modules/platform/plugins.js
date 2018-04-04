
const availablePlugins = []
const enabledPlugins = []

function plugins(onlyEnabled = true) {
  if (onlyEnabled) {
    return enabledPlugins
  }

  return availablePlugins
}

function plugin(name) {

}

function pluginEnabled(name) {

}

export {
  plugins,
  plugin,
  pluginEnabled
}
