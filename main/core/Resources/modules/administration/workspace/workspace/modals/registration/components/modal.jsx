import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/core/translation'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {Modal} from '#/main/app/overlay/modal/components/modal'
import {FormData} from '#/main/app/content/form/containers/data'
import {selectors} from '#/main/core/administration/workspace/workspace/modals/registration/store/selectors'

const RoleRegistrationModal = props => {
  const choices = {}
  props.roles.forEach(role => {
    choices[role] = trans(role)
  })

  return (
    <Modal
      {...props}
      icon="fa fa-fw fa-cog"
      title={trans('roles')}
    >
      <FormData
        name={selectors.STORE_NAME}
        meta={true}
        sections={[
          {
            title: trans('roles'),
            primary: true,
            fields: [{
              name: 'role',
              type: 'choice',
              label: trans('role'),
              required: true,
              options: {
                multiple: false,
                condensed: false,
                choices
              }
            }]
          }
        ]}
      >
      </FormData>
      <Button
        className="modal-btn btn btn-primary"
        type={CALLBACK_BUTTON}
        primary={true}
        label={trans('save', {}, 'actions')}
        callback={() => {
          if (props.mode === 'user') {
            props.registerUsers(props.selectedRole, props.workspaces, props.objects)
          } else {

            props.registerGroups(props.selectedRole, props.workspaces, props.objects)
          }
          props.fadeModal()
        }}
      />
    </Modal>
  )
}

RoleRegistrationModal.propTypes = {
  fadeModal: T.func.isRequired,
  register:T.func.isRequired,
  roles: T.array.isRequired,
  selectedRole: T.string
}

export {
  RoleRegistrationModal
}
