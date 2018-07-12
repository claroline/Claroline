import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'
import merge from 'lodash/merge'

import {trans} from '#/main/core/translation'
import {makeId} from '#/main/core/scaffolding/id'

import {RoutedPageContent} from '#/main/core/layout/router'
import {ResourcePageContainer} from '#/main/core/resource/containers/page'
import {actions as formActions} from '#/main/core/data/form/actions'

import {Announces} from '#/plugin/announcement/resources/announcement/components/announces'
import {Announce} from '#/plugin/announcement/resources/announcement/components/announce'
import {AnnounceForm} from '#/plugin/announcement/resources/announcement/components/announce-form'
import {AnnounceSend} from '#/plugin/announcement/resources/announcement/components/announce-send'

import {Announcement as AnnouncementTypes} from '#/plugin/announcement/resources/announcement/prop-types'
import {select} from '#/plugin/announcement/resources/announcement/selectors'
import {actions} from '#/plugin/announcement/resources/announcement/actions'

const Resource = props =>
  <ResourcePageContainer
    primaryAction="create-announce"
    customActions={[
      {
        type: 'link',
        icon: 'fa fa-fw fa-list',
        label: trans('announcements_list', {}, 'announcement'),
        target: '/'
      }
    ]}
  >
    <RoutedPageContent
      routes={[
        {
          path: '/',
          exact: true,
          component: Announces
        }, {
          path: '/add',
          exact: true,
          component: AnnounceForm,
          onEnter: () => props.resetForm(merge({}, AnnouncementTypes.defaultProps, {
            id: makeId()
          }), true)
        }, {
          path: '/:id',
          component: Announce,
          exact: true,
          onEnter: (params) => props.openDetail(params.id),
          onLeave: props.resetDetail
        }, {
          path: '/:id/edit',
          component: AnnounceForm,
          onEnter: (params) => props.resetForm(props.posts.find(post => post.id === params.id))
        }, {
          path: '/:id/send',
          component: AnnounceSend,
          onEnter: (params) => {
            props.resetForm(props.posts.find(post => post.id === params.id))
            props.initFormDefaultRoles(props.roles.map(r => r.id))
          }
        }
      ]}
    />
  </ResourcePageContainer>

Resource.propTypes = {
  aggregateId: T.string.isRequired,
  posts: T.arrayOf(
    T.shape(AnnouncementTypes.propTypes)
  ).isRequired,
  roles: T.arrayOf(T.shape({
    id: T.string.isRequired
  })),
  openDetail: T.func.isRequired,
  resetDetail: T.func.isRequired,
  resetForm: T.func.isRequired,
  initFormDefaultRoles: T.func.isRequired
}

const AnnouncementResource = connect(
  state => ({
    aggregateId: select.aggregateId(state),
    posts: select.posts(state),
    roles: select.workspaceRoles(state)
  }),
  dispatch => ({
    openDetail(id) {
      dispatch(actions.openDetail(id))
    },
    resetDetail() {
      dispatch(actions.resetDetail())
    },
    resetForm(data, isNew) {
      dispatch(formActions.resetForm('announcementForm', data, isNew))
    },
    initFormDefaultRoles(roleIds) {
      dispatch(formActions.updateProp('announcementForm', 'roles', roleIds))
    }
  })
)(Resource)

export {
  AnnouncementResource
}
