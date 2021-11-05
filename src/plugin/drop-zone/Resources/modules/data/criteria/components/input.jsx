import React from 'react'

import {trans} from '#/main/app/intl/translation'
import {makeId} from '#/main/core/scaffolding/id'
import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'

import {DataInput as DataInputTypes} from '#/main/app/data/types/prop-types'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {HtmlGroup}  from '#/main/core/layout/form/components/group/html-group'

import {Criterion as CriterionTypes} from '#/plugin/drop-zone/data/criteria/prop-types'

const Criterion = props =>
  <li className="criterion-item">
    <HtmlGroup
      id={`criterion-${props.index}-instruction`}
      className="criterion-content"
      label={`${trans('criterion', {}, 'dropzone')} ${props.index+1}`}
      hideLabel={true}
      value={props.criterion.instruction}
      onChange={value => props.onChange('instruction', value)}
      warnOnly={!props.validating}
      error={props.error}
    />

    <div className="right-controls">
      <Button
        id={`criterion-${props.criterion.id}-delete`}
        className="btn-link"
        type={CALLBACK_BUTTON}
        icon="fa fa-fw fa-trash-o"
        label={trans('delete', {}, 'actions')}
        callback={props.onDelete}
        tooltip="left"
        dangerous={true}
      />
    </div>
  </li>

Criterion.propTypes = {
  index: T.number.isRequired,
  criterion: T.shape(
    CriterionTypes.propTypes
  ).isRequired,
  error: T.string,
  validating: T.bool,
  onChange: T.func.isRequired,
  onDelete: T.func.isRequired
}

const CriteriaInput = props =>
  <div className="criteria-group">
    {0!== props.value.length &&
      <ul>
        {props.value.map((criterion, index) =>
          <Criterion
            key={`criterion-${index}`}
            index={index}
            criterion={criterion}
            validating={props.validating}
            error={props.error && typeof props.error !== 'string' ? props.error[index] : undefined}
            onChange={(propName, propValue) => {
              const newCriterion = Object.assign({}, criterion, {
                [propName]: propValue
              })

              const newCriteria = props.value.slice()
              newCriteria.splice(index, 1, newCriterion)

              props.onChange(newCriteria)
            }}
            onDelete={() => {
              const newCriteria = props.value.slice()
              newCriteria.splice(index, 1)

              props.onChange(newCriteria)
            }}
          />
        )}
      </ul>
    }

    {0 === props.value.length &&
      <div className="no-criterion-info">{props.placeholder}</div>
    }

    <Button
      className="btn btn-block"
      type={CALLBACK_BUTTON}
      icon="fa fa-fw fa-plus"
      label={trans('add_criterion', {}, 'dropzone')}
      callback={() => props.onChange([].concat(props.value, [{
        id: makeId(),
        instruction: ''
      }]))}
    />
  </div>

implementPropTypes(CriteriaInput, DataInputTypes, {
  // more precise value type
  value: T.arrayOf(
    T.shape(CriterionTypes.propTypes)
  )
}, {
  value: [],
  placeholder: trans('no_criterion', {}, 'dropzone')
})

export {
  CriteriaInput
}
