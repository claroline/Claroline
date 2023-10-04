import React from 'react'
import {PropTypes as T} from 'prop-types'
import merge from 'lodash/merge'
import omit from 'lodash/omit'

import {Modal} from '#/main/app/overlays/modal/components/modal'

import {Role as RoleTypes} from '#/main/community/prop-types'
import {RoleCard} from '#/main/community/role/components/card'

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
          primaryAction={merge({}, props.selectAction(role), {
            onClick: props.fadeModal
          })}
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
