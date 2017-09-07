import React from 'react'
import {PropTypes as T} from 'prop-types'

import {t} from '#/main/core/translation'

import {HtmlGroup} from '#/main/core/layout/form/components/group/html-group.jsx'
import {TextGroup} from '#/main/core/layout/form/components/group/text-group.jsx'

const ContentItemForm = props =>
  <form>
    <TextGroup
      controlId={`item-${props.item.id}-title`}
      label={t('title')}
      value={props.item.title || ''}
      onChange={text => props.onChange('title', text)}
    />

    <HtmlGroup
      controlId={`item-${props.item.id}-description`}
      label={t('description')}
      content={props.item.description || ''}
      onChange={text => props.onChange('description', text)}
    />

    <hr className="item-content-separator" />

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

export {
  ContentItemForm
}
