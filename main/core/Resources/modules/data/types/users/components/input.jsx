import React from 'react'
import isEmpty from 'lodash/isEmpty'

import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {ModalButton} from '#/main/app/buttons/modal/containers/button'

import {trans} from '#/main/core/translation'
import {PropTypes as T, implementPropTypes} from '#/main/core/scaffolding/prop-types'
import {FormField as FormFieldTypes} from '#/main/core/layout/form/prop-types'
import {EmptyPlaceholder} from '#/main/core/layout/components/placeholder'
import {UserCard} from '#/main/core/user/data/components/user-card'
import {User as UserType} from '#/main/core/user/prop-types'
import {MODAL_USERS_PICKER} from '#/main/core/modals/users'

const UsersInput = props => {
  if (!isEmpty(props.value)) {
    return(
      <div>
        {props.value.map(user =>
          <UserCard
            key={`user-card-${user.id}`}
            data={user}
            size="sm"
            orientation="col"
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
                    props.picker.handleSelect ? props.onChange(props.picker.handleSelect(newValue)) : props.onChange(newValue)
                  }
                }
              }
            ]}
          />
        )}
        <ModalButton
          className="btn btn-users-primary"
          style={{marginTop: 10}}
          primary={true}
          modal={[MODAL_USERS_PICKER, {
            title: props.picker.title,
            confirmText: props.picker.confirmText,
            selectAction: (selected) => ({
              type: CALLBACK_BUTTON,
              callback: () => {
                const newValue = props.value
                selected.forEach(user => {
                  const index = newValue.findIndex(u => u.id === user.id)

                  if (-1 === index) {
                    newValue.push(user)
                  }
                })
                props.picker.handleSelect ? props.onChange(props.picker.handleSelect(newValue)) : props.onChange(newValue)
              }
            })
          }]}
        >
          <span className="fa fa-fw fa-user-plus icon-with-text-right" />
          {trans('add_users')}
        </ModalButton>
      </div>
    )
  } else {
    return (
      <EmptyPlaceholder
        size="lg"
        icon="fa fa-user"
        title={trans('no_user')}
      >
        <ModalButton
          className="btn btn-users-primary"
          primary={true}
          modal={[MODAL_USERS_PICKER, {
            title: props.picker.title,
            confirmText: props.picker.confirmText,
            selectAction: (selected) => ({
              type: CALLBACK_BUTTON,
              callback: () => props.picker.handleSelect ? props.onChange(props.picker.handleSelect(selected)) : props.onChange(selected)
            })
          }]}
        >
          <span className="fa fa-fw fa-user-plus icon-with-text-right" />
          {trans('add_users')}
        </ModalButton>
      </EmptyPlaceholder>
    )
  }
}

implementPropTypes(UsersInput, FormFieldTypes, {
  value: T.arrayOf(T.shape(UserType.propTypes)),
  picker: T.shape({
    title: T.string,
    confirmText: T.string,
    handleSelect: T.func
  })
}, {
  value: null,
  picker: {
    title: trans('user_selector'),
    confirmText: trans('select', {}, 'actions')
  }
})

export {
  UsersInput
}
