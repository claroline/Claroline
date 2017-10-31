import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/core/translation'

import {Router} from '#/main/core/router/components/router.jsx'
import {ResourceContainer} from '#/main/core/layout/resource/containers/resource.jsx'

import {Announces} from './announces.jsx'
import {Announce} from './announce.jsx'
import {AnnounceForm} from './announce-form.jsx'

import {Announcement as AnnouncementTypes} from './../prop-types'
import {select} from './../selectors.js'
import {actions} from './../actions.js'

const Resource = props =>
  <ResourceContainer
    editor={{
      opened: props.formOpened,
      open: '#/add',
      icon: 'fa fa-plus',
      label: trans('add_announce', {}, 'announcement'),
      save: {
        disabled: !props.formPendingChanges || (props.formValidating && !props.formValid),
        action: () => {
          props.save(props.aggregateId, props.formData)
        }
      }
    }}
    customActions={[
      {
        icon: 'fa fa-fw fa-list',
        label: trans('announcements_list', {}, 'announcement'),
        action: '#/'
      }
    ]}
  >
    <Router
      routes={[
        {
          path: '/',
          component: Announces
        }, {
          path: '/add',
          component: AnnounceForm,
          onEnter: () => {
            props.openForm(AnnouncementTypes.defaultProps)
            props.initFormDefaultRoles(props.roles.map(r => r.id))
          },
          onLeave: props.resetForm
        }, {
          path: '/:id',
          component: Announce,
          onEnter: (params) => props.openDetail(params.id),
          onLeave: props.resetDetail
        }, {
          path: '/:id/edit',
          component: AnnounceForm,
          onEnter: (params) => props.openForm(props.posts.find(post => post.id === params.id)),
          onLeave: props.resetForm
        }
      ]}
    />
  </ResourceContainer>

Resource.propTypes = {
  aggregateId: T.string.isRequired,
  posts: T.arrayOf(
    T.shape(AnnouncementTypes.propTypes)
  ).isRequired,

  openDetail: T.func.isRequired,
  resetDetail: T.func.isRequired,

  formOpened: T.bool.isRequired,
  formPendingChanges: T.bool.isRequired,
  formValidating: T.bool.isRequired,
  formValid: T.bool.isRequired,
  roles: T.arrayOf(T.shape({
    id: T.number.isRequired
  })),

  save: T.func.isRequired,
  openForm: T.func.isRequired,
  resetForm: T.func.isRequired,
  initFormDefaultRoles: T.func.isRequired
}

function mapStateToProps(state) {
  return {
    aggregateId: select.aggregateId(state),
    posts: select.posts(state),
    formPendingChanges: select.formHasPendingChanges(state),
    formOpened: select.formIsOpened(state),
    formData: select.formData(state),
    formValid: select.formValid(state),
    formValidating: select.formValidating(state),
    roles: select.workspaceRoles(state)
  }
}

function mapDispatchToProps(dispatch) {
  return {
    openDetail(id) {
      dispatch(actions.openDetail(id))
    },
    resetDetail() {
      dispatch(actions.resetDetail())
    },
    openForm(announce) {
      dispatch(actions.openForm(announce))
    },
    resetForm() {
      dispatch(actions.resetForm())
    },
    validate() {
      dispatch(actions.validateForm())
    },
    save(aggregateId, announce) {
      dispatch(actions.saveAnnounce(aggregateId, announce))
    },
    initFormDefaultRoles(roleIds) {
      dispatch(actions.updateForm('roles', roleIds))
    }
  }
}

const ConnectedAnnouncementResource = connect(mapStateToProps, mapDispatchToProps)(Resource)

export {
  ConnectedAnnouncementResource as AnnouncementResource
}
