import React from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {Modal} from '#/main/app/overlay/modal/components/modal'

import {Role as RoleTypes} from '#/main/core/user/prop-types'
import {RoleCard} from '#/main/core/user/data/components/role-card'

// TODO : find a way to merge with other roles modals
// Used in register groups/users and impersonation actions

const RolesModal = props =>
  <Modal
    {...omit(props, 'roles', 'selectAction')}
  >
    <div className="modal-body">
      {props.roles.map(role =>
        <RoleCard
          key={role.name}
          className="component-container"
          data={role}
          primaryAction={props.selectAction(role)}
        />
      )}
    </div>
  </Modal>

RolesModal.propTypes = {
  icon: T.string,
  title: T.string.isRequired,
  subtitle: T.string,
  roles: T.arrayOf(T.shape(
    RoleTypes.propTypes
  )),
  // an action generator
  selectAction: T.func.isRequired,
  fadeModal: T.func.isRequired
}

RolesModal.defaultProps = {
  roles: []
}

export {
  RolesModal
}
