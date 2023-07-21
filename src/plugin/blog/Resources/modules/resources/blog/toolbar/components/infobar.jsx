import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/app/intl/translation'
import {ContentHtml} from '#/main/app/content/components/html'

import {selectors} from '#/plugin/blog/resources/blog/store/selectors'

const InfobarComponent = props => {
  if (!isEmpty(props.infos)) {
    return (
      <div className="card mb-3">
        <div className="card-header">
          <h2 className="card-title">{trans('information')}</h2>
        </div>

        <ContentHtml className="card-body">{props.infos}</ContentHtml>
      </div>
    )
  }

  return null
}

InfobarComponent.propTypes = {
  infos: T.string
}

const Infobar = connect(
  state => ({
    infos: !isEmpty(selectors.blog(state).data.options.data) ? selectors.blog(state).data.options.data.infos : selectors.blog(state).data.originalOptions.infos
  })
)(InfobarComponent)

export {Infobar}
