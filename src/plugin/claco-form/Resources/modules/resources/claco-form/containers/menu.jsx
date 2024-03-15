import {connect} from 'react-redux'

import {withRouter} from '#/main/app/router'

import {ClacoFormMenu as ClacoFormMenuComponent} from '#/plugin/claco-form/resources/claco-form/components/menu'
import {selectors} from '#/plugin/claco-form/resources/claco-form/store'

const ClacoFormMenu = /*withRouter(*/
  connect(
    (state) => ({
      canSearchEntry: selectors.canSearchEntry(state),
      randomEnabled: selectors.clacoForm(state).random.enabled
      //editable: hasPermission('edit', resourceSelectors.resourceNode(state))
    })
  )(ClacoFormMenuComponent)
/*)*/

export {
  ClacoFormMenu
}
