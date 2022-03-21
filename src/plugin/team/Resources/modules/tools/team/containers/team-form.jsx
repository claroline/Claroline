import {connect} from 'react-redux'

import {actions as formActions, selectors as formSelectors} from '#/main/app/content/form/store'
import {selectors as toolSelectors} from '#/main/core/tool/store'

import {actions, selectors} from '#/plugin/team/tools/team/store'
import {TeamForm as TeamFormComponent} from '#/plugin/team/tools/team/components/team-form'

const TeamForm = connect(
  (state) => ({
    path: toolSelectors.path(state),
    workspace: toolSelectors.contextData(state),
    team: formSelectors.data(formSelectors.form(state, selectors.STORE_NAME + '.teams.current')),
    isNew: formSelectors.isNew(formSelectors.form(state, selectors.STORE_NAME + '.teams.current')),
    resourceTypes: selectors.resourceTypes(state)
  }),
  (dispatch) => ({
    update(prop, value) {
      dispatch(formActions.updateProp(selectors.STORE_NAME + '.teams.current', prop, value))
    },
    addUsers(teamId, selected, pickManagers = false) {
      dispatch(actions.registerUsers(teamId, selected, pickManagers ? 'manager' : 'user'))
    }
  })
)(TeamFormComponent)

export {
  TeamForm
}
