import React from 'react'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {url} from '#/main/app/api'

import {selectors} from '#/plugin/blog/resources/blog/store/selectors'
import {actions} from '#/plugin/blog/resources/blog/store/actions'

// TODO : avoid hard dependency
import html2pdf from 'html2pdf.js'

const ExportersComponent = props =>
  <div className="panel panel-default">
    <div className="panel-heading">
      <a target="_blank" rel="noopener noreferrer" href={url(['icap_blog_rss', {blogId: props.blogId}])} className="label label-warning white export-links">
        <span className="fa fa-rss" /> {trans('rss_label', {}, 'icap_blog')}
      </a>
      <a target="_blank" rel="noopener noreferrer"
        onClick={() => props.downloadBlogPdf(props.blogId).then(pdfContent => {
          html2pdf()
            .set({
              filename: pdfContent.name,
              image: { type: 'jpeg', quality: 1 },
              html2canvas: { scale: 4 },
              enableLinks: true
            })
            .from(pdfContent.content, 'string')
            .save()
        })}
        className="label label-pdf white export-links">
        <span className="fa fa-file-pdf-o" /> {trans('export-pdf', {}, 'actions')}
      </a>
    </div>
  </div>

ExportersComponent.propTypes = {
  blogId: T.string.isRequired,
  downloadBlogPdf: T.func.isRequired
}

const Exporters = connect(
  state => ({
    blogId: selectors.blog(state).data.id
  }),
  (dispatch) => ({
    downloadBlogPdf(blogId) {
      return dispatch(actions.downloadBlogPdf(blogId))
    }
  })
)(ExportersComponent)

export {Exporters}
