import React from 'react'
import cloneDeep from 'lodash/cloneDeep'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/core/translation'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON} from '#/main/app/buttons'

import {PropTypes as T, implementPropTypes} from '#/main/core/scaffolding/prop-types'
import {FormField as FormFieldTypes} from '#/main/core/layout/form/prop-types'

import {FormProp} from '#/main/app/content/form/components/prop'

const CollectionInput = props =>
  <div id={props.id} className="collection-control">
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
        {props.value.map((value, index) =>
          <li key={index} className="collection-item">
            <FormProp
              id={`${props.id}-${index}`}
              type={props.type}
              options={props.options}

              label={`${props.label} #${index + 1}`}
              size="sm"
              hideLabel={true}
              required={true}
              disabled={props.disabled}
              readOnly={props.readOnly}
              validating={props.validating}

              error={props.error instanceof Object ? props.error[index] : undefined}
              value={value}

              onChange={(newValue) => {
                const newCollection = cloneDeep(props.value)

                // replace current item by updated one
                newCollection[index] = newValue

                props.onChange(newCollection)
              }}
            />

            <Button
              className="btn-link btn-delete"
              type={CALLBACK_BUTTON}
              icon="fa fa-fw fa-trash-o"
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
        )}
      </ul>
    }

    <Button
      className="btn btn-block btn-add"
      type={CALLBACK_BUTTON}
      label={props.button}
      disabled={props.disabled || (props.max && props.value && props.max <= props.value.length)}
      callback={() => props.onChange([].concat(props.value || [], [undefined]))}
    />
  </div>

implementPropTypes(CollectionInput, FormFieldTypes, {
  value: T.oneOfType([T.string, T.number, T.array]),
  min: T.number,
  max: T.number,
  placeholder: T.string,
  button: T.string,

  // items def
  type: T.string.isRequired,
  options: T.object // depends on the type of items
}, {
  value: [],
  placeholder: trans('no_item'),
  button: trans('add')
})

export {
  CollectionInput
}
