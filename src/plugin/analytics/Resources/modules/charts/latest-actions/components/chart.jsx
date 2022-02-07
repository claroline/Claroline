import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {MenuButton} from '#/main/app/buttons/menu'
import {CALLBACK_BUTTON} from '#/main/app/buttons'

import {LogCard} from '#/main/log/components/card'

class LatestActionsChart extends Component {
  componentDidMount() {
    if (!this.props.loaded) {
      this.props.fetchActions(this.props.url)
    }
  }

  render() {
    let current = 'all'
    if (this.props.filters[0] && 'user-login' === this.props.filters[0].value) {
      current = 'analytics_connections'
    }

    return (
      <div className="panel panel-default panel-analytics">
        <div className="panel-heading">
          <h2 className="panel-title">
            {trans('latest_actions', {}, 'analytics')}
          </h2>

          <nav className="panel-actions">
            <MenuButton
              id="latest-activity-actions"
              className="btn-link"
              menu={{
                align: 'right',
                items: [
                  {
                    type: CALLBACK_BUTTON,
                    label: trans('all'),
                    callback: () => this.props.changeFilter(null, this.props.url),
                    active: 'all' === current
                  }, {
                    type: CALLBACK_BUTTON,
                    label: trans('analytics_connections'),
                    callback: () => this.props.changeFilter('user-login', this.props.url),
                    active: 'analytics_connections' === current
                  }
                ]
              }}
            >
              {trans(current)}
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
          {this.props.data.map(action => (
            <LogCard
              key={action.id}
              data={action}
              orientation="row"
              size="xs"
            />
          ))}
        </div>
      </div>
    )
  }
}

LatestActionsChart.propTypes = {
  url: T.oneOfType([T.string, T.array]).isRequired,

  loaded: T.bool.isRequired,
  filters: T.array,
  data: T.array,
  fetchActions: T.func.isRequired,
  changeFilter: T.func.isRequired
}

LatestActionsChart.defaultProps = {
  filters: []
}


export {
  LatestActionsChart
}
