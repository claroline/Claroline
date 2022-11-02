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
import {route as userRoute} from '#/main/community/routing'

import {Badge as BadgeTypes, Assertion as AssertionTypes} from '#/plugin/open-badge/prop-types'

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
    if (isEmpty(this.props.badge)) {
      return (
        <ContentLoader
          className="row"
          size="lg"
          description="Nous chargeons le badge..."
        />
      )
    }

    return (
      <Fragment>
        <div className="row badge-meta">
          <div className="badge-image img-thumbnail">
            <div
              style={this.props.badge.color ? {
                backgroundColor: this.props.badge.color
              } : undefined}
            >
              <img src={get(this.props.badge, 'image') ? asset(get(this.props.badge, 'image')) : null} />
            </div>
          </div>

          <div className="badge-info">
            <h1>{this.props.badge.name}</h1>
            <ContentHtml>{this.props.badge.description}</ContentHtml>
          </div>

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

          <ul className="nav nav-tabs">
            {!isEmpty(this.props.backAction) &&
              <li className="nav-back">
                <Button
                  {...this.props.backAction}
                />
              </li>
            }

            {this.props.sections.map(section =>
              <li
                key={section.name}
                className={classes({
                  active: section.name === this.state.currentSection
                })}
              >
                <Button
                  type={CALLBACK_BUTTON}
                  icon={section.icon}
                  label={section.label}
                  callback={() => this.setState({currentSection: section.name})}
                />
              </li>
            )}

            {!isEmpty(this.props.actions) &&
              <li className="nav-actions">
                <Button
                  type={MENU_BUTTON}
                  icon="fa fa-fw fa-ellipsis-v"
                  label={trans('show-more-actions', {}, 'actions')}
                  tooltip="bottom"
                  menu={{
                    align: 'right',
                    items: this.props.actions
                  }}
                />
              </li>
            }
          </ul>
        </div>

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
  backAction: T.shape({
    // TODO : action types
  }),
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
