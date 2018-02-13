import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {Routes} from '#/main/core/router'
import {ResourcePageContainer} from '#/main/core/resource/containers/page.jsx'
import {select as formSelect} from '#/main/core/data/form/selectors'
import {actions as formActions} from '#/main/core/data/form/actions'

import {Player} from './player.jsx'
import {Editor} from './editor.jsx'

const Resource = props =>
  <ResourcePageContainer
    editor={{
      path: '/edit',
      save: {
        disabled: !props.saveEnabled,
        action: () => props.save(props.id)
      }
    }}
  >
    <Routes
      routes={[
        {
          path: '/',
          exact: true,
          component: Player
        }, {
          path: '/edit',
          component: Editor
        }
      ]}
    />
  </ResourcePageContainer>

Resource.propTypes = {
  id: T.number.isRequired,
  saveEnabled: T.bool.isRequired,
  save: T.func.isRequired
}

const BookReferenceResource = connect(
  state => ({
    id: formSelect.data(formSelect.form(state, 'bookReference')).id,
    saveEnabled: formSelect.saveEnabled(formSelect.form(state, 'bookReference'))
  }),
  dispatch => ({
    save(id) {
      dispatch(
        formActions.saveForm('bookReference', ['apiv2_book_reference_update', {id: id}])
      )
    }
  })
)(Resource)

export {
  BookReferenceResource
}
