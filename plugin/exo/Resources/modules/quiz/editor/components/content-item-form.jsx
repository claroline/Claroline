import React from 'react'
import {PropTypes as T} from 'prop-types'

import {t} from '#/main/core/translation'
import {FormGroup} from '#/main/core/layout/form/components/form-group.jsx'
import {Textarea} from '#/main/core/layout/form/components/textarea.jsx'

export const ContentItemForm = props =>
  <form>
    <FormGroup
      controlId={`item-${props.item.id}-title`}
      label={t('title')}
    >
      <input
        id={`item-${props.item.id}-title`}
        type="text"
        value={props.item.title || ''}
        className="form-control"
        onChange={e => props.onChange('title', e.target.value)}
      />
    </FormGroup>
    <FormGroup
      controlId={`item-${props.item.id}-description`}
      label={t('description')}
    >
      <Textarea
        id={`item-${props.item.id}-description`}
        content={props.item.description || ''}
        onChange={text => props.onChange('description', text)}
      />
    </FormGroup>
    <hr/>
    {props.children}
  </form>

ContentItemForm.propTypes = {
  item: T.shape({
    id: T.string.isRequired,
    title: T.string.isRequired,
    description: T.string.isRequired,
    _errors: T.object
  }).isRequired,
  children: T.element.isRequired,
  onChange: T.func.isRequired
}
