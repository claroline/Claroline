import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {trans} from '#/main/app/intl/translation'
import {Routes} from '#/main/app/router'
import {ResourcePage} from '#/main/core/resource/containers/page'
import {LINK_BUTTON} from '#/main/app/buttons'

import {constants} from '#/plugin/drop-zone/resources/dropzone/constants'

import {Overview} from '#/plugin/drop-zone/resources/dropzone/overview/components/overview'
import {Editor} from '#/plugin/drop-zone/resources/dropzone/editor/components/editor'
import {MyDrop} from '#/plugin/drop-zone/resources/dropzone/player/components/my-drop'
import {Drops} from '#/plugin/drop-zone/resources/dropzone/correction/components/drops'
import {Correctors} from '#/plugin/drop-zone/resources/dropzone/correction/components/correctors'
import {Corrector} from '#/plugin/drop-zone/resources/dropzone/correction/components/corrector'
import {Drop} from '#/plugin/drop-zone/resources/dropzone/correction/components/drop'
import {PeerDrop} from '#/plugin/drop-zone/resources/dropzone/player/components/peer-drop'
import {MyRevisions} from '#/plugin/drop-zone/resources/dropzone/player/components/my-revisions'
import {Revisions} from '#/plugin/drop-zone/resources/dropzone/player/components/revisions'
import {Revision} from '#/plugin/drop-zone/resources/dropzone/player/components/revision'

const DropzoneResource = props =>
  <ResourcePage
    styles={['claroline-distribution-plugin-drop-zone-dropzone-resource']}
    customActions={[
      {
        type: LINK_BUTTON,
        icon: 'fa fa-fw fa-home',
        label: trans('show_overview'),
        target: '/',
        exact: true
      }, {
        type: LINK_BUTTON,
        icon: 'fa fa-fw fa-upload',
        label: trans('show_evaluation', {}, 'dropzone'),
        target: '/my/drop',
        displayed: !!props.myDrop,
        exact: true
      }, {
        type: LINK_BUTTON,
        icon: 'fa fa-fw fa-list',
        label: trans('show_drops', {}, 'dropzone'),
        target: '/drops',
        displayed: props.canEdit
      }, {
        type: LINK_BUTTON,
        icon: 'fa fa-fw fa-users',
        label: trans('correctors', {}, 'dropzone'),
        target: '/correctors',
        displayed: props.canEdit && constants.REVIEW_TYPE_PEER === get(props.dropzone, 'parameters.reviewType')
      }, {
        type: LINK_BUTTON,
        icon: 'fa fa-fw fa-history',
        label: trans('show_revisions', {}, 'dropzone'),
        target: '/revisions',
        displayed: props.canEdit,
        exact: true
      }
    ]}
  >
    <Routes
      routes={[
        {
          path: '/',
          component: Overview,
          exact: true
        }, {
          path: '/edit',
          component: Editor,
          disabled: !props.canEdit,
          onLeave: () => props.resetForm(),
          onEnter: () => props.resetForm(props.dropzone)
        }, {
          path: '/my/drop',
          component: MyDrop,
          exact: true,
          onEnter: () => {
            if (props.currentRevisionId) {
              props.fetchRevision(props.currentRevisionId)
            }
          },
          onLeave: () => props.resetRevision()
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
        }, {
          path: '/my/drop/revisions',
          component: MyRevisions,
          disabled: !props.dropzone || !props.dropzone.parameters || !props.dropzone.parameters.revisionEnabled,
          exact: true
        }, {
          path: '/my/drop/revisions/:id',
          component: Revision,
          disabled: !props.dropzone || !props.dropzone.parameters || !props.dropzone.parameters.revisionEnabled,
          onEnter: (params) => {
            props.fetchRevision(params.id)
            props.fetchDropFromRevision(params.id)
          },
          onLeave: () => {
            props.resetRevision()
            props.resetCurrentDrop()
          }
        }, {
          path: '/revisions',
          component: Revisions,
          disabled: !props.canEdit,
          exact: true
        }, {
          path: '/revisions/:id',
          component: Revision,
          disabled: !props.canEdit,
          onEnter: (params) => {
            props.fetchRevision(params.id)
            props.fetchDropFromRevision(params.id)
          },
          onLeave: () => {
            props.resetRevision()
            props.resetCurrentDrop()
          }
        }
      ]}
    />
  </ResourcePage>

DropzoneResource.propTypes = {
  canEdit: T.bool.isRequired,
  dropzone: T.object.isRequired,
  myDrop: T.object,
  currentRevisionId: T.string,

  resetForm: T.func.isRequired,
  fetchDrop: T.func.isRequired,
  resetCurrentDrop: T.func.isRequired,
  fetchCorrections: T.func.isRequired,
  resetCorrectorDrop: T.func.isRequired,
  fetchPeerDrop: T.func.isRequired,
  fetchRevision: T.func.isRequired,
  fetchDropFromRevision: T.func.isRequired,
  resetRevision: T.func.isRequired
}

export {
  DropzoneResource
}
