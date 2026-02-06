import React from 'react'

import {trans} from '#/main/app/intl/translation'
import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {DataInput as DataInputTypes} from '#/main/app/data/types/prop-types'

import {ResourceNode as ResourceNodeTypes} from '#/main/core/resource/prop-types'
import {MODAL_RESOURCES} from '#/main/core/modals/resources'
import {EntityInput} from '#/main/app/data/types/entity'
import {LINK_BUTTON} from '#/main/app/buttons'
import {route} from '#/main/core/resource'

const ResourceInput = props =>
  <EntityInput
    {...props}
    add={trans(props.multiple ? 'add_resources' : 'add_resource', {}, 'actions')}
    pickerType={MODAL_RESOURCES}
    openAction={(resource) => ({
      type: LINK_BUTTON,
      target: route(resource)
    })}
  />

implementPropTypes(ResourceInput, DataInputTypes, {
  value: T.oneOfType([
    T.shape(ResourceNodeTypes.propTypes),
    T.arrayOf(
      T.shape(ResourceNodeTypes.propTypes)
    )
  ]),
  embedded: T.bool,
  picker: T.shape({
    current: T.shape({
      slug: T.string.isRequired,
      name: T.string.isRequired
    }),
    contextId: T.string,
    filters: T.array
  })
}, {
  value: null,
  picker: {
    current: null,
    filters: [],
    root: null
  }
})

export {
  ResourceInput
}
