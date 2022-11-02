import React, {Fragment} from 'react'
import isEmpty from 'lodash/isEmpty'

import {CALLBACK_BUTTON, LINK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {Button} from '#/main/app/action/components/button'
import {trans} from '#/main/app/intl/translation'
import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {DataInput as DataInputTypes} from '#/main/app/data/types/prop-types'
import {ContentPlaceholder} from '#/main/app/content/components/placeholder'

import {route} from '#/main/community/routing'
import {UserCard} from '#/main/core/user/components/card'
import {User as UserType} from '#/main/community/prop-types'
import {MODAL_USERS} from '#/main/community/modals/users'

const UsersButton = props =>
  <Button
    className="btn btn-block"
    style={{marginTop: 10}}
    type={MODAL_BUTTON}
    icon="fa fa-fw fa-plus"
    label={trans('add_users')}
    disabled={props.disabled}
    modal={[MODAL_USERS, {
      title: props.title,
      selectAction: (selected) => ({
        type: CALLBACK_BUTTON,
        label: trans('select', {}, 'actions'),
        callback: () => props.onChange(selected)
      })
    }]}
    size={props.size}
  />

UsersButton.propTypes = {
  title: T.string,
  disabled: T.bool,
  onChange: T.func.isRequired,
  size: T.string
}

const UsersInput = props => {
  if (!isEmpty(props.value)) {
    return(
      <Fragment>
        {props.value.map(user =>
          <UserCard
            key={`user-card-${user.id}`}
            data={user}
            size="xs"
            primaryAction={{
              type: LINK_BUTTON,
              label: trans('open', {}, 'actions'),
              target: route(user)
            }}
            actions={[
              {
                name: 'delete',
                type: CALLBACK_BUTTON,
                icon: 'fa fa-fw fa-trash-o',
                label: trans('delete', {}, 'actions'),
                dangerous: true,
                disabled: props.disabled,
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
          disabled={props.disabled}
          size={props.size}
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
      </Fragment>
    )
  }

  return (
    <ContentPlaceholder
      icon="fa fa-user"
      title={trans('no_user')}
      size={props.size}
    >
      <UsersButton
        {...props.picker}
        disabled={props.disabled}
        onChange={props.onChange}
        size={props.size}
      />
    </ContentPlaceholder>
  )
}

implementPropTypes(UsersInput, DataInputTypes, {
  value: T.arrayOf(T.shape(UserType.propTypes)),
  picker: T.shape({
    title: T.string
  })
}, {
  value: null
})

export {
  UsersInput
}
