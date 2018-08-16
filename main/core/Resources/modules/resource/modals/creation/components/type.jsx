import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/core/translation'
import {GridSelection} from '#/main/app/content/grid/components/selection'

import {getType} from '#/main/core/resource/utils'
import {ResourceIcon} from '#/main/core/resource/components/icon'

const ResourceType = props =>
  <GridSelection
    items={props.types.map(name => {
      const tags = getType({meta: {type: name}}).tags || []

      return ({ // todo maybe filter disabled types
        name: name,
        icon: React.createElement(ResourceIcon, {
          mimeType: `custom/${name}`
        }),
        label: trans(name, {}, 'resource'),
        description: trans(`${name}_desc`, {}, 'resource'),
        tags: tags.map(tag => trans(tag))
      })
    })}
    handleSelect={props.select}
  />

ResourceType.propTypes = {
  types: T.arrayOf(T.string),
  select: T.func.isRequired
}

export {
  ResourceType
}
