import React, {createElement} from 'react'
import classes from 'classnames'
import cloneDeep from 'lodash/cloneDeep'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON} from '#/main/app/buttons'

import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {DataInput as DataInputTypes} from '#/main/app/data/types/prop-types'

import {DataInput} from '#/main/app/data/components/input'

const CollectionInput = props =>
  <div id={props.id} className={classes('collection-control', props.className)}>
    {isEmpty(props.value) &&
      <div className="no-item-info">{props.placeholder}</div>
    }

    {!isEmpty(props.value) &&
      <Button
        className="btn-link btn-delete-all"
        type={CALLBACK_BUTTON}
        label={trans('delete_all')}
        disabled={props.disabled}
        size="sm"
        dangerous={true}
        callback={() => props.onChange([])}
      />
    }

    {!isEmpty(props.value) &&
      <ul>
        {props.value.map((value, index) => {
          let customInput
          if (props.component) {
            customInput = createElement(props.component, Object.assign({}, props.options, {
              disabled: props.disabled,
              validating: props.validating,
              error: props.error instanceof Object ? props.error[index] : undefined,
              value: value,
              onChange: (newValue) => {
                const newCollection = cloneDeep(props.value)

                // replace current item by updated one
                newCollection[index] = newValue

                props.onChange(newCollection)
              }
            }))
          } else if (props.render) {
            customInput = props.render(value, props.error instanceof Object ? props.error[index] : undefined, index)
          }

          return (
            <li key={index} className="collection-item">
              <DataInput
                id={`${props.id}-${index}`}
                type={props.type}
                options={props.options}

                label={`${props.label} #${index + 1}`}
                size="sm"
                hideLabel={true}
                required={true}
                disabled={props.disabled}
                validating={props.validating}

                error={props.error instanceof Object ? props.error[index] : undefined}
                value={value}

                onChange={(newValue) => {
                  const newCollection = cloneDeep(props.value)

                  // replace current item by updated one
                  newCollection[index] = newValue

                  props.onChange(newCollection)
                }}
              >
                {customInput}
              </DataInput>

              <Button
                className="btn-link btn-delete"
                type={CALLBACK_BUTTON}
                icon="fa fa-fw fa-trash"
                label={trans('delete')}
                tooltip="left"
                disabled={props.disabled}
                dangerous={true}
                size="sm"
                callback={() => {
                  const newCollection = cloneDeep(props.value)

                  // remove item from collection
                  newCollection.splice(index, 1)
                  props.onChange(newCollection)
                }}
              />
            </li>
          )
        })}
      </ul>
    }

    <Button
      className="btn btn-block btn-add"
      type={CALLBACK_BUTTON}
      icon="fa fa-fw fa-plus"
      label={props.button}
      disabled={props.disabled || (props.max && props.value && props.max <= props.value.length)}
      callback={() => props.onChange([].concat(props.value || [], [props.defaultItem]))}
    />
  </div>

implementPropTypes(CollectionInput, DataInputTypes, {
  value: T.oneOfType([T.string, T.number, T.array]),
  min: T.number,
  max: T.number,
  placeholder: T.string,
  button: T.string,
  defaultItem: T.any,

  // items def
  type: T.string,
  render: T.func,
  component: T.any,
  options: T.object // depends on the type of items
}, {
  value: [],
  placeholder: trans('no_item'),
  button: trans('add'),
  defaultItem: undefined
})

export {
  CollectionInput
}
