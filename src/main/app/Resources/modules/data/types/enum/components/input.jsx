import React from 'react'
import classes from 'classnames'

import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {makeId} from '#/main/core/scaffolding/id'
import {trans} from '#/main/app/intl/translation'
import {DataInput as DataInputTypes} from '#/main/app/data/types/prop-types'
import {TextGroup}  from '#/main/core/layout/form/components/group/text-group'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {ContentPlaceholder} from '#/main/app/content/components/placeholder'

const EnumItem = props =>
  <li className="enum-item mb-2">
    <TextGroup
      id={`item-${props.index}-value`}
      className="enum-item-content"
      label={`${trans('choice')} ${props.index+1}`}
      hideLabel={true}
      value={props.item.value}
      onChange={value => props.onChange('value', value)}
      warnOnly={!props.validating}
      error={props.error}
    />

    <div className="right-controls">
      <Button
        id={`enum-item-${props.item.id}-delete`}
        type={CALLBACK_BUTTON}
        className="btn btn-text-danger"
        icon="fa fa-fw fa-trash"
        label={props.deleteButtonLabel}
        tooltip="left"
        callback={props.onDelete}
        dangerous={true}
        size={props.size}
      />
    </div>
  </li>

EnumItem.propTypes = {
  index: T.number.isRequired,
  item: T.shape({
    id: T.string,
    value: T.string
  }).isRequired,
  deleteButtonLabel: T.string.isRequired,
  error: T.string,
  validating: T.bool,
  onChange: T.func.isRequired,
  onDelete: T.func.isRequired,
  size: T.string
}

EnumItem.defaultTypes = {
  deleteButtonLabel: trans('delete')
}

const EnumInput = (props) =>
  <div className={classes('enum-control', props.className)}>
    {props.value.length > 0 &&
      <ul>
        {props.value.map((item, index) =>
          <EnumItem
            key={`item-${index}`}
            index={index}
            item={item}
            deleteButtonLabel={props.deleteButtonLabel}
            validating={props.validating}
            size={props.size}
            error={props.error && typeof props.error !== 'string' ? props.error[index] : undefined}
            onChange={(propName, propValue) => {
              const newItem = Object.assign({}, item, {
                [propName]: propValue
              })

              const items = props.value.slice()
              items.splice(index, 1, newItem)

              props.onChange(items)
            }}
            onDelete={() => {
              const items = props.value.slice()
              items.splice(index, 1)

              props.onChange(items)
            }}
          />
        )}
      </ul>
    }

    {props.value.length === 0 &&
      <ContentPlaceholder title={props.placeholder} size={props.size} />
    }

    <Button
      variant="btn"
      className="w-100"
      type={CALLBACK_BUTTON}
      icon="fa fa-fw fa-plus"
      label={props.addButtonLabel}
      callback={() => props.onChange([].concat(props.value, [{
        id: makeId(),
        value: ''
      }]))}
    />
  </div>

implementPropTypes(EnumInput, DataInputTypes, {
  value: T.arrayOf(T.shape({
    id: T.string,
    value: T.string
  })),
  error: T.oneOfType([T.string, T.object]),
  addButtonLabel: T.string.isRequired,
  deleteButtonLabel: T.string.isRequired
}, {
  value: [],
  placeholder: trans('no_item'),
  addButtonLabel: trans('add_an_item'),
  deleteButtonLabel: trans('delete')
})

export {
  EnumInput
}
