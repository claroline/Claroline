import React, {PropTypes as T} from 'react'
import {t} from './../../../utils/translate'
import {FormGroup} from './../../../components/form/form-group.jsx'
import {Textarea} from './../../../components/form/textarea.jsx'

export const StepForm = props => {
  return (
    <fieldset>
      <FormGroup
        controlId={`step-${props.id}-title`}
        label={t('title')}
      >
        <input
          id={`step-${props.id}-title`}
          type="text"
          value={props.title}
          className="form-control"
          onChange={e => props.onChange({title: e.target.value})}
        />
      </FormGroup>
      <FormGroup
        controlId={`step-${props.id}-description`}
        label={t('description')}
      >
        <Textarea
          id={`step-${props.id}-description`}
          content={props.description}
          onChange={description => props.onChange({description})}
        />
      </FormGroup>
    </fieldset>
  )
}

StepForm.propTypes = {
  id: T.string.isRequired,
  title: T.string.isRequired,
  description: T.string.isRequired,
  onChange: T.func.isRequired,
  _errors: T.object
}
