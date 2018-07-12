import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {selectors as resourceSelect} from '#/main/core/resource/store'
import {hasPermission} from '#/main/core/resource/permissions'
import {RoutedPageContent} from '#/main/core/layout/router'
import {ResourcePageContainer} from '#/main/core/resource/containers/page'

import {Player} from '#/plugin/bibliography/resources/book-reference/player/components/player'
import {Editor} from '#/plugin/bibliography/resources/book-reference/editor/components/editor'

const Resource = props =>
  <ResourcePageContainer>
    <RoutedPageContent
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
  </ResourcePageContainer>

Resource.propTypes = {
  canEdit: T.bool.isRequired
}

const BookReferenceResource = connect(
  state => ({
    canEdit: hasPermission('edit', resourceSelect.resourceNode(state))
  })
)(Resource)

export {
  BookReferenceResource
}
