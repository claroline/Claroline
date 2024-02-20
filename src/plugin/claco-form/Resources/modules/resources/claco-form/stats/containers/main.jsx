import {connect} from 'react-redux'

import {StatsMain as StatsMainComponent} from '#/plugin/claco-form//resources/claco-form/stats/components/main'
import {selectors} from '#/plugin/claco-form//resources/claco-form/stats/store'

const StatsMain = connect(
  (state) => ({
    stats: selectors.stats(state)
  })
)(StatsMainComponent)

export {
  StatsMain
}
