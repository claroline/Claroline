import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {CALLBACK_BUTTON, LINK_BUTTON} from '#/main/app/buttons'
import {MenuButton} from '#/main/app/buttons/menu'

import {route as resourceRoute} from '#/main/core/resource/routing'
import {ResourceCard} from '#/main/core/resource/components/card'

class TopResourcesChart extends Component {
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
            Top ressources
          </h2>

          <nav className="panel-actions">
            <MenuButton
              id="top-resources-actions"
              className="btn-link"
              menu={{
                align: 'right',
                items: [
                  {
                    type: CALLBACK_BUTTON,
                    label: 'Les plus consultées',
                    callback: () => true,
                    active: true
                  }, {
                    type: CALLBACK_BUTTON,
                    label: 'Les plus appréciées',
                    callback: () => true,
                    displayed: false // TODO
                  }, {
                    type: CALLBACK_BUTTON,
                    label: 'Les plus récentes',
                    callback: () => true,
                    displayed: false // TODO
                  }
                ]
              }}
            >
              Les plus consultées
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
          {this.props.data.map(resourceNode =>
            <ResourceCard
              key={resourceNode.id}
              primaryAction={{
                type: LINK_BUTTON,
                label: trans('open', {}, 'actions'),
                target: resourceRoute(resourceNode)
              }}
              data={resourceNode}
              size="xs"
            />
          )}
        </div>
      </div>
    )
  }
}

TopResourcesChart.propTypes = {
  url: T.oneOfType([T.string, T.array]).isRequired,

  loaded: T.bool.isRequired,
  data: T.arrayOf(T.shape({
    // TODO : node types
  })).isRequired,
  fetchTop: T.func.isRequired
}

export {
  TopResourcesChart
}
