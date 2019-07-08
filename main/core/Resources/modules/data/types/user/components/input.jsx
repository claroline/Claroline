import React from 'react'

import {CALLBACK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {Button} from '#/main/app/action/components/button'

import {trans} from '#/main/app/intl/translation'
import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {FormField as FormFieldTypes} from '#/main/core/layout/form/prop-types'
import {EmptyPlaceholder} from '#/main/core/layout/components/placeholder'
import {User as UserType} from '#/main/core/user/prop-types'
import {UserCard} from '#/main/core/user/data/components/user-card'
import {MODAL_USERS} from '#/main/core/modals/users'

//todo: implement badge picker
const UserButton = props =>
  <Button
    className="btn"
    style={{marginTop: 10}}
    type={MODAL_BUTTON}
    icon="fa fa-fw fa-user"
    label={trans('select_a_user')}
    primary={true}
    modal={[MODAL_USERS, {
      url: ['apiv2_user_list_registerable'], // maybe not the correct URL
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
  onChange: T.func.isRequired
}

const UserInput = props => {
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
      <div>
        <UserCard
          data={props.value}
          size="sm"
          orientation="col"
          actions={actions}
        />

        {!props.disabled &&
          <UserButton
            {...props.picker}
            onChange={props.onChange}
          />
        }
      </div>
    )
  } else {
    return (
      <EmptyPlaceholder
        size="lg"
        icon="fa fa-user"
        title={trans('no_user')}
      >
        {!props.disabled &&
          <UserButton
            {...props.picker}
            onChange={props.onChange}
          />
        }
      </EmptyPlaceholder>
    )
  }
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
