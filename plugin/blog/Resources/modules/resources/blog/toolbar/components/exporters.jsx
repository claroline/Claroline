import React from 'react'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {url} from '#/main/app/api'

import {selectors} from '#/plugin/blog/resources/blog/store/selectors'

const ExportersComponent = props =>
  <div className="panel panel-default">
    <div className="panel-heading">
      <a target="_blank" rel="noopener noreferrer" href={url(['icap_blog_rss', {blogId: props.blogId}])} className="label label-warning white export-links">
        <span className="fa fa-rss" /> {trans('rss_label', {}, 'icap_blog')}
      </a>
      {props.pdfEnabled &&
        <a target="_blank" rel="noopener noreferrer" href={url(['icap_blog_pdf', {blogId: props.blogId}])} className="label label-pdf white export-links">
          <span className="fa fa-file-pdf-o" /> {trans('pdf_export', {}, 'platform')}
        </a>
      }
    </div>
  </div>

ExportersComponent.propTypes = {
  blogId: T.string.isRequired,
  pdfEnabled: T.bool
}

const Exporters = connect(
  state => ({
    blogId: selectors.blog(state).data.id,
    pdfEnabled: selectors.pdfEnabled(state)
  })
)(ExportersComponent)

export {Exporters}
