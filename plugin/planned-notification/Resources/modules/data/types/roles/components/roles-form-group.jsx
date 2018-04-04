import React from 'react'
import {connect} from 'react-redux'

import {PropTypes as T, implementPropTypes} from '#/main/core/scaffolding/prop-types'
import {actions as modalActions} from '#/main/core/layout/modal/actions'
import {MODAL_DATA_PICKER} from '#/main/core/data/list/modals'
import {trans} from '#/main/core/translation'
import {FormGroup as FormGroupWithFieldTypes} from '#/main/core/layout/form/prop-types'
import {FormGroup} from '#/main/core/layout/form/components/group/form-group.jsx'
import {TooltipButton} from '#/main/core/layout/button/components/tooltip-button.jsx'

import {select} from '#/plugin/planned-notification/tools/planned-notification/selectors'
import {Role as RoleTypes} from '#/plugin/planned-notification/data/types/roles/prop-types'

const Role = props =>
  <span className="role-item">
    {trans(props.role.translationKey)}
    {props.canEdit &&
      <TooltipButton
        id={`role-${props.role.id}-delete`}
        className="btn-link-danger"
        title={trans('delete')}
        onClick={props.onDelete}
      >
        <span className="fa fa-fw fa-trash-o" />
      </TooltipButton>
    }
  </span>

Role.propTypes = {
  role: T.shape(RoleTypes.propTypes).isRequired,
  canEdit: T.bool.isRequired,
  onDelete: T.func.isRequired
}

Role.defaultProps = {
  canEdit: false,
  onDelete: () => {}
}

const RolesFormGroupComponent = props =>
  <FormGroup
    {...props}
    error={props.error && typeof props.error === 'string' ? props.error : undefined}
    className="roles-form-group"
  >
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
      <button
        className="btn btn-block btn-default"
        type="button"
        onClick={() => props.pickRoles(props.workspace.uuid, props)}
      >
        <span className="fa fa-fw fa-plus icon-with-text-right" />
        {trans('add_roles')}
      </button>
    }
  </FormGroup>

implementPropTypes(RolesFormGroupComponent, FormGroupWithFieldTypes, {
  value: T.arrayOf(
    T.shape(RoleTypes.propTypes)
  ),
  workspace: T.shape({
    uuid: T.string.isRequired
  }).isRequired,
  disabled: T.bool.isRequired,
  pickRoles: T.func.isRequired
}, {
  value: []
})

const RolesFormGroup = connect(
  state => ({
    workspace: select.workspace(state)
  }),
  dispatch => ({
    pickRoles(worskpaceUuid, props) {
      dispatch(modalActions.showModal(MODAL_DATA_PICKER, {
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
          url: ['apiv2_plannednotification_workspace_roles_list', {workspace: worskpaceUuid}],
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
)(RolesFormGroupComponent)

export {
  RolesFormGroup
}