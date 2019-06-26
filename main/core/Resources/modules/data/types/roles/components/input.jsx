import React, {Fragment} from 'react'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl/translation'
import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {CALLBACK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {Button} from '#/main/app/action/components/button'

import {FormField as FormFieldTypes} from '#/main/core/layout/form/prop-types'
import {Role as RoleType} from '#/main/core/user/prop-types'
import {MODAL_ROLES_PICKER} from '#/main/core/modals/roles'
import {RoleCard} from '#/main/core/user/data/components/role-card'
import {EmptyPlaceholder} from '#/main/core/layout/components/placeholder'

const RolesButton = props =>
  <Button
    className="btn"
    style={{marginTop: 10}}
    type={MODAL_BUTTON}
    icon="fa fa-fw fa-plus"
    label={trans('add_roles')}
    primary={true}
    modal={[MODAL_ROLES_PICKER, {
      url: ['apiv2_role_platform_loggable_list'], // maybe not the correct URL
      title: props.title,
      selectAction: (selected) => ({
        type: CALLBACK_BUTTON,
        label: trans('select', {}, 'actions'),
        callback: () => props.onChange(selected)
      })
    }]}
  />

RolesButton.propTypes = {
  title: T.string,
  onChange: T.func.isRequired
}

const RolesInput = props => {
  if (!isEmpty(props.value)) {
    return(
      <Fragment>
        {props.value.map(role =>
          <RoleCard
            key={`role-card-${role.id}`}
            data={role}
            actions={!props.disabled ? [
              {
                name: 'delete',
                type: CALLBACK_BUTTON,
                icon: 'fa fa-fw fa-trash-o',
                label: trans('delete', {}, 'actions'),
                dangerous: true,
                callback: () => {
                  const newValue = props.value
                  const index = newValue.findIndex(r => r.id === role.id)

                  if (-1 < index) {
                    newValue.splice(index, 1)
                    props.onChange(newValue)
                  }
                }
              }
            ] : []}
          />
        )}

        {!props.disabled &&
          <RolesButton
            {...props.picker}
            onChange={(selected) => {
              const newValue = props.value
              selected.forEach(role => {
                const index = newValue.findIndex(r => r.id === role.id)

                if (-1 === index) {
                  newValue.push(role)
                }
              })
              props.onChange(newValue)
            }}
          />
        }
      </Fragment>
    )
  } else {
    return (
      <EmptyPlaceholder
        size="lg"
        icon="fa fa-id-badge"
        title={trans('no_role')}
      >
        {!props.disabled &&
          <RolesButton
            {...props.picker}
            onChange={props.onChange}
          />
        }
      </EmptyPlaceholder>
    )
  }
}

implementPropTypes(RolesInput, FormFieldTypes, {
  value: T.arrayOf(T.shape(RoleType.propTypes)),
  picker: T.shape({
    title: T.string
  })
}, {
  value: null,
  picker: {
    title: trans('role_selector')
  }
})

export {
  RolesInput
}
