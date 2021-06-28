import {connect} from 'react-redux'

import {Docimology as DocimologyComponent} from '#/plugin/exo/resources/quiz/statistics/components/docimology'
import {selectors} from '#/plugin/exo/resources/quiz/statistics/store/selectors'

const Docimology = connect(
  (state) => ({
    statistics: selectors.docimology(state)
  })
)(DocimologyComponent)

export {
  Docimology
}
