import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'

import {ItemIcon} from '#/plugin/exo/items/components/icon'

/**
 * Renders the name and icon of an ItemType.
 */
const ItemType = props =>
  <article className="item-type">
    <ItemIcon name={props.name} size="md" />

    <div>
      <h1>{trans(props.name, {}, 'question_types')}</h1>
      <p className="hidden-xs">{trans(`${props.name}_desc`, {}, 'question_types')}</p>
    </div>
  </article>

ItemType.propTypes = {
  name: T.string.isRequired
}

export {
  ItemType
}
