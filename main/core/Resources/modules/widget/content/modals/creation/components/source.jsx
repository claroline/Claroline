import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/core/translation'
import {GridSelection} from '#/main/app/content/grid/components/selection'

import {WidgetSourceIcon} from '#/main/core/widget/content/components/icon'

const ContentSource = props =>
  <GridSelection
    items={props.sources.map(source => {
      return ({
        name: source.name,
        icon: React.createElement(WidgetSourceIcon, {
          type: source.name
        }),
        label: trans(source.name, {}, 'data_sources'),
        description: trans(`${source.name}_desc`, {}, 'data_sources'),
        tags: source.tags.map(tag => trans(tag))
      })
    })}
    handleSelect={props.select}
  />

ContentSource.propTypes = {
  sources: T.arrayOf(T.shape({
    name: T.string.isRequired,
    tags: T.arrayOf(T.string)
  })).isRequired,
  select: T.func.isRequired
}

export {
  ContentSource
}
