import React from 'react'
import {PropTypes as T} from 'prop-types'

import {ResourcePage} from '#/main/core/resource/containers/page'

import {Player} from '#/plugin/bibliography/resources/book-reference/player/components/player'
import {Editor} from '#/plugin/bibliography/resources/book-reference/editor/components/editor'

const BookReferenceResource = props =>
  <ResourcePage
    routes={[
      {
        path: '/',
        exact: true,
        component: Player
      }, {
        path: '/edit',
        disabled: !props.canEdit,
        render: () => {
          const component = <Editor path={props.path} />

          return component
        }
      }
    ]}
  />

BookReferenceResource.propTypes = {
  path: T.string.isRequired,
  canEdit: T.bool.isRequired
}

export {
  BookReferenceResource
}
