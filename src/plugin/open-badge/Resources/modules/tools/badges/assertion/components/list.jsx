import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/app/intl/translation'
import {CALLBACK_BUTTON, LINK_BUTTON} from '#/main/app/buttons'
import {ListData} from '#/main/app/content/list/containers/data'
import {constants as listConstants} from '#/main/app/content/list/constants'
import {selectors as toolSelectors} from '#/main/core/tool/store'

import {AssertionBadgeCard} from '#/plugin/open-badge/tools/badges/assertion/components/card'
import {actions, selectors} from '#/plugin/open-badge/tools/badges/store'
import {ToolPage} from '#/main/core/tool'
import {PageListSection} from '#/main/app/page'

const AssertionsList = (props) =>
  <ToolPage title={trans('my_badges', {}, 'badge')}>
    <PageListSection>
      <ListData
        name={selectors.STORE_NAME + '.mine'}
        fetch={{
          url: ['apiv2_badge_assertion_current_user_list', {workspace: props.contextData ? props.contextData.id : null}],
          autoload: true
        }}
        primaryAction={(row) => ({
          type: LINK_BUTTON,
          target: props.path + `/badges/${row.badge.id}/assertion/${row.id}`,
          label: trans('open', {}, 'actions')
        })}
        definition={[
          {
            name: 'badge.name',
            type: 'string',
            label: trans('name'),
            displayed: true,
            primary: true
          }, {
            name: 'issuedOn',
            label: trans('granted_date', {}, 'badge'),
            type: 'date',
            displayed: true,
            primary: true,
            options: {
              time: true
            }
          }, {
            name: 'badge.meta.enabled',
            type: 'boolean',
            label: trans('enabled'),
            displayed: true
          }
        ]}
        actions={(rows) => [
          {
            type: CALLBACK_BUTTON,
            icon: 'fa fa-fw fa-download',
            label: trans('download', {}, 'actions'),
            callback: () => rows.map(row => props.download(row))
          }
        ]}
        card={AssertionBadgeCard}
        display={{current: listConstants.DISPLAY_LIST_SM}}
      />
    </PageListSection>
  </ToolPage>

AssertionsList.propTypes = {
  path: T.string.isRequired,
  download: T.func.isRequired,
  contextData: T.object
}

const Assertions = connect(
  (state) => ({
    path: toolSelectors.path(state),
    contextData: toolSelectors.contextData(state)
  }),
  (dispatch) => ({
    download(assertion) {
      dispatch(actions.downloadAssertion(assertion))
    }
  })
)(AssertionsList)

export {
  Assertions
}
