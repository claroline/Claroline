import React from 'react'
import isEmpty from 'lodash/isEmpty'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/core/translation'
import {select as resourceSelect} from '#/main/core/resource/selectors'
import {select as formSelect} from '#/main/core/data/form/selectors'
import {actions as formActions} from '#/main/core/data/form/actions'
import {Routes} from '#/main/core/router/components/router.jsx'
import {ResourceContainer} from '#/main/core/resource/containers/resource.jsx'

import {select} from '#/plugin/drop-zone/resources/dropzone/selectors'
import {constants} from '#/plugin/drop-zone/resources/dropzone/constants'
import {actions as playerActions} from '#/plugin/drop-zone/resources/dropzone/player/actions'
import {actions as correctionActions} from '#/plugin/drop-zone/resources/dropzone/correction/actions'

import {Overview} from '#/plugin/drop-zone/resources/dropzone/overview/components/overview.jsx'
import {Editor} from '#/plugin/drop-zone/resources/dropzone/editor/components/editor.jsx'
import {MyDrop} from '#/plugin/drop-zone/resources/dropzone/player/components/my-drop.jsx'
import {Drops} from '#/plugin/drop-zone/resources/dropzone/correction/components/drops.jsx'
import {Correctors} from '#/plugin/drop-zone/resources/dropzone/correction/components/correctors.jsx'
import {Corrector} from '#/plugin/drop-zone/resources/dropzone/correction/components/corrector.jsx'
import {Drop} from '#/plugin/drop-zone/resources/dropzone/correction/components/drop.jsx'
import {PeerDrop} from '#/plugin/drop-zone/resources/dropzone/player/components/peer-drop.jsx'

const Resource = props =>
  <ResourceContainer
    editor={{
      opened: props.editorOpened,
      open: '#/edit',
      label: trans('configure', {}, 'platform'),
      save: {
        disabled: !props.saveEnabled,
        action: () => props.saveForm(props.dropzone.id)
      }
    }}
    customActions={[
      {
        icon: 'fa fa-fw fa-home',
        label: trans('show_overview'),
        action: '#/'
      }, {
        icon: 'fa fa-fw fa-upload',
        label: trans('show_evaluation', {}, 'dropzone'),
        action: '#/my/drop',
        displayed: !!props.myDrop
      }, {
        icon: 'fa fa-fw fa-list',
        label: trans('show_drops', {}, 'dropzone'),
        action: '#/drops',
        displayed: props.canEdit
      }, {
        icon: 'fa fa-fw fa-users',
        label: trans('correctors', {}, 'dropzone'),
        action: '#/correctors',
        displayed: props.canEdit && constants.REVIEW_TYPE_PEER === props.dropzone.parameters.reviewType
      }
    ]}
  >
    <Routes
      routes={[
        {
          path: '/',
          exact: true,
          component: Overview
        }, {
          path: '/edit',
          component: Editor,
          canEnter: () => props.canEdit,
          onLeave: () => props.resetForm(),
          onEnter: () => props.resetForm(props.dropzone)
        }, {
          path: '/my/drop',
          component: MyDrop
        }, {
          path: '/drops',
          component: Drops
        }, {
          path: '/drop/:id',
          component: Drop,
          onEnter: (params) => props.fetchDrop(params.id, 'current'),
          onLeave: () => props.resetCurrentDrop()
        }, {
          path: '/peer/drop',
          component: PeerDrop,
          onEnter: () => props.fetchPeerDrop()
        }, {
          path: '/correctors',
          component: Correctors,
          onEnter: () => {
            props.fetchCorrections(props.dropzone.id)
          }
        }, {
          path: '/corrector/:id',
          component: Corrector,
          onEnter: (params) => {
            props.fetchDrop(params.id, 'corrector')
            props.fetchCorrections(props.dropzone.id)
          },
          onLeave: () => props.resetCorrectorDrop()
        }
      ]}
    />
  </ResourceContainer>

Resource.propTypes = {
  canEdit: T.bool.isRequired,
  dropzone: T.object.isRequired,
  editorOpened: T.bool.isRequired,
  saveEnabled: T.bool.isRequired,
  myDrop: T.object,

  resetForm: T.func.isRequired,
  saveForm: T.func.isRequired,
  fetchDrop: T.func.isRequired,
  resetCurrentDrop: T.func.isRequired,
  fetchCorrections: T.func.isRequired,
  resetCorrectorDrop: T.func.isRequired,
  fetchPeerDrop: T.func.isRequired
}

const DropzoneResource = connect(
  (state) => ({
    canEdit: resourceSelect.editable(state),
    dropzone: state.dropzone,
    editorOpened: !isEmpty(formSelect.data(formSelect.form(state, 'dropzoneForm'))),
    saveEnabled: formSelect.saveEnabled(formSelect.form(state, 'dropzoneForm')),
    myDrop: select.myDrop(state)
  }),
  (dispatch) => ({
    resetForm: (formData) => dispatch(formActions.resetForm('dropzoneForm', formData)),
    saveForm: (dropzoneId) => dispatch(formActions.saveForm('dropzoneForm', ['claro_dropzone_update', {id: dropzoneId}])),

    fetchDrop: (dropId, type) => dispatch(correctionActions.fetchDrop(dropId, type)),
    resetCurrentDrop: () => dispatch(correctionActions.resetCurrentDrop()),
    fetchCorrections: (dropzoneId) => dispatch(correctionActions.fetchCorrections(dropzoneId)),
    resetCorrectorDrop: () => dispatch(correctionActions.resetCorrectorDrop()),
    fetchPeerDrop: () => dispatch(playerActions.fetchPeerDrop())
  })
)(Resource)

export {
  DropzoneResource
}
