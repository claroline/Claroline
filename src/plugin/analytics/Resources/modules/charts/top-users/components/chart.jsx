import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {CALLBACK_BUTTON, LINK_BUTTON} from '#/main/app/buttons'
import {MenuButton} from '#/main/app/buttons/menu'

import {route as userRoute} from '#/main/community/user/routing'
import {UserCard} from '#/main/community/user/components/card'

class TopUsersChart extends Component {
  componentDidMount() {
    if (!this.props.loaded) {
      this.props.fetchTop(this.props.url)
    }
  }

  render() {
    return (
      <div className="panel panel-default panel-analytics">
        <div className="panel-heading">
          <h2 className="panel-title">
            Top utilisateurs
          </h2>

          <nav className="panel-actions">
            <MenuButton
              id="top-users-actions"
              className="btn-link"
              menu={{
                align: 'right',
                items: [
                  {
                    type: CALLBACK_BUTTON,
                    label: 'Les plus actifs',
                    callback: () => true,
                    displayed: false // TODO
                  }, {
                    type: CALLBACK_BUTTON,
                    label: 'Les plus souvent connectés',
                    callback: () => true,
                    displayed: false // TODO
                  }, {
                    type: CALLBACK_BUTTON,
                    label: 'Les plus récents',
                    callback: () => true,
                    active: true
                  }, {
                    type: CALLBACK_BUTTON,
                    label: 'Inscrits dans le plus d\'espaces d\'activités',
                    callback: () => true,
                    displayed: false // TODO
                  }
                ]
              }}
            >
              Les plus récents
              <span className="fa fa-caret-down icon-with-text-left" />
            </MenuButton>
          </nav>
        </div>

        <div
          className="data-cards-stacked data-cards-striped"
          style={{
            height: '315px', // FIXME
            overflowY: 'auto'
          }}
        >
          {this.props.data.map(user =>
            <UserCard
              key={user.id}
              primaryAction={{
                type: LINK_BUTTON,
                label: trans('open', {}, 'actions'),
                target: userRoute(user)
              }}
              data={user}
              size="xs"
            />
          )}
        </div>
      </div>
    )
  }
}

TopUsersChart.propTypes = {
  url: T.oneOfType([T.string, T.array]).isRequired,

  loaded: T.bool.isRequired,
  data: T.arrayOf(T.shape({
    // TODO : users types
  })).isRequired,
  fetchTop: T.func.isRequired
}

export {
  TopUsersChart
}
