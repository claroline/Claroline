import React from 'react'

import {trans} from '#/main/core/translation'
import {makeId} from '#/main/core/scaffolding/id'
import {PropTypes as T, implementPropTypes} from '#/main/core/scaffolding/prop-types'

import {FormGroup as FormGroupWithFieldTypes} from '#/main/core/layout/form/prop-types'
import {FormGroup} from '#/main/core/layout/form/components/group/form-group'
import {TooltipButton} from '#/main/core/layout/button/components/tooltip-button'
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
      <TooltipButton
        id={`criterion-${props.criterion.id}-delete`}
        className="btn-link-danger"
        title={trans('delete')}
        onClick={props.onDelete}
      >
        <span className="fa fa-fw fa-trash-o" />
      </TooltipButton>
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

const CriteriaGroup = props =>
  <FormGroup
    {...props}
    error={props.error && typeof props.error === 'string' ? props.error : undefined}
    className="criteria-group"
  >
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

    <button
      className="btn btn-block btn-default"
      type="button"
      onClick={() => props.onChange([].concat(props.value, [{
        id: makeId(),
        instruction: ''
      }]))}
    >
      <span className="fa fa-fw fa-plus icon-with-text-right" />
      {trans('add_criterion', {}, 'dropzone')}
    </button>
  </FormGroup>

implementPropTypes(CriteriaGroup, FormGroupWithFieldTypes, {
  // more precise value type
  value: T.arrayOf(
    T.shape(CriterionTypes.propTypes)
  ),
  // override error types to handles individual criterion errors
  error: T.oneOfType([T.string, T.object])
}, {
  value: [],
  placeholder: trans('no_criterion', {}, 'dropzone')
})

export {
  CriteriaGroup
}
