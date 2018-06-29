import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/core/translation'
import {selectors as resourceSelect} from '#/main/core/resource/store'
import {hasPermission} from '#/main/core/resource/permissions'
import {RoutedPageContent} from '#/main/core/layout/router'
import {ResourcePageContainer} from '#/main/core/resource/containers/page'
import {actions as pageActions} from '#/main/core/resource/store'
import {select as formSelect} from '#/main/core/data/form/selectors'
import {actions as formActions} from '#/main/core/data/form/actions'

import {Player} from '#/plugin/bibliography/resources/book-reference/player/components/player'
import {Editor} from '#/plugin/bibliography/resources/book-reference/editor/components/editor'

const Resource = props =>
  <ResourcePageContainer
    editor={{
      path: '/edit',
      save: {
        disabled: !props.saveEnabled,
        action: () => props.saveForm(props.id)
      }
    }}
    customActions={[
      {
        type: 'link',
        icon: 'fa fa-fw fa-home',
        label: trans('show_overview'),
        target: '/',
        primary: false
      }
    ]}
  >
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
  id: T.number.isRequired,
  saveEnabled: T.bool.isRequired,
  saveForm: T.func.isRequired,
  canEdit: T.bool.isRequired
}

const BookReferenceResource = connect(
  state => ({
    id: formSelect.data(formSelect.form(state, 'bookReference')).id,
    saveEnabled: formSelect.saveEnabled(formSelect.form(state, 'bookReference')),
    canEdit: hasPermission('edit', resourceSelect.resourceNode(state))
  }),
  dispatch => ({
    saveForm(id) {
      dispatch(
        formActions.saveForm('bookReference', ['apiv2_book_reference_update', {id: id}])
      ).then(
        data => dispatch(pageActions.update({'name': data.name}))
      )
    }
  })
)(Resource)

export {
  BookReferenceResource
}
