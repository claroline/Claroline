import React from 'react'
import {connect} from 'react-redux'

import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {actions as modalActions} from '#/main/app/overlay/modal/store'
import {MODAL_DATA_LIST} from '#/main/app/modals/list'
import {trans} from '#/main/app/intl/translation'
import {FormField as FormFieldTypes} from '#/main/core/layout/form/prop-types'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON} from '#/main/app/buttons'

import {select} from '#/plugin/planned-notification/tools/planned-notification/selectors'
import {Role as RoleType} from '#/plugin/planned-notification/data/types/roles/prop-types'

const Role = props =>
  <span className="role-item">
    {trans(props.role.translationKey)}
    {props.canEdit &&
      <Button
        id={`role-${props.role.id}-delete`}
        className="btn-link"
        icon="fa fa-fw fa-trash-o"
        label={trans('delete', {}, 'actions')}
        tooltip="left"
        callback={props.onDelete}
        dangerous={true}
      />
    }
  </span>

Role.propTypes = {
  role: T.shape(RoleType.propTypes).isRequired,
  canEdit: T.bool.isRequired,
  onDelete: T.func.isRequired
}

Role.defaultProps = {
  canEdit: false,
  onDelete: () => {}
}

const RolesInputComponent = props =>
  <div className="roles-form-group">
    {props.value.length > 0 ?
      <div className="roles-form-list">
        {props.value.map((role, index) =>
          <Role
            key={`role-${index}`}
            role={role}
            canEdit={!props.disabled}
            onDelete={() => {
              const newRoles = props.value.slice()
              newRoles.splice(index, 1)

              props.onChange(newRoles)
            }}
          />
        )}
      </div> :
      <div className="alert alert-warning">
        {trans('no_role')}
      </div>
    }

    {!props.disabled &&
      <Button
        className="btn btn-block"
        type={CALLBACK_BUTTON}
        icon="fa fa-fw fa-plus"
        label={trans('add_roles')}
        callback={() => props.pickRoles(props.workspace.uuid, props)}
      />
    }
  </div>

implementPropTypes(RolesInputComponent, FormFieldTypes, {
  value: T.arrayOf(
    T.shape(RoleType.propTypes)
  ),
  workspace: T.shape({
    uuid: T.string.isRequired
  }).isRequired,
  disabled: T.bool.isRequired,
  pickRoles: T.func.isRequired
}, {
  value: []
})

const RolesInput = connect(
  state => ({
    workspace: select.workspace(state)
  }),
  dispatch => ({
    pickRoles(workspaceUuid, props) {
      dispatch(modalActions.showModal(MODAL_DATA_LIST, {
        icon: 'fa fa-fw fa-id-badge',
        title: trans('select_roles', {}, 'planned_notification'),
        confirmText: trans('select', {}, 'planned_notification'),
        name: 'notifications.rolesPicker',
        onlyId: false,
        definition: [
          {
            name: 'translationKey',
            type: 'string',
            label: trans('role'),
            displayed: true,
            primary: true
          }
        ],
        fetch: {
          url: ['apiv2_plannednotification_workspace_roles_list', {workspace: workspaceUuid}],
          autoload: true
        },
        handleSelect: (selected) => {
          const newRoles = props.value.slice()
          selected.forEach(role => {
            const existingRole = newRoles.find(nr => nr.id === role.id)

            if (!existingRole) {
              newRoles.push(role)
            }
          })
          props.onChange(newRoles)
        }
      }))
    }
  })
)(RolesInputComponent)

export {
  RolesInput
}