import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'
import isEmpty from 'lodash/isEmpty'

import {withRouter} from '#/main/app/router'
import {hasPermission} from '#/main/app/security'
import {selectors as securitySelectors} from '#/main/app/security/store'
import {trans} from '#/main/app/intl/translation'
import {CALLBACK_BUTTON, LINK_BUTTON} from '#/main/app/buttons'
import {ListData} from '#/main/app/content/list/containers/data'

import {selectors as resourceSelect} from '#/main/core/resource/store'

import {actions} from '#/plugin/wiki/resources/wiki/history/store'
import {selectors} from '#/plugin/wiki/resources/wiki/store/selectors'

const HistoryComponent = props =>
  <section className="wiki-section-history">
    <h2>{(!isEmpty(props.section.activeContribution) ? (props.section.activeContribution.title + ': ') : '') + trans('revision_history', {}, 'icap_wiki')}</h2>
    {!isEmpty(props.section) &&
    <ListData
      name={selectors.STORE_NAME + '.history.contributions'}
      fetch={{
        url: ['apiv2_wiki_section_contribution_history', {sectionId: props.section.id}],
        autoload: true
      }}
      primaryAction={(row) => ({
        label: trans('view', {}, 'platform'),
        type: LINK_BUTTON,
        target: `${props.path}/contribution/${props.section.id}/${row.id}`
      })}
      definition={[
        {
          name: 'meta.createdAt',
          alias: 'creationDate',
          label: trans('date', {}, 'platform'),
          type: 'date',
          displayed: true,
          filterable: true,
          options: {
            time: true
          }
        }, {
          name: 'meta.creator.name',
          alias: 'creator',
          label: trans('author', {}, 'platform'),
          type: 'string',
          displayed: true,
          render: rowData => rowData.meta.creator ? `${rowData.meta.creator.name}` : trans('unknown')
        },
        {
          name: 'active',
          label: trans('active_contribution', {}, 'icap_wiki'),
          type: 'boolean',
          displayed: true,
          filterable: false,
          sortable: false,
          calculated: rowData => rowData.id === props.section.activeContribution.id
        }
      ]}
      actions={rows => [
        {
          type: CALLBACK_BUTTON,
          label: trans('set_active_contribution', {}, 'icap_wiki'),
          icon: 'fa fa-fw fa-eye',
          callback: () => props.setActiveContribution(props.section.id, rows[0].id),
          scope: ['object'],
          displayed: props.section.activeContribution.id !== rows[0].id && (
            props.canEdit ||
              (props.mode === '0' && props.currentUser !== null) ||
              (props.mode !== '2' && props.currentUser !== null && props.section.meta.creator !== null && props.section.meta.creator.id === props.currentUser.id)
          )
        }, {
          type: LINK_BUTTON,
          icon: 'fa fa-fw fa-arrows-h',
          target: rows.length === 2 ? `${props.path}/contribution/compare/${props.section.id}/${rows[0].id}/${rows[1].id}` : props.path,
          label: trans('compare_versions', {}, 'icap_wiki'),
          scope: ['collection'],
          displayed: rows.length === 2
        }
      ]}
    />
    }
  </section>

HistoryComponent.propTypes = {
  path: T.string.isRequired,
  currentUser: T.object,
  section: T.object.isRequired,
  canEdit: T.bool.isRequired,
  setActiveContribution: T.func.isRequired,
  mode: T.string.isRequired
}

const History = withRouter(connect(
  state => ({
    path: resourceSelect.path(state),
    currentUser: securitySelectors.currentUser(state),
    section: selectors.currentSection(state),
    canEdit: hasPermission('edit', resourceSelect.resourceNode(state)),
    mode: selectors.mode(state)
  }),
  dispatch => (
    {
      setActiveContribution: (sectionId, contributionId) => dispatch(actions.setActiveContribution(sectionId, contributionId))
    }
  )
)(HistoryComponent))

export {
  History
}
