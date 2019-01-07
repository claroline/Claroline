import React from 'react'
import isEmpty from 'lodash/isEmpty'

import {CALLBACK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {Button} from '#/main/app/action/components/button'

import {trans} from '#/main/app/intl/translation'
import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {FormField as FormFieldTypes} from '#/main/core/layout/form/prop-types'
import {EmptyPlaceholder} from '#/main/core/layout/components/placeholder'
import {UserCard} from '#/main/core/user/data/components/user-card'
import {User as UserType} from '#/main/core/user/prop-types'
import {MODAL_USERS_PICKER} from '#/main/core/modals/users'

const UsersButton = props =>
  <Button
    className="btn"
    style={{marginTop: 10}}
    type={MODAL_BUTTON}
    icon="fa fa-fw fa-user-plus"
    label={trans('add_users')}
    primary={true}
    modal={[MODAL_USERS_PICKER, {
      url: ['apiv2_user_list_registerable'], // maybe not the correct URL
      title: props.title,
      selectAction: (selected) => ({
        type: CALLBACK_BUTTON,
        label: trans('select', {}, 'actions'),
        callback: () => props.onChange(selected)
      })
    }]}
  />

UsersButton.propTypes = {
  title: T.string,
  onChange: T.func.isRequired
}

const UsersInput = props => {
  if (!isEmpty(props.value)) {
    return(
      <div>
        {props.value.map(user =>
          <UserCard
            key={`user-card-${user.id}`}
            data={user}
            actions={[
              {
                name: 'delete',
                type: CALLBACK_BUTTON,
                icon: 'fa fa-fw fa-trash-o',
                label: trans('delete', {}, 'actions'),
                dangerous: true,
                callback: () => {
                  const newValue = props.value
                  const index = newValue.findIndex(u => u.id === user.id)

                  if (-1 < index) {
                    newValue.splice(index, 1)
                    props.onChange(newValue)
                  }
                }
              }
            ]}
          />
        )}

        <UsersButton
          {...props.picker}
          onChange={(selected) => {
            const newValue = props.value
            selected.forEach(user => {
              const index = newValue.findIndex(u => u.id === user.id)

              if (-1 === index) {
                newValue.push(user)
              }
            })
            props.onChange(newValue)
          }}
        />
      </div>
    )
  } else {
    return (
      <EmptyPlaceholder
        size="lg"
        icon="fa fa-user"
        title={trans('no_user')}
      >
        <UsersButton
          {...props.picker}
          onChange={props.onChange}
        />
      </EmptyPlaceholder>
    )
  }
}

implementPropTypes(UsersInput, FormFieldTypes, {
  value: T.arrayOf(T.shape(UserType.propTypes)),
  picker: T.shape({
    title: T.string
  })
}, {
  value: null,
  picker: {
    title: trans('user_selector')
  }
})

export {
  UsersInput
}
