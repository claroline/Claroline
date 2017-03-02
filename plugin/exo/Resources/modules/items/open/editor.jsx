import React, {PropTypes as T} from 'react'
import get from 'lodash/get'
import {actions} from './editor'
import {tex} from './../../utils/translate'
import {FormGroup} from './../../components/form/form-group.jsx'

export const Open = (props) =>
  <fieldset>
    <FormGroup
      controlId={`item-${props.item.id}-maxScore`}
      label={tex('score_max')}
      error={get(props.item, '_errors.maxScore')}
    >
      <input
        id={`item-${props.item.id}-maxScore`}
        type="number"
        min="0"
        value={props.item.score.max}
        className="form-control"
        onChange={e => props.onChange(
          actions.update('maxScore', e.target.value)
        )}
      />
    </FormGroup>

    <FormGroup
      controlId={`item-${props.item.id}-maxLength`}
      label={tex('open_maximum_length')}
      error={get(props.item, '_errors.maxLength')}
    >
      <input
        id={`item-${props.item.id}-maxLength`}
        type="number"
        min="0"
        value={props.item.maxLength}
        className="form-control"
        onChange={e => props.onChange(
          actions.update('maxLength', e.target.value)
        )}
      />
    </FormGroup>
  </fieldset>

Open.propTypes = {
  item: T.shape({
    id: T.string.isRequired,
    score: T.shape({
      type: T.string.isRequired,
      max: T.number.isRequired
    }).isRequired,
    maxLength: T.number
  }).isRequired,
  onChange: T.func.isRequired
}
