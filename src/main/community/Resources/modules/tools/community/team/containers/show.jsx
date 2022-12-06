import {connect} from 'react-redux'

import {selectors as toolSelectors} from '#/main/core/tool/store'
import {selectors as formSelectors} from '#/main/app/content/form/store'

import {actions, selectors} from '#/main/community/tools/community/team/store'
import {TeamShow as TeamShowComponent} from '#/main/community/tools/community/team/components/show'

const TeamShow = connect(
  state => ({
    path: toolSelectors.path(state),
    contextData: toolSelectors.contextData(state),
    team: formSelectors.data(formSelectors.form(state, selectors.FORM_NAME))
  }),
  dispatch =>({
    reload(id) {
      dispatch(actions.open(id, true))
    },
    addUsers(teamId, selected) {
      dispatch(actions.addUsers(teamId, selected.map(row => row.id)))
    },
    addManagers(teamId, selected) {
      dispatch(actions.addManagers(teamId, selected.map(row => row.id)))
    }
  })
)(TeamShowComponent)

export {
  TeamShow
}
