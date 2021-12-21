import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {Routes} from '#/main/app/router'
import {LINK_BUTTON} from '#/main/app/buttons'
import {hasPermission} from '#/main/app/security'
import {ContentLoader} from '#/main/app/content/components/loader'

import {UserPage} from '#/main/core/user/components/page'
import {User as UserTypes} from '#/main/core/user/prop-types'
import {route} from '#/main/core/user/routing'
import {getActions} from '#/main/core/user/utils'

import {ProfileEdit} from '#/main/core/user/profile/editor/components/main'
import {ProfileShow} from '#/main/core/user/profile/player/components/main'

class Profile extends Component {
  componentDidMount() {
    this.props.open(this.props.username)
  }

  componentDidUpdate() {
    this.props.open(this.props.username)
  }

  render() {
    if (!this.props.loaded) {
      return (
        <ContentLoader
          size="lg"
          description="Nous chargeons votre utilisateur..."
        />
      )
    }

    return (
      <UserPage
        showBreadcrumb={this.props.showBreadcrumb}
        breadcrumb={this.props.breadcrumb}
        user={this.props.user}
        toolbar="edit | send-message add-contact | fullscreen more"
        actions={
          getActions([this.props.user], {
            add: () => false,
            update: (users) => this.props.history.push(route(users[0])),
            delete: () => false
          }, this.props.path, this.props.currentUser)
            .then(actions => [
              {
                name: 'edit',
                type: LINK_BUTTON,
                icon: 'fa fa-pencil',
                label: trans('edit', {}, 'actions'),
                target: this.props.path + '/edit',
                displayed: hasPermission('edit', this.props.user),
                primary: true
              }
            ].concat(actions))
        }
      >
        <Routes
          path={this.props.path}
          routes={[
            {
              path: '/show',
              render: () => (
                <ProfileShow
                  path={this.props.path}
                />
              )
            }, {
              path: '/edit',
              disabled: !hasPermission('edit', this.props.user),
              render: () => (
                <ProfileEdit
                  path={this.props.path}
                />
              )
            }
          ]}
          redirect={[
            {from: '/', exact: true, to: '/show'}
          ]}
        />
      </UserPage>
    )
  }
}

Profile.propTypes = {
  username: T.string.isRequired,
  history: T.object.isRequired,
  showBreadcrumb: T.bool.isRequired,
  breadcrumb: T.arrayOf(T.shape({
    type: T.string,
    label: T.string.isRequired,
    displayed: T.bool,
    target: T.oneOfType([T.string, T.array])
  })),
  user: T.shape(
    UserTypes.propTypes
  ).isRequired,
  currentUser: T.shape(
    UserTypes.propTypes
  ).isRequired,
  path: T.string,
  loaded: T.bool,
  parameters: T.object.isRequired,
  open: T.func.isRequired
}

export {
  Profile
}
