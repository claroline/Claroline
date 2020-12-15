import {connect} from 'react-redux'

import {makeId} from '#/main/core/scaffolding/id'
import {selectors as toolSelectors} from '#/main/core/tool/store'

import {IconSet as IconSetType} from '#/main/theme/administration/appearance/icon/prop-types'
import {IconMain as IconMainComponent} from '#/main/theme/administration/appearance/icon/components/main'
import {actions, selectors} from '#/main/theme/administration/appearance/icon/store'

const IconMain = connect(
  (state) => ({
    path: toolSelectors.path(state)
  }),
  (dispatch) => ({
    openIconSetForm(id = null) {
      const defaultProps = Object.assign({}, IconSetType.defaultProps, {
        id: makeId()
      })
      dispatch(actions.openIconSetForm(selectors.STORE_NAME+'.current', defaultProps, id))
    },
    resetIconSetForm() {
      dispatch(actions.resetForm(selectors.STORE_NAME+'.current'))
    }
  })
)(IconMainComponent)

export {
  IconMain
}
