import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/reducer'

import {IconCollection as IconCollectionComponent} from '#/main/theme/icon/components/collection'
import {actions, reducer, selectors} from '#/main/theme/icon/store'

const IconCollection = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      icons: selectors.icons(state)
    }),
    (dispatch) => ({
      load() {
        return dispatch(actions.fetchIconCollection())
      }
    })
  )(IconCollectionComponent)
)

export {
  IconCollection
}
