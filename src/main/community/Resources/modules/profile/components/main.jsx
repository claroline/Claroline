import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'

import {Routes} from '#/main/app/router'
import {hasPermission} from '#/main/app/security'
import {ContentLoader} from '#/main/app/content/components/loader'

import {UserPage} from '#/main/core/user/components/page'
import {User as UserTypes} from '#/main/community/prop-types'
import {route} from '#/main/community/user/routing'
import {getActions} from '#/main/community/user/utils'

import {ProfileEdit} from '#/main/community/profile/editor/components/main'
import {ProfileShow} from '#/main/community/profile/player/components/main'

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
        actions={getActions([this.props.user], {
          add: () => false,
          update: (users) => this.props.history.push(route(users[0])),
          delete: () => false
        }, this.props.path, this.props.currentUser)}
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
