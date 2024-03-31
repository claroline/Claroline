import React from 'react'
import {PropTypes as T} from 'prop-types'

import {TagForm} from '#/plugin/tag/tools/tags/components/form'
import {TagList} from '#/plugin/tag/tools/tags/containers/list'
import {Tool} from '#/main/core/tool'

const TagsTool = (props) =>
  <Tool
    {...props}
    pages={[
      {
        path: '/',
        exact: true,
        component: TagList
      }, {
        path: '/new',
        component: TagForm,
        exact: true,
        onEnter: () => props.openForm(),
        disabled: !props.canCreate
      }, {
        path: '/:id?',
        component: TagForm,
        onEnter: (params = {}) => props.openForm(params.id)
      }
    ]}
  />

TagsTool.propTypes = {
  canCreate: T.bool.isRequired,
  openForm: T.func.isRequired
}

export {
  TagsTool
}
