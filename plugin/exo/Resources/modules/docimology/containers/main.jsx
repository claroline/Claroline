import {connect} from 'react-redux'

import {DocimologyMain as DocimologyMainComponent} from '#/plugin/exo/docimology/components/main'
import {selectors} from '#/plugin/exo/docimology/store/selectors'

const DocimologyMain = connect(
  (state) => ({
    statistics: selectors.docimology(state)
  })
)(DocimologyMainComponent)

export {
  DocimologyMain
}
