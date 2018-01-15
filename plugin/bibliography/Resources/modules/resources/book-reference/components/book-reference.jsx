import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {ResourceContainer} from '#/main/core/resource/containers/resource.jsx'
import {Routes, Router} from '#/main/core/router'
import {Player} from './player.jsx'
import {Editor} from './editor.jsx'
import {select as formSelect} from '#/main/core/data/form/selectors'
import {actions as formActions} from '#/main/core/data/form/actions'

const BookReference = props =>
  <ResourceContainer
    editor={{
      opened: props.formOpened,
      open: '#/edit',
      save: {
        disabled: !props.saveEnabled,
        action: () => {}
      }
    }}
  >
    <Router>
      <Routes routes={[
        {
          path: '/',
          exact: true,
          component: Player
        },
        {
          path: '/edit',
          component: Editor,
          onEnter: () => props.openForm(props.bookReference)
        }
      ]}/>
    </Router>
  </ResourceContainer>

BookReference.propTypes = {
  saveEnabled: T.bool.isRequired,
  bookReference: T.object.isRequired,
  openForm: T.func.isRequired,
  formOpened: T.bool.isRequired
}

function mapStateToProps(state) {
  return {
    saveEnabled: formSelect.saveEnabled(formSelect.form(state, 'bookReferenceForm')),
    bookReference: state.bookReference,
    formOpened: formSelect.data(formSelect.form(state, 'bookReferenceForm')) !== null
  }
}

function mapDispatchToProps(dispatch) {
  return {
    openForm(bookReferenceData) { dispatch(formActions.resetForm('bookReferenceForm', bookReferenceData)) }
  }
}

const ConnectedBookReference = connect(mapStateToProps, mapDispatchToProps)(BookReference)

export {
  ConnectedBookReference as BookReference
}
