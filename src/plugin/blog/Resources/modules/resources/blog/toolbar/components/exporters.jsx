import React from 'react'
import {connect} from 'react-redux'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'
import {DOWNLOAD_BUTTON, URL_BUTTON} from '#/main/app/buttons'

import {selectors} from '#/plugin/blog/resources/blog/store/selectors'

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
      type={DOWNLOAD_BUTTON}
      icon="fa fa-fw fa-file-pdf"
      label={trans('export-pdf', {}, 'actions')}
      file={{url: ['icap_blog_pdf', {blogId: props.blogId}]}}
    />
  </div>

ExportersComponent.propTypes = {
  blogId: T.string.isRequired
}

const Exporters = connect(
  state => ({
    blogId: selectors.blog(state).data.id
  })
)(ExportersComponent)

export {Exporters}
