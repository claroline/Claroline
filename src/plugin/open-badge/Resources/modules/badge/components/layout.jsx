import React, {Fragment, Component, createElement} from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'
import classes from 'classnames'
import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'

import {asset} from '#/main/app/config/asset'
import {trans} from '#/main/app/intl/translation'
import {displayDate} from '#/main/app/intl/date'
import {ContentHtml} from '#/main/app/content/components/html'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON, MENU_BUTTON} from '#/main/app/buttons'
import {LinkButton} from '#/main/app/buttons/link'
import {ContentLoader} from '#/main/app/content/components/loader'
import {selectors as securitySelectors} from '#/main/app/security/store'
import {selectors as toolSelectors} from '#/main/core/tool/store'
import {UserAvatar} from '#/main/core/user/components/avatar'
import {displayUsername} from '#/main/community/utils'
import {route as userRoute} from '#/main/community/user/routing'

import {Badge as BadgeTypes, Assertion as AssertionTypes} from '#/plugin/open-badge/prop-types'
import {ContentSizing} from '#/main/app/content/components/sizing'

// TODO : reuse ContentHeading
// TODO : enabled granted placeholder if current user does not have the badge

class BadgeLayoutComponent extends Component {
  constructor(props) {
    super(props)

    this.state = {
      currentSection: this.props.sections[0] ? this.props.sections[0].name : null
    }
  }

  renderSection() {
    if (this.state.currentSection) {
      const section = this.props.sections.find(current => current.name === this.state.currentSection)
      if (section) {
        if (section.render) {
          return section.render()
        }

        if (section.component) {
          return createElement(section.component)
        }
      }
    }

    return null
  }

  componentDidUpdate() {
    // current section is no longer in the list of available sections
    if (-1 === this.props.sections.findIndex(section => section.name === this.state.currentSection)) {
      this.setState({
        currentSection: this.props.sections[0] ? this.props.sections[0].name : null
      })
    }
  }

  render() {
    return (
      <Fragment>


        {false && isEmpty(this.props.assertion) &&
          <div className="badge-user badge-not-granted">
            <span className="badge-certificate">
              <span className="fa fa-certificate" />
              <span className="fa fa-times" />
            </span>

            <h2 className="h3">
              <small>{trans('not_granted', {}, 'badge')}</small>
            </h2>
          </div>
        }

        {!isEmpty(this.props.assertion) && get(this.props.currentUser, 'id') !== get(this.props.assertion.user, 'id') &&
          <LinkButton className="badge-user badge-granted" target={userRoute(this.props.assertion.user)}>
            <UserAvatar picture={this.props.assertion.user.picture} alt={false} />
            <h2 className="h3">
              {displayUsername(get(this.props.assertion, 'user'))}
              <small>{trans('granted_at', {date: displayDate(get(this.props.assertion, 'issuedOn'), false, true)}, 'badge')}</small>
            </h2>
          </LinkButton>
        }

        {!isEmpty(this.props.assertion) && get(this.props.currentUser, 'id') === get(this.props.assertion.user, 'id') &&
          <div className="badge-user badge-granted">
            <span className="badge-certificate">
              <span className="fa fa-certificate" />
              <span className="fa fa-check" />
            </span>

            <h2 className="h3">
              <small>{trans('granted_at', {date: displayDate(get(this.props.assertion, 'issuedOn'), false, true)}, 'badge')}</small>
            </h2>
          </div>
        }

        {this.renderSection()}
      </Fragment>
    )
  }
}

BadgeLayoutComponent.propTypes = {
  path: T.string.isRequired,
  currentUser: T.shape({
    // TODO : user types
  }),
  badge: T.shape(
    BadgeTypes.propTypes
  ),
  assertion: T.shape(
    AssertionTypes.propTypes
  ),
  actions: T.arrayOf(T.shape({
    // TODO : action types
  })),
  sections: T.arrayOf(T.shape({
    name: T.string.isRequired,
    icon: T.string,
    label: T.string.isRequired,
    render: T.func.isRequired
  }))
}

BadgeLayoutComponent.defaultProps = {
  sections: []
}

const BadgeLayout = connect(
  (state) => ({
    path: toolSelectors.path(state),
    currentUser: securitySelectors.currentUser(state)
  })
)(BadgeLayoutComponent)

export {
  BadgeLayout
}
