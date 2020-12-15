import React from 'react'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'

import {Routes} from '#/main/app/router'
import {actions as listActions} from '#/main/app/content/list/store'

import {selectors as resourceSelectors} from '#/main/core/resource/store'

import {selectors} from '#/plugin/forum/resources/forum/store'
import {actions} from '#/plugin/forum/resources/forum/player/store'
import {Subject} from '#/plugin/forum/resources/forum/player/components/subject'
import {Subjects} from '#/plugin/forum/resources/forum/player/components/subjects'

const PlayerComponent = (props) =>
  <Routes
    path={props.path}
    routes={[
      {
        path: '/subjects',
        component: Subjects,
        exact: true,
        onEnter: () => props.loadSubjectList()
      }, {
        path: '/subjects/form/:id?',
        component: Subject,
        onEnter: (params) => {
          if (params.id) {
            props.newSubject(params.id)
          } else {
            props.invalidateMessagesList()
            props.newSubject()
          }
        },
        onLeave: () => {
          props.closeSubjectForm()
          if (props.editingSubject){
            props.stopSubjectEdition()
          }
        }
      },{
        path: '/subjects/show/:id',
        component: Subject,
        onEnter: (params) => {
          props.invalidateMessagesList()
          props.openSubject(params.id)
        },
        onLeave: () => {
          if (props.showSubjectForm){
            props.closeSubjectForm()
          }
        }
      }
    ]}
  />

PlayerComponent.propTypes = {
  path: T.string.isRequired,
  newSubject: T.func.isRequired,
  closeSubjectForm: T.func.isRequired,
  stopSubjectEdition: T.func.isRequired,
  openSubject: T.func.isRequired,
  showSubjectForm: T.bool.isRequired,
  editingSubject: T.bool.isRequired,
  loadSubjectList: T.func.isRequired,
  loadSubjectForm: T.func,
  invalidateMessagesList: T.func
}

const Player = connect(
  state => ({
    path: resourceSelectors.path(state),
    editingSubject: selectors.editingSubject(state),
    showSubjectForm: selectors.showSubjectForm(state)
  }),
  dispatch => ({
    newSubject(id) {
      dispatch(actions.newSubject(id))
    },
    openSubject(id) {
      dispatch(actions.openSubject(id))
    },
    closeSubjectForm() {
      dispatch(actions.closeSubjectForm())
    },
    stopSubjectEdition() {
      dispatch(actions.stopSubjectEdition())
    },
    loadSubjectList() {
      dispatch(listActions.invalidateData(`${selectors.STORE_NAME}.subjects.list`))
    },
    invalidateMessagesList() {
      dispatch(listActions.invalidateData(`${selectors.STORE_NAME}.subjects.messages`))
    }
  })
)(PlayerComponent)

export {
  Player
}
