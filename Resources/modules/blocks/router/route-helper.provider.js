/**
 * Created by ptsavdar on 15/03/16.
 */
export default class RouteHelperConfig {
  construct () {
    this.config = {}
  }

  $get () {
    return {
      config: this.config
    }
  }

  setConfig (config) {
    this.config = config
  }
}
