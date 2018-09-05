import React from 'react'
import {PropTypes as T} from 'prop-types'

import {Routes} from '#/main/app/router'
import {ResourcePage} from '#/main/core/resource/containers/page'

import {Player} from '#/plugin/bibliography/resources/book-reference/player/components/player'
import {Editor} from '#/plugin/bibliography/resources/book-reference/editor/components/editor'

const BookReferenceResource = props =>
  <ResourcePage>
    <Routes
      routes={[
        {
          path: '/',
          exact: true,
          component: Player
        }, {
          path: '/edit',
          disabled: !props.canEdit,
          component: Editor
        }
      ]}
    />
  </ResourcePage>

BookReferenceResource.propTypes = {
  canEdit: T.bool.isRequired
}

export {
  BookReferenceResource
}
