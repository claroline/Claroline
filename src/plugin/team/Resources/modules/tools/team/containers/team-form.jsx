import {connect} from 'react-redux'

import {trans} from '#/main/app/intl/translation'
import {actions as modalActions} from '#/main/app/overlays/modal/store'
import {actions as formActions, selectors as formSelectors} from '#/main/app/content/form/store'
import {MODAL_DATA_LIST} from '#/main/app/modals/list'

import {selectors as toolSelectors} from '#/main/core/tool/store'
import {UserList} from '#/main/core/administration/community/user/components/user-list'

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
    pickUsers(teamId, workspaceId, pickManagers = false) {
      dispatch(modalActions.showModal(MODAL_DATA_LIST, {
        icon: 'fa fa-fw fa-user',
        title: pickManagers ? trans('add_managers', {}, 'team') : trans('add_members', {}, 'team'),
        confirmText: trans('add', {}, 'actions'),
        name: selectors.STORE_NAME + '.teams.current.usersPicker',
        definition: UserList.definition,
        card: UserList.card,
        fetch: {
          url: ['apiv2_workspace_list_users', {id: workspaceId}],
          autoload: true
        },
        handleSelect: (selected) => dispatch(actions.registerUsers(teamId, selected, pickManagers ? 'manager' : 'user'))
      }))
    }
  })
)(TeamFormComponent)

export {
  TeamForm
}
