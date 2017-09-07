import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {trans} from '#/main/core/translation'
import {HtmlGroup} from '#/main/core/layout/form/components/group/html-group.jsx'
import {actions} from './editor'

const TextContent = (props) =>
  <fieldset>
    <HtmlGroup
      controlId={`item-${props.item.id}-data`}
      label={trans('text', {}, 'question_types')}
      content={props.item.data || ''}
      onChange={content => props.onChange(actions.updateItemContentText(content))}
      warnOnly={!props.validating}
      error={get(props.item, '_errors.data')}
    />
  </fieldset>

TextContent.propTypes = {
  item: T.shape({
    id: T.string.isRequired,
    data: T.string.isRequired,
    _errors: T.object
  }).isRequired,
  validating: T.bool.isRequired,
  onChange: T.func.isRequired
}

export {
  TextContent
}
