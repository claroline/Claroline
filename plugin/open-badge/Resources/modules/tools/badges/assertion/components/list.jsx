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

import {AssertionCard} from '#/plugin/open-badge/tools/badges/assertion/components/card'
import {actions} from '#/plugin/open-badge/tools/badges/assertion/store'

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
        label: trans('download'),
        scope: ['object'],
        callback: () => {
          props.download(rows[0]).then(pdfContent => {
            html2pdf()
              .set({
                filename:    pdfContent.name,
                image:       { type: 'jpeg', quality: 1 },
                html2canvas: { scale: 4 }
              })
              .from(pdfContent.content, 'string')
              .save()
          })
        }
      }
    ]}
    card={AssertionCard}
    display={{current: listConstants.DISPLAY_LIST_SM}}
  />

AssertionsList.propTypes = {
  name: T.string.isRequired,
  url: T.oneOfType([T.string, T.array]).isRequired,
  path: T.string.isRequired,
  download: T.func.isRequired
}

const Assertions = connect(
  (state) => ({
    path: toolSelectors.path(state)
  }),
  (dispatch) => ({
    download(assertion) {
      return dispatch(actions.download(assertion))
    }
  })
)(AssertionsList)

export {
  Assertions
}
