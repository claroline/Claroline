import React from 'react'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {trans} from '#/main/app/intl/translation'
import {withRouter} from '#/main/app/router'
import {ASYNC_BUTTON, LINK_BUTTON} from '#/main/app/buttons'
import {PageSection} from '#/main/app/page/components/section'
import {ContentMenu} from '#/main/app/content/components/menu'

import {selectors as resourceSelectors} from '#/main/core/resource/store'

import {selectors} from '#/plugin/claco-form/resources/claco-form/store'
import {ResourceOverview} from '#/main/core/resource'


const OverviewComponent = props =>
  <ResourceOverview>
    {(props.canAddEntry || props.canSearchEntry || props.randomEnabled) &&
      <PageSection size="md" className="py-3">
        <ContentMenu
          items={[
            {
              id: 'add',
              icon: 'plus',
              label: trans('new_entry', {}, 'clacoform'),
              description: trans('new_entry_help', {}, 'clacoform'),
              displayed: props.canAddEntry,
              action: {
                type: LINK_BUTTON,
                target: `${props.path}/entry/form`
              }
            }, {
              id: 'list',
              icon: 'search',
              label: trans('entries_list', {}, 'clacoform'),
              description: trans('entries_list_help', {}, 'clacoform'),
              displayed: props.canSearchEntry,
              action: {
                type: LINK_BUTTON,
                target: `${props.path}/entries`
              }
            }, {
              id: 'random',
              icon: 'random',
              label: trans('random_entry', {}, 'clacoform'),
              description: trans('random_entry_help', {}, 'clacoform'),
              displayed: props.randomEnabled,
              action: {
                type: ASYNC_BUTTON,
                request: {
                  url: ['apiv2_clacoformentry_random', {clacoForm: props.resourceId}],
                  success: (entryId) => props.history.push(`${props.path}/entries/${entryId}`)
                }
              }
            }
          ]}
        />
      </PageSection>
    }
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
