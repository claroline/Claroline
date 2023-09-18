import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import Tab from 'react-bootstrap/Tab'
import Tabs from 'react-bootstrap/Tabs'

import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'
import {Modal} from '#/main/app/overlays/modal/components/modal'

import {selectors} from '#/main/community/actions/workspace/modals/register/store'
import {UserList}  from '#/main/community/user/components/list'
import {GroupList} from '#/main/community/group/components/list'

const RegisterModal = props => {
  const selectAction = props.selectAction(props.selectedGroups, props.selectedUsers)
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

      <Button
        label={trans('save', {}, 'actions')}
        {...selectAction}
        className="modal-btn"
        variant="btn"
        size="lg"
        primary={true}
        disabled={0 === props.selectedUsers.length && 0 === props.selectedGroups}
        onClick={props.fadeModal}
      />
    </Modal>
  )
}

RegisterModal.propTypes = {
  selectedUsers: T.array.isRequired,
  selectedGroups: T.array.isRequired,
  selectAction: T.func.isRequired,
  resetGroups: T.func.isRequired,
  resetUsers: T.func.isRequired,
  fadeModal: T.func.isRequired
}

RegisterModal.defaultProps = {
  title: trans('user-groups')
}

export {
  RegisterModal
}
