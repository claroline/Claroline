import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

// TODO : avoid hard dependency
import html2pdf from 'html2pdf.js'

import {trans} from '#/main/app/intl/translation'
import {CALLBACK_BUTTON, LINK_BUTTON} from '#/main/app/buttons'
import {ListData} from '#/main/app/content/list/containers/data'
import {constants as listConstants} from '#/main/app/content/list/constants'
import {selectors as toolSelectors} from '#/main/core/tool/store'

import {AssertionBadgeCard} from '#/plugin/open-badge/tools/badges/assertion/components/card'
import {actions, selectors} from '#/plugin/open-badge/tools/badges/assertion/store'

const AssertionsList = (props) =>
  <ListData
    name={selectors.LIST_NAME}
    fetch={{
      url: ['apiv2_assertion_current_user_list'],
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
        label: trans('issued_on', {}, 'badge'),
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
        scope: ['object'],
        callback: () => props.download(rows[0])
      }
    ]}
    card={AssertionBadgeCard}
    display={{current: listConstants.DISPLAY_LIST_SM}}
  />

AssertionsList.propTypes = {
  path: T.string.isRequired,
  download: T.func.isRequired
}

const Assertions = connect(
  (state) => ({
    path: toolSelectors.path(state)
  }),
  (dispatch) => ({
    download(assertion) {
      dispatch(actions.download(assertion)).then(pdfContent => {
        html2pdf()
          .set({
            filename:    pdfContent.name,
            image:       { type: 'jpeg', quality: 1 },
            html2canvas: { scale: 4 },
            enableLinks: true
          })
          .from(pdfContent.content, 'string')
          .save()
      })
    }
  })
)(AssertionsList)

export {
  Assertions
}
