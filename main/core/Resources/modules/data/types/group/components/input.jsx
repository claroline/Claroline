import React, {Fragment} from 'react'

import {CALLBACK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {Button} from '#/main/app/action/components/button'

import {trans} from '#/main/app/intl/translation'
import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {FormField as FormFieldTypes} from '#/main/core/layout/form/prop-types'
import {EmptyPlaceholder} from '#/main/core/layout/components/placeholder'
import {GroupCard} from '#/main/core/user/data/components/group-card'
import {Group as GroupType} from '#/main/core/user/prop-types'
import {MODAL_GROUPS} from '#/main/core/modals/groups'

const GroupButton = props =>
  <Button
    className="btn"
    style={{marginTop: 10}}
    type={MODAL_BUTTON}
    icon="fa fa-fw fa-users"
    label={trans('add_groups')}
    primary={true}
    disabled={props.disabled}
    modal={[MODAL_GROUPS, {
      url: ['apiv2_group_list_registerable'],
      title: props.title,
      selectAction: (selected) => ({
        type: CALLBACK_BUTTON,
        label: trans('select', {}, 'actions'),
        callback: () => props.onChange(selected[0])
      })
    }]}
  />

GroupButton.propTypes = {
  title: T.string,
  disabled: T.bool,
  onChange: T.func.isRequired
}

const GroupInput = props => {
  if (props.value) {
    return (
      <Fragment>
        <GroupCard
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

        <GroupButton
          {...props.picker}
          disabled={props.disabled}
          onChange={props.onChange}
        />
      </Fragment>
    )
  }

  return (
    <EmptyPlaceholder
      size="lg"
      icon="fa fa-users"
      title={trans('no_group')}
    >
      <GroupButton
        {...props.picker}
        disabled={props.disabled}
        onChange={props.onChange}
      />
    </EmptyPlaceholder>
  )
}

implementPropTypes(GroupInput, FormFieldTypes, {
  value: T.shape(
    GroupType.propTypes
  ),
  picker: T.shape({
    title: T.string
  })
}, {
  value: null
})

export {
  GroupInput
}
