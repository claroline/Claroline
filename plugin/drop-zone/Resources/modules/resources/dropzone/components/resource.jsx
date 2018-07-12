import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/core/translation'
import {selectors as resourceSelect} from '#/main/core/resource/store'
import {hasPermission} from '#/main/core/resource/permissions'
import {actions as formActions} from '#/main/core/data/form/actions'
import {RoutedPageContent} from '#/main/core/layout/router'
import {ResourcePageContainer} from '#/main/core/resource/containers/page.jsx'

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
  <ResourcePageContainer
    customActions={[
      {
        type: 'link',
        icon: 'fa fa-fw fa-home',
        label: trans('show_overview'),
        target: '/'
      }, {
        type: 'link',
        icon: 'fa fa-fw fa-upload',
        label: trans('show_evaluation', {}, 'dropzone'),
        target: '/my/drop',
        displayed: !!props.myDrop
      }, {
        type: 'link',
        icon: 'fa fa-fw fa-list',
        label: trans('show_drops', {}, 'dropzone'),
        target: '/drops',
        displayed: props.canEdit
      }, {
        type: 'link',
        icon: 'fa fa-fw fa-users',
        label: trans('correctors', {}, 'dropzone'),
        target: '/correctors',
        displayed: props.canEdit && constants.REVIEW_TYPE_PEER === props.dropzone.parameters.reviewType
      }
    ]}
  >
    <RoutedPageContent
      headerSpacer={false}
      routes={[
        {
          path: '/',
          exact: true,
          component: Overview
        }, {
          path: '/edit',
          component: Editor,
          disabled: !props.canEdit,
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
  </ResourcePageContainer>

Resource.propTypes = {
  canEdit: T.bool.isRequired,
  dropzone: T.object.isRequired,
  myDrop: T.object,

  resetForm: T.func.isRequired,
  fetchDrop: T.func.isRequired,
  resetCurrentDrop: T.func.isRequired,
  fetchCorrections: T.func.isRequired,
  resetCorrectorDrop: T.func.isRequired,
  fetchPeerDrop: T.func.isRequired
}

const DropzoneResource = connect(
  (state) => ({
    canEdit: hasPermission('edit', resourceSelect.resourceNode(state)),
    dropzone: state.dropzone,
    myDrop: select.myDrop(state)
  }),
  (dispatch) => ({
    resetForm: (formData) => dispatch(formActions.resetForm('dropzoneForm', formData)),

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
