import React from 'react'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {withRouter} from '#/main/app/router'
import {AsyncButton} from '#/main/app/buttons/async'
import {LinkButton} from '#/main/app/buttons/link'

import {selectors as resourceSelectors} from '#/main/core/resource/store'

import {selectors} from '#/plugin/claco-form/resources/claco-form/store'

const OverviewComponent = props =>
  <div className="resource-section resource-overview">
    {props.canAddEntry &&
      <LinkButton
        className="btn-overview"
        target={`${props.path}/entry/form`}
      >
        <span className="action-icon fa fa-plus" />
        <span className="action-label">{trans('add-entry', {}, 'actions')}</span>
      </LinkButton>
    }

    {props.canSearchEntry &&
      <LinkButton
        className="btn-overview"
        target={`${props.path}/entries`}
      >
        <span className="action-icon fa fa-search" />
        <span className="action-label">{trans('find_entry', {}, 'clacoform')}</span>
      </LinkButton>
    }

    {props.randomEnabled &&
      <AsyncButton
        className="btn-overview"
        request={{
          url: ['claro_claco_form_entry_random', {clacoForm: props.resourceId}],
          success: (entryId) => props.history.push(`${props.path}/entries/${entryId}`)
        }}
      >
        <span className="action-icon fa fa-random" />
        <span className="action-label">{trans('random_entry', {}, 'clacoform')}</span>
      </AsyncButton>
    }
  </div>

OverviewComponent.propTypes = {
  path: T.string.isRequired,
  resourceId: T.string.isRequired,
  canSearchEntry: T.bool.isRequired,
  canAddEntry: T.bool.isRequired,
  randomEnabled: T.bool.isRequired,
  history: T.shape({
    push: T.func.isRequired
  }).isRequired
}

const Overview = withRouter(
  connect(
    (state) => ({
      path: resourceSelectors.path(state),
      resourceId: selectors.clacoForm(state).id,
      canSearchEntry: selectors.canSearchEntry(state),
      randomEnabled: selectors.clacoForm(state).random.enabled,
      canAddEntry: selectors.canAddEntry(state)
    })
  )(OverviewComponent)
)

export {
  Overview
}
