import React, {PropTypes as T} from 'react'
import get from 'lodash/get'
import {FormGroup} from './../../components/form/form-group.jsx'
import {Textarea} from './../../components/form/textarea.jsx'

export const TextObjectEditor = (props) =>
  <fieldset>
    <FormGroup
      controlId={`object-${props.object.id}-data`}
      label=''
      warnOnly={!props.validating}
      error={get(props.object, '_errors.data')}
    >
      <Textarea
        id={`object-${props.object.id}-data`}
        content={props.object.data || ''}
        onChange={data => props.onChange(data)}
      />
    </FormGroup>
  </fieldset>

TextObjectEditor.propTypes = {
  object: T.shape({
    id: T.string.isRequired,
    type: T.string.isRequired,
    url: T.string,
    data: T.string,
    _errors: T.object
  }).isRequired,
  validating: T.bool.isRequired,
  onChange: T.func.isRequired
}
