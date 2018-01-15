import React from 'react'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'

import {t, trans, transChoice} from '#/main/core/translation'
import {generateUrl} from '#/main/core/api/router'
import {localeDate} from '#/main/core/scaffolding/date'

import {MODAL_IFRAME} from '#/main/core/layout/modal'
import {actions as modalActions} from '#/main/core/layout/modal/actions'

import {
  PageContainer,
  PageHeader,
  PageContent
} from '#/main/core/layout/page'

import {constants as listConstants} from '#/main/core/data/list/constants'
import {DataListContainer} from '#/main/core/data/list/containers/data-list.jsx'

const PortalPage = props =>
  <PageContainer id="portal">
    <PageHeader title={t('portal')} />

    <PageContent>
      <DataListContainer
        name="portal"
        open={{
          action: (rowData) => generateUrl('claro_resource_open', {node: rowData.id, resourceType: rowData.meta.type})
        }}
        fetch={{
          url: ['apiv2_portal_index']
        }}
        definition={[
          {
            name: 'name',
            label: t('name'),
            displayed: true,
            primary: true
          }, {
            name: 'meta.created',
            label: t('creation_date'),
            type: 'date',
            alias: 'creationDate',
            displayed: true,
            filterable: false
          }, {
            name: 'createdAfter',
            label: t('created_after'),
            type: 'date',
            displayable: false
          }, {
            name: 'createdBefore',
            label: t('created_before'),
            type: 'date',
            displayable: false
          }
        ]}

        card={(row) => ({
          onClick: row.url && row.url.isYoutube ?
            () => {props.displayModalVideo(row.name, row.url.embedYoutubeUrl)} // open a modal with the video in a iframe
            :
            generateUrl('claro_resource_open', {node: row.id, resourceType: row.meta.type}), // direct link to the resource
          poster: row.poster,
          className: row.url && row.url.isExternal ? 'external-resource' : 'internal-resource',
          icon: row.url && row.url.isYoutube ?
            <span className="item-icon-container fa fa-play" />
            :
            <span className="item-icon-container" style={{
              backgroundImage: 'url("' + row.meta.icon + '")',
              backgroundPosition: 'center',
              backgroundRepeat: 'no-repeat'
            }} />,
          title: row.name,
          subtitle: row.code,
          contentText: row.meta.description,
          footer:
            <div>
              {t('published_at', {'date': localeDate(row.meta.created)})}
            </div>,
          footerLong:
            //TODO: social data anv view count should be displayed in flags. Display in footer should be a hidden option of the platform.
            <div>
              <span className="publish-date">{trans(row.meta.type, {}, 'resource')} {t('published_at', {'date': localeDate(row.meta.created)})}</span>
              <span className="creator"> {t('by')} {row.meta.creator ? row.meta.creator.name: t('unknown')}</span>
              <br />
              <span className="social">
                <span className="fa fa-eye" aria-hidden="true" /> {transChoice('display_views', row.meta.views, {'%count%': row.meta.views}, 'platform')}
                &nbsp;
                <span className="fa fa-heart" aria-hidden="true" /> {transChoice('nb_likes', row.social.likes, {'%count%': row.social.likes}, 'icap_socialmedia')}
              </span>
            </div>
        })}

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