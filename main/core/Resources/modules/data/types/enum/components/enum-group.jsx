import React from 'react'

import {PropTypes as T, implementPropTypes} from '#/main/core/scaffolding/prop-types'
import {makeId} from '#/main/core/scaffolding/id'
import {trans} from '#/main/core/translation'
import {FormGroup as FormGroupWithFieldTypes} from '#/main/core/layout/form/prop-types'
import {FormGroup} from '#/main/core/layout/form/components/group/form-group.jsx'
import {TooltipButton} from '#/main/core/layout/button/components/tooltip-button.jsx'
import {TextGroup}  from '#/main/core/layout/form/components/group/text-group.jsx'

const EnumItem = props =>
  <li className="enum-item">
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
      <TooltipButton
        id={`enum-item-${props.item.id}-delete`}
        className="btn-link-danger"
        title={props.deleteButtonLabel}
        onClick={props.onDelete}
      >
        <span className="fa fa-fw fa-trash-o" />
      </TooltipButton>
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
  onDelete: T.func.isRequired
}

EnumItem.defaultTypes = {
  deleteButtonLabel: trans('delete')
}

const EnumGroup = (props) =>
  <FormGroup
    {...props}
    error={props.error && typeof props.error === 'string' ? props.error : undefined}
    className="enum-group"
  >
    {props.value.length > 0 &&
      <ul>
        {props.value.map((item, index) =>
          <EnumItem
            key={`item-${index}`}
            index={index}
            item={item}
            deleteButtonLabel={props.deleteButtonLabel}
            validating={props.validating}
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
      <div className="no-item-info">
        {props.placeholder}
      </div>
    }

    <button
      className="btn btn-block btn-default"
      type="button"
      onClick={() => props.onChange([].concat(props.value, [{
        id: makeId(),
        value: ''
      }]))}
    >
      <span className="fa fa-fw fa-plus icon-with-text-right" />
      {props.addButtonLabel}
    </button>
  </FormGroup>

implementPropTypes(EnumGroup, FormGroupWithFieldTypes, {
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
  EnumGroup
}
