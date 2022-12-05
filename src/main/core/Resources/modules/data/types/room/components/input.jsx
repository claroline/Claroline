import React, {Fragment} from 'react'

import {trans} from '#/main/app/intl/translation'
import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {CALLBACK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {Button} from '#/main/app/action/components/button'

import {DataInput as DataInputTypes} from '#/main/app/data/types/prop-types'
import {ContentPlaceholder} from '#/main/app/content/components/placeholder'
import {RoomCard} from '#/main/core/data/types/room/components/card'
import {MODAL_ROOMS} from '#/main/core/modals/rooms'

const RoomsButton = props =>
  <Button
    className="btn btn-block"
    style={{marginTop: 10}}
    type={MODAL_BUTTON}
    icon="fa fa-fw fa-plus"
    label={trans('add_room')}
    disabled={props.disabled}
    modal={[MODAL_ROOMS, {
      url: props.url,
      title: props.title,
      filters: props.filters,
      selectAction: (selected) => ({
        type: CALLBACK_BUTTON,
        label: trans('select', {}, 'actions'),
        callback: () => props.onChange(selected[0])
      })
    }]}
    size={props.size}
  />

RoomsButton.propTypes = {
  url: T.oneOfType([T.string, T.array]),
  title: T.string,
  filters: T.arrayOf(T.shape({
    // TODO : list filter types
  })),
  disabled: T.bool,
  onChange: T.func.isRequired,
  size: T.string
}

const RoomInput = props => {
  if (props.value) {
    return (
      <Fragment>
        <RoomCard
          data={props.value}
          size="xs"
          actions={[
            {
              name: 'delete',
              type: CALLBACK_BUTTON,
              icon: 'fa fa-fw fa-trash',
              label: trans('delete', {}, 'actions'),
              dangerous: true,
              disabled: props.disabled,
              callback: () => props.onChange(null)
            }
          ]}
        />

        <RoomsButton
          {...props.picker}
          size={props.size}
          disabled={props.disabled}
          onChange={props.onChange}
        />
      </Fragment>
    )
  }

  return (
    <ContentPlaceholder
      icon="fa fa-door-open"
      title={trans('no_room')}
      size={props.size}
    >
      <RoomsButton
        {...props.picker}
        size={props.size}
        disabled={props.disabled}
        onChange={props.onChange}
      />
    </ContentPlaceholder>
  )
}

implementPropTypes(RoomInput, DataInputTypes, {
  value: T.object,
  picker: T.shape({
    title: T.string
  })
}, {
  value: null
})

export {
  RoomInput
}
