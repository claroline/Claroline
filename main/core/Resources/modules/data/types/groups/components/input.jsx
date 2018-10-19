import React from 'react'
import isEmpty from 'lodash/isEmpty'

import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {ModalButton} from '#/main/app/buttons/modal/containers/button'

import {trans} from '#/main/app/intl/translation'
import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {FormField as FormFieldTypes} from '#/main/core/layout/form/prop-types'
import {EmptyPlaceholder} from '#/main/core/layout/components/placeholder'
import {GroupCard} from '#/main/core/user/data/components/group-card'
import {Group as GroupType} from '#/main/core/user/prop-types'
import {MODAL_GROUPS_PICKER} from '#/main/core/modals/groups'

const GroupsInput = props => {
  if (!isEmpty(props.value)) {
    return(
      <div>
        {props.value.map(group =>
          <GroupCard
            key={`group-card-${group.id}`}
            data={group}
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
                  const index = newValue.findIndex(g => g.id === group.id)

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
          className="btn btn-groups-primary"
          style={{marginTop: 10}}
          primary={true}
          modal={[MODAL_GROUPS_PICKER, {
            title: props.picker.title,
            confirmText: props.picker.confirmText,
            selectAction: (selected) => ({
              type: CALLBACK_BUTTON,
              callback: () => {
                const newValue = props.value
                selected.forEach(group => {
                  const index = newValue.findIndex(g => g.id === group.id)

                  if (-1 === index) {
                    newValue.push(group)
                  }
                })
                props.picker.handleSelect ? props.onChange(props.picker.handleSelect(newValue)) : props.onChange(newValue)
              }
            })
          }]}
        >
          <span className="fa fa-fw fa-users icon-with-text-right" />
          {trans('add_groups')}
        </ModalButton>
      </div>
    )
  } else {
    return (
      <EmptyPlaceholder
        size="lg"
        icon="fa fa-users"
        title={trans('no_group')}
      >
        <ModalButton
          className="btn btn-groups-primary"
          primary={true}
          modal={[MODAL_GROUPS_PICKER, {
            title: props.picker.title,
            confirmText: props.picker.confirmText,
            selectAction: (selected) => ({
              type: CALLBACK_BUTTON,
              callback: () => props.picker.handleSelect ? props.onChange(props.picker.handleSelect(selected)) : props.onChange(selected)
            })
          }]}
        >
          <span className="fa fa-fw fa-users icon-with-text-right" />
          {trans('add_groups')}
        </ModalButton>
      </EmptyPlaceholder>
    )
  }
}

implementPropTypes(GroupsInput, FormFieldTypes, {
  value: T.arrayOf(T.shape(GroupType.propTypes)),
  picker: T.shape({
    title: T.string,
    confirmText: T.string,
    handleSelect: T.func
  })
}, {
  value: null,
  picker: {
    title: trans('groups_picker'),
    confirmText: trans('select', {}, 'actions')
  }
})

export {
  GroupsInput
}
