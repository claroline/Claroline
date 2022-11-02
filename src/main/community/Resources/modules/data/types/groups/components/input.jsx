import React, {Fragment} from 'react'
import isEmpty from 'lodash/isEmpty'

import {CALLBACK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {Button} from '#/main/app/action/components/button'

import {trans} from '#/main/app/intl/translation'
import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {DataInput as DataInputTypes} from '#/main/app/data/types/prop-types'
import {ContentPlaceholder} from '#/main/app/content/components/placeholder'
import {GroupCard} from '#/main/core/user/data/components/group-card'
import {Group as GroupType} from '#/main/community/prop-types'
import {MODAL_GROUPS} from '#/main/community/modals/groups'

const GroupsButton = props =>
  <Button
    className="btn btn-block"
    style={{marginTop: 10}}
    type={MODAL_BUTTON}
    icon="fa fa-fw fa-plus"
    label={trans('add_groups')}
    disabled={props.disabled}
    modal={[MODAL_GROUPS, {
      url: props.url,
      title: props.title,
      filters: props.filters,
      selectAction: (selected) => ({
        type: CALLBACK_BUTTON,
        label: trans('select', {}, 'actions'),
        callback: () => props.onChange(selected)
      })
    }]}
    size={props.size}
  />

GroupsButton.propTypes = {
  url: T.oneOfType([T.string, T.array]),
  title: T.string,
  filters: T.arrayOf(T.shape({
    // TODO : list filter types
  })),
  disabled: T.bool,
  onChange: T.func.isRequired,
  size: T.string
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
          size={props.size}
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
    <ContentPlaceholder
      icon="fa fa-users"
      title={trans('no_group')}
      size={props.size}
    >
      <GroupsButton
        {...props.picker}
        size={props.size}
        disabled={props.disabled}
        onChange={props.onChange}
      />
    </ContentPlaceholder>
  )
}

implementPropTypes(GroupsInput, DataInputTypes, {
  value: T.arrayOf(T.shape(
    GroupType.propTypes
  )),
  picker: T.shape({
    url: T.oneOfType([T.string, T.array]),
    title: T.string,
    filters: T.arrayOf(T.shape({
      // TODO : list filter types
    }))
  })
}, {
  value: null,
  picker: {}
})

export {
  GroupsInput
}
