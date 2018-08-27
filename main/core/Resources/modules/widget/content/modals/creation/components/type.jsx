import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/core/translation'
import {GridSelection} from '#/main/app/content/grid/components/selection'

import {WidgetContentIcon} from '#/main/core/widget/content/components/icon'
import {Widget as WidgetTypes} from '#/main/core/widget/prop-types'

const ContentType = props =>
  <GridSelection
    items={props.types.map(type => {
      return ({
        name: type.name,
        icon: React.createElement(WidgetContentIcon, {
          type: type.name
        }),
        label: trans(type.name, {}, 'widget'),
        description: trans(`${type.name}_desc`, {}, 'widget'),
        tags: type.tags.map(tag => trans(tag)),
        sources: type.sources
      })
    })}
    handleSelect={props.select}
  />

ContentType.propTypes = {
  types: T.arrayOf(T.shape(
    WidgetTypes.propTypes
  )).isRequired,
  select: T.func.isRequired
}

export {
  ContentType
}
