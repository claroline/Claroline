import React, {Fragment} from 'react'

import {CALLBACK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {Button} from '#/main/app/action/components/button'

import {trans} from '#/main/app/intl/translation'
import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {FormField as FormFieldTypes} from '#/main/core/layout/form/prop-types'
import {EmptyPlaceholder} from '#/main/core/layout/components/placeholder'
import {RoleCard} from '#/main/core/user/data/components/role-card'
import {Role as RoleType} from '#/main/core/user/prop-types'
import {MODAL_ROLES} from '#/main/core/modals/roles'

const RoleButton = props =>
  <Button
    className="btn"
    style={{marginTop: 10}}
    type={MODAL_BUTTON}
    icon="fa fa-fw fa-id-card"
    label={trans('add_roles')}
    primary={true}
    modal={[MODAL_ROLES, {
      url: ['apiv2_role_platform_loggable_list'],
      title: props.title,
      filters: props.filters,
      selectAction: (selected) => ({
        type: CALLBACK_BUTTON,
        label: trans('select', {}, 'actions'),
        callback: () => props.onChange(selected[0])
      })
    }]}
  />

RoleButton.propTypes = {
  title: T.string,
  filters: T.arrayOf(T.shape({
    // TODO : list filter types
  })),
  onChange: T.func.isRequired
}

const RoleInput = props => {
  const actions = props.disabled ? []: [
    {
      name: 'delete',
      type: CALLBACK_BUTTON,
      icon: 'fa fa-fw fa-trash-o',
      label: trans('delete', {}, 'actions'),
      dangerous: true,
      callback: () => props.onChange(null)
    }
  ]

  if (props.value) {
    return(
      <Fragment>
        <RoleCard
          data={props.value}
          size="xs"
          actions={actions}
        />

        {!props.disabled &&
          <RoleButton
            {...props.picker}
            onChange={props.onChange}
          />
        }
      </Fragment>
    )
  }

  return (
    <EmptyPlaceholder
      size="lg"
      icon="fa fa-id-card"
      title={trans('no_role')}
    >
      <RoleButton
        {...props.picker}
        onChange={props.onChange}
      />
    </EmptyPlaceholder>
  )
}

implementPropTypes(RoleInput, FormFieldTypes, {
  value: T.arrayOf(T.shape(RoleType.propTypes)),
  picker: T.shape({
    title: T.string,
    filters: T.arrayOf(T.shape({
      // TODO : list filter types
    }))
  })
}, {
  value: null
})

export {
  RoleInput
}
