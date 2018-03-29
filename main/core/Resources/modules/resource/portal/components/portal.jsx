import React from 'react'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/core/translation'
import {url} from '#/main/core/api/router'

import {MODAL_IFRAME} from '#/main/core/layout/modal'
import {actions as modalActions} from '#/main/core/layout/modal/actions'

import {
  PageContainer,
  PageHeader,
  PageContent
} from '#/main/core/layout/page'

import {constants as listConstants} from '#/main/core/data/list/constants'
import {DataListContainer} from '#/main/core/data/list/containers/data-list'
import {ResourceCard} from '#/main/core/resource/data/components/resource-card'

const PortalPage = props =>
  <PageContainer id="portal">
    <PageHeader title={trans('portal')} />

    <PageContent>
      <DataListContainer
        name="portal"
        open={{
          action: (row) => row.url && row.url.isYoutube ?
            () => props.displayModalVideo(row.name, row.url.embedYoutubeUrl) // open a modal with the video in a iframe
            :
            url(['claro_resource_open', {node: row.id, resourceType: row.meta.type}]) // direct link to the resource
        }}
        fetch={{
          url: ['apiv2_portal_index']
        }}
        definition={[
          {
            name: 'name',
            label: trans('name'),
            displayed: true,
            primary: true
          }, {
            name: 'meta.created',
            label: trans('creation_date'),
            type: 'date',
            alias: 'creationDate',
            displayed: true,
            filterable: false
          }, {
            name: 'createdAfter',
            label: trans('created_after'),
            type: 'date',
            displayable: false
          }, {
            name: 'createdBefore',
            label: trans('created_before'),
            type: 'date',
            displayable: false
          }
        ]}

        card={ResourceCard}

        display={{
          current: listConstants.DISPLAY_TILES,
          available: Object.keys(listConstants.DISPLAY_MODES)
        }}
      />
    </PageContent>
  </PageContainer>

PortalPage.propTypes = {
  displayModalVideo: T.func.isRequired
}

const Portal = connect(
  null,
  (dispatch) => ({
    displayModalVideo(title, src, controls = 0, showInfo = 0, autoPlay = 1, width = 535, height = 315) {
      dispatch(modalActions.showModal(MODAL_IFRAME, {
        title: title,
        src: `${src}?rel=${controls}&amp;showinfo=${showInfo}&amp;autoplay=${autoPlay}`,
        width: width,
        height: height
      }))
    }
  })
)(PortalPage)

export {
  Portal
}