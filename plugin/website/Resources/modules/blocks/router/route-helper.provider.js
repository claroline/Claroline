/**
 * Created by ptsavdar on 15/03/16.
 */
let _config = new WeakMap()

export default class RouteHelperConfig {
  constructor () {
    _config.set(this, {})
  }

  $get () {
    return this.config
  }

  get config () {
    return {
      config: _config.get(this)
    }
  }

  set config (config) {
    _config.set(this, config)
  }
}
