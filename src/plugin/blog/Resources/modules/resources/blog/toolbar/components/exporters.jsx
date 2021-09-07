import React from 'react'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON, URL_BUTTON} from '#/main/app/buttons'

import {selectors} from '#/plugin/blog/resources/blog/store/selectors'
import {actions} from '#/plugin/blog/resources/blog/store/actions'

// TODO : avoid hard dependency
import html2pdf from 'html2pdf.js'

const ExportersComponent = props =>
  <div className="component-container">
    <Button
      className="btn btn-block btn-emphasis"
      type={URL_BUTTON}
      icon="fa fa-fw fa-rss"
      label={trans('show_rss', {}, 'actions')}
      target={['icap_blog_rss', {blogId: props.blogId}]}
    />

    <Button
      className="btn btn-block btn-emphasis"
      type={CALLBACK_BUTTON}
      icon="fa fa-fw fa-file-pdf-o"
      label={trans('export-pdf', {}, 'actions')}
      callback={() => props.downloadBlogPdf(props.blogId).then(pdfContent => {
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
    />
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
