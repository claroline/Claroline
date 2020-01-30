import React, {Fragment} from 'react'
import isEmpty from 'lodash/isEmpty'

import {CALLBACK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {Button} from '#/main/app/action/components/button'

import {trans} from '#/main/app/intl/translation'
import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {FormField as FormFieldTypes} from '#/main/core/layout/form/prop-types'
import {EmptyPlaceholder} from '#/main/core/layout/components/placeholder'
import {GroupCard} from '#/main/core/user/data/components/group-card'
import {Group as GroupType} from '#/main/core/user/prop-types'
import {MODAL_GROUPS} from '#/main/core/modals/groups'

const GroupsButton = props =>
  <Button
    className="btn btn-block"
    style={{marginTop: 10}}
    type={MODAL_BUTTON}
    icon="fa fa-fw fa-plus"
    label={trans('add_groups')}
    disabled={props.disabled}
    modal={[MODAL_GROUPS, {
      url: ['apiv2_group_list_registerable'],
      title: props.title,
      selectAction: (selected) => ({
        type: CALLBACK_BUTTON,
        label: trans('select', {}, 'actions'),
        callback: () => props.onChange(selected)
      })
    }]}
  />

GroupsButton.propTypes = {
  title: T.string,
  disabled: T.bool,
  onChange: T.func.isRequired
}

const GroupsInput = props => {
  if (!isEmpty(props.value)) {
    return(
      <Fragment>
        {props.value.map(group =>
          <GroupCard
            key={`group-card-${group.id}`}
            data={group}
            size="xs"
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
                  const index = newValue.findIndex(g => g.id === group.id)

                  if (-1 < index) {
                    newValue.splice(index, 1)
                    props.onChange(newValue)
                  }
                }
              }
            ]}
          />
        )}

        <GroupsButton
          {...props.picker}
          disabled={props.disabled}
          onChange={(selected) => {
            const newValue = props.value
            selected.forEach(group => {
              const index = newValue.findIndex(g => g.id === group.id)

              if (-1 === index) {
                newValue.push(group)
              }
            })
            props.onChange(newValue)
          }}
        />
      </Fragment>
    )
  }

  return (
    <EmptyPlaceholder
      icon="fa fa-users"
      title={trans('no_group')}
    >
      <GroupsButton
        {...props.picker}
        disabled={props.disabled}
        onChange={props.onChange}
      />
    </EmptyPlaceholder>
  )
}

implementPropTypes(GroupsInput, FormFieldTypes, {
  value: T.arrayOf(T.shape(
    GroupType.propTypes
  )),
  picker: T.shape({
    title: T.string
  })
}, {
  value: null
})

export {
  GroupsInput
}
