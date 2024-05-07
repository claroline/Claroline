import React from 'react'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {trans} from '#/main/app/intl/translation'
import {withRouter} from '#/main/app/router'
import {AsyncButton} from '#/main/app/buttons/async'
import {LinkButton} from '#/main/app/buttons/link'

import {selectors as resourceSelectors} from '#/main/core/resource/store'

import {selectors} from '#/plugin/claco-form/resources/claco-form/store'

import {ResourceOverview} from '#/main/core/resource'
import {PageSection} from '#/main/app/page/components/section'

const OverviewComponent = props =>
  <ResourceOverview>
    <PageSection size="md" className="py-3">
      {(props.canAddEntry || props.canSearchEntry || props.randomEnabled) &&
        <ul className="list-group">
          {props.canAddEntry &&
            <LinkButton
              className="list-group-item list-group-item-action d-flex gap-3 p-3"
              target={`${props.path}/entry/form`}
            >
              <span className="fa fa-fw fa-plus fs-2" aria-hidden={true} />
              <div className="" role="presentation">
                <h5 className="mb-1">{trans('new_entry', {}, 'clacoform')}</h5>
                <p className="mb-0 text-body-secondary">{trans('new_entry_help', {}, 'clacoform')}</p>
              </div>
              <span className="fa fa-chevron-right text-body-tertiary ms-auto align-self-center" aria-hidden={true} />
            </LinkButton>
          }

          {props.canSearchEntry &&
            <LinkButton
              className="list-group-item list-group-item-action d-flex gap-3 p-3"
              target={`${props.path}/entries`}
            >
              <span className="fa fa-fw fa-search fs-2" />
              <div className="" role="presentation">
                <h5 className="mb-1">{trans('entries_list', {}, 'clacoform')}</h5>
                <p className="mb-0 text-body-secondary">{trans('entries_list_help', {}, 'clacoform')}</p>
              </div>
              <span className="fa fa-chevron-right text-body-tertiary ms-auto align-self-center" aria-hidden={true} />
            </LinkButton>
          }

          {props.randomEnabled &&
            <AsyncButton
              className="list-group-item list-group-item-action d-flex gap-3 p-3"
              request={{
                url: ['claro_claco_form_entry_random', {clacoForm: props.resourceId}],
                success: (entryId) => props.history.push(`${props.path}/entries/${entryId}`)
              }}
            >
              <span className="fa fa-fw fa-random fs-2" />
              <div className="" role="presentation">
                <h5 className="mb-1">{trans('random_entry', {}, 'clacoform')}</h5>
                <p className="mb-0 text-body-secondary">{trans('random_entry_help', {}, 'clacoform')}</p>
              </div>
              <span className="fa fa-chevron-right text-body-tertiary ms-auto align-self-center" aria-hidden={true} />
            </AsyncButton>
          }
        </ul>
      }
    </PageSection>
  </ResourceOverview>

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
      randomEnabled: get(selectors.clacoForm(state), 'random.enabled', false),
      canAddEntry: selectors.canAddEntry(state)
    })
  )(OverviewComponent)
)

export {
  Overview
}
