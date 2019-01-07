import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {FormGroup} from '#/main/app/content/form/components/group.jsx'
import {Textarea} from '#/main/core/layout/form/components/field/textarea.jsx'

export const TextObjectEditor = (props) =>
  <fieldset>
    <FormGroup
      id={`object-${props.object.id}-data`}
      label=''
      warnOnly={!props.validating}
      error={get(props.object, '_errors.data')}
    >
      <Textarea
        id={`object-${props.object.id}-data`}
        value={props.object.data || ''}
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
