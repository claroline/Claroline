import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import get from 'lodash/get'

import {Routes} from '#/main/app/router'
import {getPlatformRoles} from '#/main/community/utils'
import {displayDate, trans} from '#/main/app/intl'
import {Vertical} from '#/main/app/content/tabs/components/vertical'

import {User as UserTypes} from '#/main/community/prop-types'
import {getMainFacet} from '#/main/community/profile/utils'

const UserDetails = props =>
  <div className="user-details panel panel-default">
    <div className="panel-body text-center">
      {getPlatformRoles(props.user.roles).map(role => trans(role.translationKey)).join(', ')}
    </div>

    <ul className="list-group list-group-values">
      <li className="list-group-item">
        {trans('registered_at')}
        <span className="value">
          {displayDate(props.user.meta.created)}
        </span>
      </li>
      <li className="list-group-item">
        {trans('last_activity_at')}
        <span className="value">
          {props.user.meta.lastActivity ? displayDate(props.user.meta.lastActivity, false, true) : trans('never')}
        </span>
      </li>
    </ul>
  </div>

UserDetails.propTypes = {
  user: T.shape({
    meta: T.shape({
      created: T.string.isRequired,
      lastActivity: T.string
    }),
    roles: T.arrayOf(T.shape({
      type: T.number.isRequired,
      translationKey: T.string.isRequired
    })).isRequired
  })
}

class Profile extends Component {
  componentDidMount() {
    if (!this.props.loaded) {
      this.props.open()
    }
  }

  componentDidUpdate() {
    if (!this.props.loaded) {
      this.props.open()
    }
  }

  render() {
    return (
      <div className={classes('row user-profile', this.props.className)}>
        <div className="user-profile-aside col-md-3">
          {this.props.user.id &&
            <UserDetails
              user={this.props.user}
            />
          }

          {this.props.facets && 1 < this.props.facets.length &&
            <Vertical
              basePath={this.props.path}
              tabs={this.props.facets.map(facet => ({
                icon: facet.icon,
                title: facet.title,
                path: get(facet, 'meta.main') ? '' : `/${facet.id}`,
                exact: true
              }))}
            />
          }
        </div>

        <div className="user-profile-content col-md-9">
          <Routes
            path={this.props.path}
            routes={[
              {
                path: '/:id?',
                onEnter: (params) => this.props.openFacet(params.id || getMainFacet(this.props.facets).id),
                render: () => this.props.children
              }
            ]}
          />
        </div>
      </div>
    )
  }
}

Profile.propTypes = {
  className: T.string,
  path: T.string.isRequired,
  facets: T.array,
  user: T.shape(
    UserTypes.propTypes
  ).isRequired,
  loaded: T.bool,
  parameters: T.object.isRequired,
  open: T.func.isRequired,
  openFacet: T.func.isRequired,
  children: T.node
}

export {
  Profile
}
