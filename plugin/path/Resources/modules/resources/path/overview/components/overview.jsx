import React from 'react'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/core/translation'
import {ResourceOverview} from '#/main/core/resource/components/overview.jsx'

import {Summary} from '#/plugin/path/resources/path/overview/components/summary.jsx'
import {select} from '#/plugin/path/resources/path/selectors'
import {Path as PathTypes} from '#/plugin/path/resources/path/prop-types'

const OverviewComponent = props =>
  <ResourceOverview
    contentText={props.path.display.description}
    actions={[
      { // TODO : implement continue and restart
        icon: 'fa fa-fw fa-play icon-with-text-right',
        label: trans('start_path', {}, 'path'),
        action: '#/play',
        primary: true,
        disabled: props.empty,
        disabledMessages: props.empty ? [trans('start_disabled_empty')]:[]
      }
    ]}
  >
    <section className="resource-parameters">
      <h3 className="h2">{trans('summary')}</h3>

      <Summary
        steps={props.path.steps}
      />
    </section>
  </ResourceOverview>

OverviewComponent.propTypes = {
  path: T.shape(
    PathTypes.propTypes
  ).isRequired,
  empty: T.bool.isRequired
}

const Overview = connect(
  (state) => ({
    path: select.path(state),
    empty: select.empty(state)
  })
)(OverviewComponent)

export {
  Overview
}
