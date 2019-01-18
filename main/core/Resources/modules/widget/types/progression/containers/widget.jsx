import {connect} from 'react-redux'

import {withReducer} from '#/main/app/store/components/withReducer'

import {selectors as contentSelectors} from '#/main/core/widget/content/store'
import {actions, reducer, selectors} from '#/main/core/widget/types/progression/store'
import {ProgressionWidget as ProgressionWidgetComponent} from '#/main/core/widget/types/progression/components/widget'

const ProgressionWidget = withReducer(selectors.STORE_NAME, reducer)(
  connect(
    (state) => ({
      currentContext: contentSelectors.context(state),
      levelMax: contentSelectors.parameters(state).levelMax,
      items: selectors.items(state)
    }),
    (dispatch) => ({
      loadItems(workspaceId, levelMax = null) {
        dispatch(actions.loadProgressionItems(workspaceId, levelMax))
      }
    })
  )(ProgressionWidgetComponent)
)

export {
  ProgressionWidget
}
