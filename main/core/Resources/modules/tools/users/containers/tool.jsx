import {connect} from 'react-redux'

import {withRouter} from '#/main/app/router'
import {trans} from '#/main/app/intl/translation'
import {selectors as securitySelectors} from '#/main/app/security/store'
import {actions as modalActions} from '#/main/app/overlays/modal/store'
import {MODAL_DATA_LIST} from '#/main/app/modals/list'

import {selectors as toolSelectors} from '#/main/core/tool/store'
import {selectors} from '#/main/core/tools/users/store'
import {actions as userActions} from '#/main/core/tools/users/user/store'
import {actions as groupActions}   from '#/main/core/tools/users/group/store'
import {getModalDefinition} from '#/main/core/tools/users/role/modal'
import {UserList} from '#/main/core/administration/users/user/components/user-list'
import {GroupList} from '#/main/core/administration/users/group/components/group-list'
import {UsersTool as UsersToolComponent} from '#/main/core/tools/users/components/tool'

const UsersTool = withRouter(connect(
  (state) => ({
    currentUser: securitySelectors.currentUser(state),
    workspace: toolSelectors.contextData(state)
  }),
  (dispatch) => ({
    registerUsers(workspace) {
      dispatch(modalActions.showModal(MODAL_DATA_LIST, {
        icon: 'fa fa-fw fa-user',
        title: trans('register_users'),
        subtitle: trans('workspace_register_select_users'),
        confirmText: trans('select', {}, 'actions'),
        name: selectors.STORE_NAME + '.users.picker',
        definition: UserList.definition,
        card: UserList.card,
        fetch: {
          url: ['apiv2_user_list_registerable'],
          autoload: true
        },
        handleSelect: (users) => {
          dispatch(modalActions.showModal(MODAL_DATA_LIST, getModalDefinition(
            'fa fa-fw fa-user',
            trans('register_users'),
            workspace,
            (roles) => roles.forEach(role => dispatch(userActions.addUsersToRole(role, users)))
          )))
        }
      }))
    },
    registerGroups(workspace) {
      dispatch(modalActions.showModal(MODAL_DATA_LIST, {
        icon: 'fa fa-fw fa-users',
        title: trans('register_groups'),
        subtitle: trans('workspace_register_select_groups'),
        confirmText: trans('select', {}, 'actions'),
        name: selectors.STORE_NAME + '.groups.picker',
        definition: GroupList.definition,
        card: GroupList.card,
        fetch: {
          url: ['apiv2_group_list_registerable'],
          autoload: true
        },
        handleSelect: (groups) => {
          dispatch(modalActions.showModal(MODAL_DATA_LIST, getModalDefinition(
            'fa fa-fw fa-users',
            trans('register_groups'),
            workspace,
            (roles) => roles.forEach(role => dispatch(groupActions.addGroupsToRole(role, groups)))
          )))
        }
      }))
    }
  })
)(UsersToolComponent))

export {
  UsersTool
}
