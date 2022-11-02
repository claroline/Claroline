import React, {Fragment} from 'react'

import {CALLBACK_BUTTON, LINK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {Button} from '#/main/app/action/components/button'
import {trans} from '#/main/app/intl/translation'
import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {DataInput as DataInputTypes} from '#/main/app/data/types/prop-types'
import {ContentPlaceholder} from '#/main/app/content/components/placeholder'

import {route} from '#/main/community/routing'
import {User as UserType} from '#/main/community/prop-types'
import {UserCard} from '#/main/core/user/components/card'
import {MODAL_USERS} from '#/main/community/modals/users'

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
    size={props.size}
  />

UserButton.propTypes = {
  title: T.string,
  disabled: T.bool,
  onChange: T.func.isRequired,
  size: T.string
}

const UserInput = props => {
  if (props.value) {
    return(
      <Fragment>
        <UserCard
          data={props.value}
          size="xs"
          primaryAction={{
            type: LINK_BUTTON,
            label: trans('open', {}, 'actions'),
            target: route(props.value)
          }}
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
          size={props.size}
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
      <UserButton
        {...props.picker}
        disabled={props.disabled}
        onChange={props.onChange}
        size={props.size}
      />
    </ContentPlaceholder>
  )
}

implementPropTypes(UserInput, DataInputTypes, {
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
