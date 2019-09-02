import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/app/intl/translation'
import {CALLBACK_BUTTON, LINK_BUTTON} from '#/main/app/buttons'
import {ListData} from '#/main/app/content/list/containers/data'

import {selectors as toolSelectors} from '#/main/core/tool/store'

import issue from '#/plugin/open-badge/tools/badges/badge/actions/issue'
import {actions} from '#/plugin/open-badge/tools/badges/assertion/store'
import {AssertionList} from '#/plugin/open-badge/tools/badges/assertion/components/definition'

const AssertionsList = (props) =>
  <ListData
    name={props.name}
    fetch={{
      url: props.url,
      autoload: true
    }}
    primaryAction={(row) => ({
      type: LINK_BUTTON,
      target: props.path + `/badges/${row.badge.id}/assertion/${row.id}`,
      label: trans('', {}, 'actions')
    })}
    definition={AssertionList.definition}
    actions={(rows) => [
      issue(rows),
      {
        type: CALLBACK_BUTTON,
        icon: 'fa fa-fw fa-download',
        label: trans('download'),
        scope: ['object'],
        displayed: true,
        callback: () => props.download(rows[0])
      }
    ]}
    card={AssertionList.card}
  />

AssertionsList.propTypes = {
  currentUser: T.object,
  name: T.string.isRequired,
  badge: T.object,
  url: T.oneOfType([T.string, T.array]).isRequired,
  invalidate: T.func.isRequired,
  disable: T.func.isRequired,
  enable: T.func.isRequired,
  currentContext: T.object.isRequired,
  path: T.string.isRequired,
  download: T.func.isRequired
}

export const Assertions = connect(
  (state) => ({
    path: toolSelectors.path(state)
  }),
  (dispatch) => ({
    download(assertion) {
      dispatch(actions.download(assertion))
    }
  })
)(AssertionsList)
