import React from 'react'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'
import {constants as listConstants} from '#/main/core/layout/list/constants'

import {t, trans, transChoice} from '#/main/core/translation'
import {generateUrl} from '#/main/core/fos-js-router'

import {localeDate} from '#/main/core/layout/data/types/date/utils'
import {MODAL_IFRAME} from '#/main/core/layout/modal'
import {actions as modalActions} from '#/main/core/layout/modal/actions'

import {
  PageContainer as Page,
  PageHeader,
  PageContent
} from '#/main/core/layout/page'

import {DataListContainer as DataList} from '#/main/core/layout/list/containers/data-list.jsx'

const PortalPage = props =>
  <Page id="portal">
    <PageHeader title={t('portal')}>
    </PageHeader>

    <PageContent>
      <DataList
        display={{
          current: listConstants.DISPLAY_TILES,
          available: Object.keys(listConstants.DISPLAY_MODES)
        }}
        name="portal"
        definition={[
          {
            name: 'name',
            label: t('name'),
            renderer: (rowData) => {
              // variables is used because React will use it has component display name (eslint requirement)
              const wsLink = <a href={generateUrl('claro_resource_open', {node: rowData.id, resourceType: rowData.meta.type})}>{rowData.name}</a>

              return wsLink
            },
            displayed: true
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

        actions={[]}

        card={(row) => ({
          onClick: row.url && row.url.isYoutube
                     ? () => {props.displayModalVideo(row.name, row.url.embedYoutubeUrl)} // open a modal with the video in a iframe
                     : generateUrl('claro_resource_open', {node: row.id, resourceType: row.meta.type}), // direct link to the resource
          poster: row.poster,
          className: row.url && row.url.isExternal ? 'external-resource' : 'internal-resource',
          icon: row.url && row.url.isYoutube
                     ? <span className="item-icon-container fa fa-play"></span>
                     : <span className="item-icon-container" style={{
                       backgroundImage: 'url("' + row.meta.icon + '")',
                       backgroundPosition: 'center',
                       backgroundRepeat: 'no-repeat'
                     }}></span>,
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
                     <span className="social"><i className="fa fa-eye" aria-hidden="true"></i> {transChoice('display_views', row.meta.views, {'%count%': row.meta.views}, 'platform')}
                       &nbsp;
                       <i className="fa fa-heart" aria-hidden="true"></i> {transChoice('nb_likes', row.social.likes, {'%count%': row.social.likes}, 'icap_socialmedia')}</span>
                   </div>
        })}
      />
    </PageContent>
  </Page>

PortalPage.propTypes = {
  displayModalVideo: T.func.isRequired
}

function mapDispatchToProps(dispatch) {
  return {
    displayModalVideo(title, src, controls = 0, showinfo = 0, autoplay = 1, width = 535, height = 315) {
      dispatch(modalActions.showModal(MODAL_IFRAME, {
        title: title,
        src: `${src}?rel=${controls}&amp;showinfo=${showinfo}&amp;autoplay=${autoplay}`,
        width: width,
        height: height
      }))
    }
  }
}

const Portal = connect(null, mapDispatchToProps)(PortalPage)

export {
  Portal
}