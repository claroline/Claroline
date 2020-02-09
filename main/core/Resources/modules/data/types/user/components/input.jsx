import React, {Fragment} from 'react'

import {CALLBACK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {Button} from '#/main/app/action/components/button'

import {trans} from '#/main/app/intl/translation'
import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {FormField as FormFieldTypes} from '#/main/core/layout/form/prop-types'
import {EmptyPlaceholder} from '#/main/core/layout/components/placeholder'
import {User as UserType} from '#/main/core/user/prop-types'
import {UserCard} from '#/main/core/user/components/card'
import {MODAL_USERS} from '#/main/core/modals/users'

const UserButton = props =>
  <Button
    className="btn btn-block"
    style={{marginTop: 10}}
    type={MODAL_BUTTON}
    icon="fa fa-fw fa-plus"
    label={trans('add_user')}
    disabled={props.disabled}
    modal={[MODAL_USERS, {
      title: props.title,
      selectAction: (selected) => ({
        type: CALLBACK_BUTTON,
        label: trans('select', {}, 'actions'),
        callback: () => props.onChange(selected[0])
      })
    }]}
  />

UserButton.propTypes = {
  title: T.string,
  disabled: T.bool,
  onChange: T.func.isRequired
}

const UserInput = props => {
  if (props.value) {
    return(
      <Fragment>
        <UserCard
          data={props.value}
          size="xs"
          actions={[
            {
              name: 'delete',
              type: CALLBACK_BUTTON,
              icon: 'fa fa-fw fa-trash-o',
              label: trans('delete', {}, 'actions'),
              dangerous: true,
              disabled: props.disabled,
              callback: () => props.onChange(null)
            }
          ]}
        />

        <UserButton
          {...props.picker}
          disabled={props.disabled}
          onChange={props.onChange}
        />
      </Fragment>
    )
  }

  return (
    <EmptyPlaceholder
      icon="fa fa-user"
      title={trans('no_user')}
    >
      <UserButton
        {...props.picker}
        disabled={props.disabled}
        onChange={props.onChange}
      />
    </EmptyPlaceholder>
  )
}

implementPropTypes(UserInput, FormFieldTypes, {
  value: T.shape(UserType.propTypes),
  picker: T.shape({
    title: T.string
  })
}, {
  value: null
})

export {
  UserInput
}
