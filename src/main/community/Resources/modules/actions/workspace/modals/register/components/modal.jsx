import React, {useState} from 'react'
import {PropTypes as T}  from 'prop-types'
import omit from 'lodash/omit'
import Tab from 'react-bootstrap/Tab'
import Tabs from 'react-bootstrap/Tabs'

import {url} from '#/main/app/api'
import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'
import {ASYNC_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {Modal} from '#/main/app/overlays/modal/components/modal'
import {Checkbox} from '#/main/app/input/components/checkbox'

import {selectors} from '#/main/community/actions/workspace/modals/register/store'
import {UserList}  from '#/main/community/user/components/list'
import {GroupList} from '#/main/community/group/components/list'
import {MODAL_ROLES} from '#/main/community/modals/roles'

const RegisterModal = props => {
  const [roleSelectionEnabled, setRoleSelectionEnabled] = useState(false)

  const registerAction = (selectedRoles = []) => {
    let selectedRole = null
    if (selectedRoles && 0 !== selectedRoles.length) {
      selectedRole = selectedRoles[0].translationKey
    }

    return ({
      type: ASYNC_BUTTON,
      label: trans('register', {}, 'actions'),
      request: {
        url: url(['apiv2_workspace_register', {role: selectedRole || ''}]),
        request: {
          method: 'PATCH',
          body: JSON.stringify({
            workspaces: props.workspaces.map(workspace => workspace.id),
            groups: props.selectedGroups.map(group => group.id),
            users: props.selectedUsers.map(user => user.id)
          })
        },
        success: () => props.onRegister(props.workspaces)
      }
    })
  }

  const registerWithRoleAction = () => ({
    label: trans('workspace_register_select_role'),
    type: MODAL_BUTTON,
    modal: [MODAL_ROLES, {
      title: trans('roles'),
      url: ['apiv2_workspace_list_roles', {id: props.workspaces[0].id}],
      filters: [],
      selectAction: registerAction
    }]
  })

  return (
    <Modal
      icon="fa fa-fw fa-user-plus"
      {...omit(props, 'selected', 'selectAction','selectedGroups', 'selectedUsers','resetGroups', 'resetUsers')}
      className="data-picker-modal"
      size="xl"
      onExited={() => {
        props.resetGroups()
        props.resetUsers()
      }}
    >
      <Tabs defaultActiveKey="users">
        <Tab eventKey="users" title={trans('users')}>
          <UserList
            name={selectors.STORE_NAME+'.users'}
            url={['apiv2_user_list']}
            primaryAction={undefined}
            actions={undefined}
          />
        </Tab>

        <Tab eventKey="groups" title={trans('groups')}>
          <GroupList
            name={selectors.STORE_NAME+'.groups'}
            url={['apiv2_group_list']}
            primaryAction={undefined}
            actions={undefined}
          />
        </Tab>
      </Tabs>

      <div className="modal-footer">
        <Checkbox
          id="select-role"
          label={trans('choose_role', {}, 'actions')}
          className="form-switch form-check-reverse"
          checked={roleSelectionEnabled}
          onChange={setRoleSelectionEnabled}
        />
      </div>

      <Button
        {...(roleSelectionEnabled ? registerWithRoleAction() : registerAction() )}
        className="modal-btn"
        variant="btn"
        size="lg"
        primary={true}
        disabled={props.selectedUsers.length === 0 && props.selectedGroups.length === 0}
        onClick={props.fadeModal}
      />
    </Modal>
  )
}

RegisterModal.propTypes = {
  title: T.string,
  workspaces: T.array.isRequired,
  selectedUsers: T.array.isRequired,
  selectedGroups: T.array.isRequired,
  resetGroups: T.func.isRequired,
  resetUsers: T.func.isRequired,
  fadeModal: T.func.isRequired,
  onRegister: T.func.isRequired
}

RegisterModal.defaultProps = {
  title: trans('user-groups')
}

export {
  RegisterModal
}
